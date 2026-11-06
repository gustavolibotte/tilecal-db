<?php
namespace App\Commands;

use App\Libraries\DrawerAssembly;
use App\Libraries\DrawerLayout;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** Add missing metadata only; stale slots remain available to history and audit. */
final class ConfigureDrawerLayouts extends BaseCommand
{
    protected $group = 'Assembly';
    protected $name = 'assembly:configure-layouts';
    protected $description = 'Preview or add drawer layout metadata. Never rewrites equipment or relations.';
    protected $usage = 'assembly:configure-layouts [--apply --user username]';
    protected $options = ['--apply' => 'Apply additive metadata changes.', '--user' => 'Audit username.'];
    public function run(array $params)
    {
        $apply = CLI::getOption('apply') !== null;
        $user = CLI::getOption('user');
        if ($apply && (!is_string($user) || !preg_match('/^[A-Za-z0-9_.-]{1,10}$/D', $user))) {
            CLI::error('Provide --user with the audit username (1–10 characters).'); return EXIT_ERROR;
        }
        $db = db_connect();
        try {
            $db->transException(true)->transBegin();
            $types = $db->query('SELECT * FROM equipment_types ORDER BY id FOR UPDATE')->getResultArray();
            $allSlots = $db->query('SELECT * FROM type_slots ORDER BY id FOR UPDATE')->getResultArray();
            $permissions = $db->query('SELECT * FROM allowed_type_slots ORDER BY id FOR UPDATE')->getResultArray();
            $byName = [];
            foreach ($types as $type) if ($type['deleted_at'] === null && (int)$type['active']) $byName[$type['name']] = $type;
            if ((int)($byName['HV Bus Board']['id'] ?? 0) !== 31) throw new \DomainException('Active HV Bus Board type 31 is required.');
            $planned = [];
            foreach ($byName as $name => $type) {
                if (in_array($name, \App\Libraries\UpgradePosition::BLOCKS, true)) {
                    $required = [
                        'pmt_block_electronics_slot' => ['FENICS (Short Cable)', 'FENICS (Long Cable)'],
                        'pmt_block_hv_divider_slot' => ['Active HV Divider (Short Cable)', 'Active HV Divider (Long Cable)'],
                        'pmt_block_pmt_slot' => ['PMT'],
                    ];
                    foreach ($required as $slotName => $childType) {
                        $id = null;
                        foreach ($allSlots as $existing) {
                            if ((int)$existing['parent_type_id'] === (int)$type['id']
                                && $existing['name'] === $slotName && $existing['deleted_at'] === null) {
                                $id = $existing['id'];
                            }
                        }
                        $planned[$type['id'] . ':' . $slotName] = [
                            'slot_id' => $id, 'parent_type_id' => $type['id'],
                            'name' => $slotName, 'types' => $childType,
                        ];
                    }
                    continue;
                }
                if (!DrawerLayout::managed($name)) continue;
                $profiles = [];
                if (isset(DrawerLayout::SUPERS[$name])) $profiles[] = DrawerLayout::profile($name, '', '');
                elseif (str_starts_with($name, 'Micro Drawer')) $profiles[] = DrawerLayout::profile($name, '', '');
                else foreach (['LBA01', 'EBA01', 'EBA15'] as $location) {
                    foreach (DrawerLayout::layout(DrawerLayout::configuration($location))['units'] as $unit) {
                        if ($unit['type'] === 'minidrawer') $profiles[] = DrawerLayout::profile($name, $location, $unit['id']);
                    }
                }
                $catalog = array_values(array_filter($allSlots, static fn($s) => (int)$s['parent_type_id'] === (int)$type['id'] && $s['deleted_at'] === null));
                foreach ($profiles as $profile) foreach (DrawerAssembly::bind(DrawerLayout::specifications($name, $profile), $catalog) as $slot) {
                    $key = $type['id'] . ':' . $slot['name'];
                    if (!isset($planned[$key])) $planned[$key] = $slot + ['parent_type_id' => $type['id']];
                    $planned[$key]['types'] = array_unique(array_merge($planned[$key]['types'], $slot['types']));
                }
            }
            $count = 0;
            foreach ($planned as $slot) {
                $slotId = $slot['slot_id'];
                if ($slotId === null) {
                    foreach ($allSlots as $old) if ($old['name'] === $slot['name'] && (int)$old['parent_type_id'] === (int)$slot['parent_type_id']) {
                        throw new \DomainException('A deleted slot needs manual review: ' . $slot['name']);
                    }
                    CLI::write('ADD SLOT ' . $slot['parent_type_id'] . ': ' . $slot['name']); $count++;
                    if ($apply) {
                        $stamp = $db->query('SELECT CURRENT_TIMESTAMP(6) AS stamp')->getRowArray()['stamp'];
                        if (!$db->table('type_slots')->insert(['name' => $slot['name'], 'parent_type_id' => $slot['parent_type_id'],
                            'user' => $user, 'created_at' => $stamp, 'updated_at' => $stamp])) throw new \RuntimeException('Slot insert failed.');
                        $slotId = $db->insertID();
                    }
                }
                foreach ($slot['types'] as $allowedName) {
                    if (!isset($byName[$allowedName])) {
                        // Standard-name aliases are optional, but at least one allowed type must exist.
                        if (in_array($allowedName, ['Mini Drawer', 'Mini Drawer (Standard)', 'PMT Block', 'PMT Block (Standard)', 'PMT Block (Long Cable)'], true)) continue;
                        throw new \DomainException('Missing active type: ' . $allowedName);
                    }
                    $typeId = $byName[$allowedName]['id']; $found = false;
                    foreach ($permissions as $permission) if ($slotId !== null && (int)$permission['slot_id'] === (int)$slotId && (int)$permission['allowed_type_id'] === (int)$typeId) {
                        if ($permission['deleted_at'] !== null) throw new \DomainException('Deleted permission needs manual review.');
                        $found = true;
                    }
                    if ($found) continue;
                    CLI::write('ALLOW ' . $slot['name'] . ': ' . $allowedName); $count++;
                    if ($apply) {
                        $stamp = $db->query('SELECT CURRENT_TIMESTAMP(6) AS stamp')->getRowArray()['stamp'];
                        if (!$db->table('allowed_type_slots')->insert(['slot_id' => $slotId, 'allowed_type_id' => $typeId,
                            'user' => $user, 'created_at' => $stamp, 'updated_at' => $stamp])) throw new \RuntimeException('Permission insert failed.');
                    }
                }
            }
            if (!$apply) { $db->transRollback(); CLI::write("Preview: {$count} additions. No rows written."); }
            else {
                if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Could not commit metadata.');
                CLI::write("Applied {$count} additions. Equipment, comments, positions and assemblies were not modified.");
            }
            return EXIT_SUCCESS;
        } catch (\Throwable $e) { $db->transRollback(); CLI::error($e->getMessage()); return EXIT_ERROR; }
    }
}
