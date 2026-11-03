<?php
namespace App\Libraries;

use App\Entities\Equipment;

/** All methods that access the database must run inside the caller's transaction. */
final class UpgradePosition
{
    public const BLOCKS = ['PMT Block', 'PMT Block (Standard)', 'PMT Block (Long Cable)'];
    public const DRAWERS = ['Mini Drawer', 'Mini Drawer (Standard)', 'Mini Drawer (With Patch-Panel)',
        'Micro Drawer 4', 'Micro Drawer 5', 'Micro Drawer 6', 'Micro Drawer 7'];
    public const RESERVED = 'planned_upgrade_position';

    public static function normalize($value): ?int
    {
        if ($value === null || $value === '') return null;
        if (!(is_int($value) || (is_string($value) && preg_match('/^\d{1,2}$/D', $value)))) {
            throw new \DomainException('Upgrade Position must be an integer from 0 to 48, or empty.');
        }
        $number = (int)$value;
        if ($number < 0 || $number > 48) throw new \DomainException('Upgrade Position must be between 0 and 48.');
        return $number;
    }

    public static function optionalRule(string $field, array &$data, string &$error, array $params): bool
    {
        try {
            if (array_key_exists($field, $data)) $data[$field] = self::normalize($data[$field]);
            return true;
        } catch (\DomainException $e) { $error = $e->getMessage(); return false; }
    }

    public static function equipment(string $id): array
    {
        $row = db_connect()->query('SELECT e.*, t.name AS type_name FROM equipments e
            JOIN equipment_types t ON t.id=e.type_id WHERE e.id=? AND e.deleted_at IS NULL FOR UPDATE', [$id])->getRowArray();
        if (!$row) throw new \DomainException('Equipment is missing or deleted.');
        return $row;
    }

    public static function params(array $row): array
    {
        $value = $row['type_params'] === null ? [] : json_decode($row['type_params'], true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($value)) throw new \DomainException('Invalid equipment parameters.');
        return $value;
    }

    public static function relations(string $id, string $side): array
    {
        if (!in_array($side, ['parent', 'child'], true)) throw new \LogicException('Invalid relation side.');
        return db_connect()->query("SELECT * FROM parent_child_equips WHERE {$side}_equip_id=? AND deleted_at IS NULL ORDER BY id FOR UPDATE", [$id])->getResultArray();
    }

    public static function context(string $id): array
    {
        $owner = self::equipment($id);
        $pmt = null; $block = null; $links = [];
        if (in_array($owner['type_name'], self::BLOCKS, true)) {
            $block = $owner;
            $links = self::relations($id, 'parent');
            foreach ($links as $link) {
                $child = self::equipment($link['child_equip_id']);
                if ($child['type_name'] !== 'PMT') continue;
                if ($pmt !== null) throw new \DomainException('Multiple PMTs in this block. Review the assembly.');
                $pmt = $child;
                if (count(self::relations($pmt['id'], 'child')) !== 1) throw new \DomainException('PMT has multiple parents.');
            }
        } elseif ($owner['type_name'] === 'PMT') {
            $pmt = $owner;
            $links = self::relations($id, 'child');
            if (count($links) > 1) throw new \DomainException('PMT has multiple parents.');
            if ($links) {
                $parent = self::equipment($links[0]['parent_equip_id']);
                if (in_array($parent['type_name'], self::BLOCKS, true)) {
                    // Also detect a malformed block containing more than one PMT.
                    $context = self::context($parent['id']);
                    $block = $context['block'];
                    $links = $context['links'];
                }
            }
        } else throw new \DomainException('An active PMT or PMT Block is required.');
        return ['owner' => $owner, 'pmt' => $pmt, 'block' => $block, 'links' => $links];
    }

    public static function token(array $context): string
    {
        return hash('sha256', json_encode($context, JSON_THROW_ON_ERROR));
    }

    public static function snapshot(string $id): array
    {
        $c = self::context($id);
        $p = $c['pmt'] ? self::params($c['pmt']) : [];
        $b = $c['block'] ? self::params($c['block']) : [];
        return ['equipment_id' => $id, 'pmt_id' => $c['pmt']['id'] ?? null,
            'block_id' => $c['block']['id'] ?? null,
            'phase1_position' => $p['phase1_position'] ?? null,
            'box_position' => $c['pmt'] ? ($p['box_position'] ?? null) : ($b[self::RESERVED] ?? null),
            'version' => self::token($c), '_context' => $c];
    }

    public static function write(array $equipment, array $changes): void
    {
        $params = self::params($equipment);
        $changed = false;
        foreach ($changes as $key => $value) {
            if (($params[$key] ?? null) !== $value) { $params[$key] = $value; $changed = true; }
        }
        if ($changed && !model('EquipmentModel')->update($equipment['id'], new Equipment(['type_params' => $params]))) {
            throw new \RuntimeException('Could not save equipment positions.');
        }
    }

    /** Pure policy: a differing pair of recorded values requires an explicit decision. */
    public static function choose($blockValue, $pmtValue, ?string $source = null): ?int
    {
        $blockValue = self::normalize($blockValue); $pmtValue = self::normalize($pmtValue);
        if ($blockValue !== null && $pmtValue !== null && $blockValue !== $pmtValue) {
            if ($source === 'block') return $blockValue;
            if ($source === 'pmt') return $pmtValue;
            throw new \DomainException('An explicit position choice is required.');
        }
        return $blockValue ?? $pmtValue;
    }

    /** Prepare before modifying any relation or position. A choice is bound to current values. */
    public static function prepareAssembly(string $blockId, string $pmtId, ?array $resolution): ?array
    {
        $block = self::equipment($blockId); $pmt = self::equipment($pmtId);
        if (!in_array($block['type_name'], self::BLOCKS, true) || $pmt['type_name'] !== 'PMT') return null;
        $context = self::context($blockId);
        if ($context['pmt']) throw new \DomainException('The block already contains a PMT. Disassemble it first.');
        $old = self::context($pmtId);
        $blockValue = self::normalize(self::params($block)[self::RESERVED] ?? null);
        $pmtValue = self::normalize(self::params($pmt)['box_position'] ?? null);
        $version = self::token([$context, $old]);
        $conflict = $blockValue !== null && $pmtValue !== null && $blockValue !== $pmtValue;
        if ($resolution !== null || $conflict) {
            if (!$resolution || ($resolution['pmt_id'] ?? null) !== $pmtId
                || !is_string($resolution['version'] ?? null) || !hash_equals($version, $resolution['version'])
                || !in_array($resolution['source'] ?? null, ['block', 'pmt'], true)) {
                if (!$conflict) throw new \DomainException('Positions changed. Reopen the assembly confirmation.');
                throw new PositionConflict(['code' => 'upgrade_position_conflict', 'block_id' => $blockId,
                    'pmt_id' => $pmtId, 'block_position' => $blockValue, 'pmt_position' => $pmtValue,
                    'version' => $version, 'changed' => $resolution !== null]);
            }
        }
        return ['block' => $block, 'pmt' => $pmt,
            'value' => self::choose($blockValue, $pmtValue, $resolution['source'] ?? null)];
    }

    public static function scanPosition($value): int
    {
        if (!is_int($value) || $value < 1 || $value > 48) {
            throw new \DomainException('Upgrade Position must be an integer from 1 to 48.');
        }
        return $value;
    }

    /** A scanned position is an explicit edit, confirmed together with drawer assembly. */
    public static function prepareDrawer(string $parentId, array $slot): ?array
    {
        $parent = self::equipment($parentId);
        $child = self::equipment($slot['equipment_id']);
        $required = in_array($parent['type_name'], self::DRAWERS, true)
            && in_array($child['type_name'], self::BLOCKS, true);
        if (!$required) {
            if (array_key_exists('upgrade_position', $slot) || array_key_exists('position_version', $slot)) {
                throw new \DomainException('Upgrade Position is only accepted for PMT Blocks in mini/micro drawers.');
            }
            return null;
        }
        $value = self::scanPosition($slot['upgrade_position'] ?? null);
        $snapshot = self::snapshot($child['id']);
        if (!is_string($slot['position_version'] ?? null)
            || !hash_equals($snapshot['version'], $slot['position_version'])) {
            throw new \DomainException('Block positions or assembly changed. Validate the block ID again.');
        }
        return ['kind' => 'drawer', 'context' => $snapshot['_context'], 'value' => $value];
    }

    /** Return PMT parameters for the same UPDATE that sets its assembly status/location. */
    public static function applyAssembly(?array $plan): ?array
    {
        if (!$plan) return null;
        if (($plan['kind'] ?? null) === 'drawer') {
            $context = $plan['context'];
            if ($context['pmt']) self::write($context['pmt'], ['box_position' => $plan['value']]);
            $params = self::params($context['block']);
            $params[self::RESERVED] = $plan['value'];
            return $params;
        }
        self::write($plan['block'], [self::RESERVED => $plan['value']]);
        $params = self::params($plan['pmt']);
        if (($params['box_position'] ?? null) === $plan['value']) return null;
        $params['box_position'] = $plan['value'];
        return $params;
    }

    /** Keep the last actual position on the empty block, including pre-existing assemblies. */
    public static function retainBeforeDetach(string $parentId, string $childId): void
    {
        $parent = self::equipment($parentId); $child = self::equipment($childId);
        if (in_array($parent['type_name'], self::BLOCKS, true) && $child['type_name'] === 'PMT') {
            $context = self::context($parentId);
            if (($context['pmt']['id'] ?? null) !== $childId) throw new \DomainException('Assembly changed.');
            self::write($parent, [self::RESERVED => self::normalize(self::params($child)['box_position'] ?? null)]);
        }
    }
}
