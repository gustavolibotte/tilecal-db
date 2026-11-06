<?php
namespace App\Libraries;

/** Physical occupancy only. Diagram arrows are not electronic channel assignments. */
final class DrawerLayout
{
    public const MINIS = ['Mini Drawer', 'Mini Drawer (Standard)', 'Mini Drawer (With Patch-Panel)'];
    public const SUPERS = [
        'P2 LB Super Drawer' => 'LB', 'P2 LB Super Drawer BSI' => 'LB',
        'P2 EB Super Drawer' => 'EB', 'P2 EB Super Drawer BSI' => 'EB',
        'P2 Short EB Super Drawer' => 'SHORT_EBA15_EBC18',
    ];
    public static function managed(string $type): bool
    {
        return in_array($type, UpgradePosition::DRAWERS, true) || isset(self::SUPERS[$type]);
    }
    public static function configuration(string $location): string
    {
        if (!preg_match('/^(LBA|LBC|EBA|EBC)(0[1-9]|[1-5][0-9]|6[0-4])$/D', $location)) {
            throw new \DomainException('Select a detector module from LBA01–LBC64 or EBA01–EBC64.');
        }
        if (in_array($location, ['EBA15', 'EBC18'], true)) return 'SHORT_EBA15_EBC18';
        return str_starts_with($location, 'LB') ? 'LB' : 'EB';
    }
    public static function layout(string $configuration): array
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../data/drawer_layouts.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($data['configurations'] as $entry) if ($entry['id'] === $configuration) return $entry;
        throw new \DomainException('Unknown drawer configuration.');
    }
    public static function unit(string $configuration, string $unit): array
    {
        foreach (self::layout($configuration)['units'] as $entry) if ($entry['id'] === $unit) return $entry;
        throw new \DomainException('Unit is not part of this configuration.');
    }
    public static function profile(string $type, string $location, string $unit): array
    {
        $configuration = $location !== '' ? self::configuration($location) : null;
        if (isset(self::SUPERS[$type])) {
            if ($configuration !== null && $configuration !== self::SUPERS[$type]) {
                throw new \DomainException('Super-drawer type and Detector Location disagree.');
            }
            return ['configuration' => self::SUPERS[$type], 'unit' => '', 'location' => $location];
        }
        if (preg_match('/^Micro Drawer ([4-7])$/D', $type, $match)) {
            $expected = (int)$match[1] <= 5 ? 'EB' : 'SHORT_EBA15_EBC18';
            if ($configuration !== null && $configuration !== $expected) throw new \DomainException('Micro-drawer type and Detector Location disagree.');
            return ['configuration' => $expected, 'unit' => 'mD' . $match[1], 'location' => $location];
        }
        if (!in_array($type, self::MINIS, true)) throw new \DomainException('Unsupported drawer type.');
        if ($configuration === null) throw new \DomainException('Select Detector Location before assembling a mini-drawer.');
        $entry = self::unit($configuration, $unit);
        if ($entry['type'] !== 'minidrawer') throw new \DomainException('Select a mini-drawer unit.');
        return ['configuration' => $configuration, 'unit' => $unit, 'location' => $location];
    }
    /** Mini slot suffixes retain local physical numbering; micro slots retain legacy occupied-position numbering. */
    public static function specifications(string $type, array $profile): array
    {
        $layout = self::layout($profile['configuration']);
        $slots = [];
        if (isset(self::SUPERS[$type])) {
            foreach ($layout['mechanical_order'] as $unit) {
                $mini = str_starts_with($unit, 'MD');
                $prefix = $profile['configuration'] === 'LB' ? 'lbsd' : 'ebsd';
                $name = $mini ? sprintf('%s_mini_drawer_slot_%02d', $prefix, substr($unit, 2))
                    : sprintf('ebsd_micro_drawer_%d_slot_01', substr($unit, 2));
                $slots[] = ['name' => $name, 'label' => $unit, 'unit' => $unit,
                    'types' => $mini ? self::MINIS : ['Micro Drawer ' . substr($unit, 2)]];
            }
            return $slots;
        }
        $unit = self::unit($profile['configuration'], $profile['unit']);
        $mini = $unit['type'] === 'minidrawer';
        if ($mini) {
            foreach (['daughterboard' => 'Daughterboard', 'mainboard' => 'Mainboard', 'hv_bus_board' => 'HV Bus Board'] as $key => $name) {
                $slots[] = ['name' => 'mini_drawer_' . $key . '_slot', 'label' => $name, 'types' => [$name]];
            }
        }
        $prefix = $mini ? 'mini_drawer' : 'micro_drawer_' . substr($profile['unit'], 2);
        foreach ($unit['occupied_pmt_slots'] as $index => $position) {
            $long = in_array($position, $layout['long_cable_pmt_positions_stated_in_note'], true);
            $localSlot = $mini ? $position - $unit['slot_range'][0] + 1 : $index + 1;
            $slots[] = ['name' => sprintf('%s_pmt_block_slot_%02d', $prefix, $localSlot),
                'label' => 'PMT Block — position ' . $position, 'position' => $position,
                'cable' => $long ? 'Long Cable' : 'Short Cable',
                'types' => UpgradePosition::BLOCKS];
        }
        return $slots;
    }
}
