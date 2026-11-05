<?php
namespace App\Libraries;

use App\Entities\Equipment;

/** Called inside a transaction. Never repairs or removes existing assemblies. */
final class DrawerAssembly
{
    public const PARAM = 'drawer_assembly_profile';

    public static function location(array $equipment): string
    {
        if (empty($equipment['detector_location_id'])) return '';
        $row = db_connect()->table('detector_locations')->where('id', $equipment['detector_location_id'])->get()->getRowArray();
        return $row['name'] ?? '';
    }
    public static function resolve(array $equipment, array $input = [], array $seen = []): array
    {
        if (in_array($equipment['id'], $seen, true)) throw new \DomainException('Cycle in drawer hierarchy. Review existing assembly.');
        $seen[] = $equipment['id'];
        $stored = UpgradePosition::params($equipment)[self::PARAM] ?? [];
        if ($stored instanceof \stdClass) $stored = get_object_vars($stored);
        if (!is_array($stored)) throw new \DomainException('Invalid saved drawer profile. Review equipment history.');
        $location = self::location($equipment);
        $requested = $input['detector_location_name'] ?? '';
        $unit = ($input['drawer_unit'] ?? '') !== '' ? $input['drawer_unit'] : ($stored['unit'] ?? '');
        if (!is_string($requested) || !is_string($unit)) throw new \DomainException('Invalid assembly context.');
        if ($requested !== '') {
            DrawerLayout::configuration($requested);
            if ($location !== '' && $location !== $requested) throw new \DomainException('Detector Location changed. Edit it explicitly before assembling.');
            $location = $requested;
        }
        $parents = UpgradePosition::relations($equipment['id'], 'child');
        if (count($parents) > 1) throw new \DomainException('Drawer has multiple parents. Review existing assembly.');
        if ($parents) {
            $parent = UpgradePosition::equipment($parents[0]['parent_equip_id']);
            if (isset(DrawerLayout::SUPERS[$parent['type_name']])) {
                $parentProfile = self::resolve($parent, [], $seen);
                $parentSlots = self::slots($parent, $parentProfile);
                $slot = null;
                foreach ($parentSlots as $candidate) if ((int)$candidate['slot_id'] === (int)$parents[0]['slot_id']) $slot = $candidate;
                if (!$slot) throw new \DomainException('Drawer is mounted in an unsupported super-drawer slot.');
                if ($unit !== '' && $unit !== $slot['unit']) throw new \DomainException('Drawer unit disagrees with its super-drawer slot.');
                $unit = $slot['unit'];
                $parentLocation = $parentProfile['location'];
                if ($location !== '' && $parentLocation !== '' && $location !== $parentLocation) throw new \DomainException('Drawer and super-drawer Detector Locations disagree.');
                $location = $parentLocation ?: $location;
                $resolved = DrawerLayout::profile($equipment['type_name'], $location, $unit);
                if ($resolved['configuration'] !== $parentProfile['configuration']) throw new \DomainException('Drawer configuration disagrees with its parent.');
                return $resolved;
            }
        }
        if (isset(DrawerLayout::SUPERS[$equipment['type_name']]) && $location === '') {
            throw new \DomainException('Select Detector Location before assembling a super-drawer.');
        }
        $resolved = DrawerLayout::profile($equipment['type_name'], $location, $unit);
        if (isset($stored['configuration']) && $stored['configuration'] !== $resolved['configuration']
            && UpgradePosition::relations($equipment['id'], 'parent')) {
            throw new \DomainException('Existing assembly uses another configuration. Review it before changing the profile.');
        }
        return $resolved;
    }
    /** Resolve semantic slots against current metadata; do not assume database slot IDs. */
    public static function bind(array $specs, array $catalog): array
    {
        foreach ($specs as &$spec) {
            $matches = [];
            foreach ($catalog as $row) {
                $name = $row['name'];
                if ($name === $spec['name']) $matches[] = $row;
                elseif (isset($spec['position']) && preg_match('/pmt_block_slot_(\d+)$/D', $spec['name'], $m)
                    && preg_match('/pmt_block_slot_0*' . (int)$m[1] . '$/D', $name)) $matches[] = $row;
                elseif (!isset($spec['unit']) && !isset($spec['position'])) {
                    $suffix = substr($spec['name'], strlen('mini_drawer_'));
                    if (str_ends_with($name, $suffix)) $matches[] = $row;
                }
            }
            if (count($matches) > 1) throw new \DomainException('Ambiguous slot metadata: ' . $spec['name']);
            $spec['slot_id'] = $matches[0]['id'] ?? null;
            if ($matches) $spec['name'] = $matches[0]['name'];
        }
        return $specs;
    }
    public static function slots(array $equipment, array $profile): array
    {
        $catalog = db_connect()->table('type_slots')->where('parent_type_id', $equipment['type_id'])
            ->where('deleted_at', null)->get()->getResultArray();
        return self::bind(DrawerLayout::specifications($equipment['type_name'], $profile), $catalog);
    }
    public static function checkType(array $slot, array $child): void
    {
        if (!in_array($child['type_name'], $slot['types'], true)) {
            throw new \DomainException($slot['label'] . ': incompatible equipment type ' . $child['type_name'] . '. Expected ' . implode(' / ', $slot['types']) . '.');
        }
        if ($slot['types'] === ['HV Bus Board'] && (int)$child['type_id'] !== 31) throw new \DomainException('HV Bus Board must have type ID 31.');
    }
    public static function validateChild(array $parent, array $profile, array $slot, array $child, bool $complete): void
    {
        self::checkType($slot, $child);
        if (!isset($slot['unit'])) return;
        $location = self::location($child);
        if ($location !== '' && $profile['location'] !== '' && $location !== $profile['location']) {
            throw new \DomainException($child['id'] . ': Detector Location differs from the super-drawer.');
        }
        $saved = UpgradePosition::params($child)[self::PARAM] ?? [];
        if ($saved instanceof \stdClass) $saved = get_object_vars($saved);
        if (!is_array($saved)) throw new \DomainException('Invalid child drawer profile.');
        if (($saved['unit'] ?? $slot['unit']) !== $slot['unit'] ||
            ($saved['configuration'] ?? $profile['configuration']) !== $profile['configuration']) {
            throw new \DomainException($child['id'] . ': saved drawer profile does not match ' . $slot['unit'] . '.');
        }
        $childProfile = ['configuration' => $profile['configuration'], 'unit' => $slot['unit'], 'location' => $profile['location'] ?: $location];
        // Mini-drawers require a concrete module, either their own or the parent's.
        if (in_array($child['type_name'], DrawerLayout::MINIS, true) && $childProfile['location'] === '') {
            throw new \DomainException('Set Detector Location on the super-drawer or mini-drawer before assembly.');
        }
        if ($location !== '' && DrawerLayout::configuration($location) !== $profile['configuration']) throw new \DomainException('Child location belongs to another configuration.');
        self::validateExisting($child, $childProfile, $complete);
    }
    /** Returns occupied slot names, failing visibly instead of silently hiding legacy conflicts. */
    public static function validateExisting(array $parent, array $profile, bool $complete = false): array
    {
        $specs = self::slots($parent, $profile);
        $byId = []; foreach ($specs as $spec) if ($spec['slot_id'] !== null) $byId[(int)$spec['slot_id']] = $spec;
        $occupied = [];
        foreach (UpgradePosition::relations($parent['id'], 'parent') as $link) {
            $slot = $byId[(int)$link['slot_id']] ?? null;
            if (!$slot) throw new \DomainException($parent['id'] . ': an existing component occupies a slot outside this layout. No data was changed; review the assembly.');
            if (isset($occupied[$slot['name']])) throw new \DomainException('Multiple components in one drawer slot.');
            $child = UpgradePosition::equipment($link['child_equip_id']);
            $parents = UpgradePosition::relations($child['id'], 'child');
            if (count($parents) !== 1) throw new \DomainException('Component has multiple parents.');
            self::validateChild($parent, $profile, $slot, $child, $complete);
            if (isset($slot['position'])) {
                $position = UpgradePosition::snapshot($child['id'])['box_position'];
                if ($position === null || (int)$position !== $slot['position']) {
                    throw new \DomainException($child['id'] . ': existing Upgrade Position does not match physical position ' . $slot['position'] . '. Review positions before assembling.');
                }
            }
            $occupied[$slot['name']] = $child['id'];
        }
        if ($complete && count($occupied) !== count($specs)) throw new \DomainException($parent['id'] . ': drawer assembly is incomplete.');
        return $occupied;
    }
    public static function validateSlots(array $parent, array $input, bool $complete): ?array
    {
        if (!DrawerLayout::managed($parent['type_name'])) return null;
        $profile = self::resolve($parent, $input);
        $specs = self::slots($parent, $profile);
        $occupied = self::validateExisting($parent, $profile);
        $byName = []; foreach ($specs as $spec) $byName[$spec['name']] = $spec;
        foreach ($input['slots'] as $entry) {
            $slot = $byName[$entry['name']] ?? null;
            if (!$slot || $slot['slot_id'] === null) throw new \DomainException('Slot is unavailable for this layout. Run assembly:configure-layouts if metadata is missing.');
            if (isset($occupied[$slot['name']])) throw new \DomainException('Selected slot is already occupied.');
            $child = UpgradePosition::equipment($entry['equipment_id']);
            self::validateChild($parent, $profile, $slot, $child, $complete);
            if ($complete && isset($slot['position']) && ($entry['upgrade_position'] ?? null) !== $slot['position']) {
                throw new \DomainException($slot['label'] . ': scanned Upgrade Position must be ' . $slot['position'] . '.');
            }
            $occupied[$slot['name']] = $child['id'];
        }
        if ($complete && count($occupied) !== count($specs)) throw new \DomainException('Fill all occupied positions and electronics slots in the selected layout.');
        return $profile;
    }
    public static function saveProfile(array $equipment, array $profile): void
    {
        // Read the current row inside the assembly transaction.
        $equipment = UpgradePosition::equipment($equipment['id']);
        $params = UpgradePosition::params($equipment);
        $changes = [];

        $newProfile = [
            'configuration' => $profile['configuration'],
            'unit' => $profile['unit'],
        ];

        $storedProfile = $params[self::PARAM] ?? null;
        if ($storedProfile instanceof \stdClass) {
            $storedProfile = get_object_vars($storedProfile);
        }

        if ($storedProfile !== $newProfile) {
            // Preserve all other equipment parameters.
            $params[self::PARAM] = $newProfile;
            $changes['type_params'] = $params;
        }

        if (self::location($equipment) === '' && $profile['location'] !== '') {
            $location = db_connect()
                ->table('detector_locations')
                ->where('name', $profile['location'])
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$location) {
                throw new \DomainException(
                    'Detector Location is not registered.'
                );
            }

            $changes['detector_location_id'] = $location['id'];
        }

        if ($changes === []) {
            return;
        }

        // Save the profile and location together so the audit trigger
        // receives one equipment update for this operation.
        if (!model('EquipmentModel')->update(
            $equipment['id'],
            new Equipment($changes)
        )) {
            throw new \RuntimeException(
                'Could not save drawer assembly profile and Detector Location.'
            );
        }
    }
    public static function persist(array $parent, array $profile, array $entries): void
    {
        self::saveProfile($parent, $profile);
        if (!isset(DrawerLayout::SUPERS[$parent['type_name']])) return;
        $slots = self::slots($parent, $profile);
        foreach ($entries as $entry) foreach ($slots as $slot) if ($slot['name'] === $entry['name']) {
            $child = UpgradePosition::equipment($entry['equipment_id']);
            self::saveProfile($child, ['configuration' => $profile['configuration'], 'unit' => $slot['unit'],
                'location' => $profile['location'] ?: self::location($child)]);
        }
    }
}
