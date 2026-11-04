<?php
namespace App\Libraries;

/** Additive plan only: no existing row is updated or deleted. */
final class DrawerSlotPlan
{
    public static function plan(array $types, array $slots, array $allowed): array
    {
        $byName = []; foreach ($types as $type) $byName[$type['name']] = $type;
        $hv = $byName['HV Bus Board'] ?? null;
        if (!$hv || (int)$hv['id'] !== 31 || $hv['deleted_at'] !== null || !(int)$hv['active']) {
            throw new \DomainException('Active HV Bus Board type 31 is required.');
        }
        $plan = [];
        foreach (UpgradePosition::DRAWERS as $name) {
            $type = $byName[$name] ?? null;
            if (!$type || $type['deleted_at'] !== null || !(int)$type['active']) {
                throw new \DomainException('Missing active drawer type: ' . $name);
            }
            $matches = [];
            foreach ($slots as $slot) {
                if ((int)$slot['parent_type_id'] !== (int)$type['id'] || $slot['deleted_at'] !== null) continue;
                foreach ($allowed as $permission) {
                    if ((int)$permission['slot_id'] === (int)$slot['id'] && (int)$permission['allowed_type_id'] === 31
                        && $permission['deleted_at'] === null) $matches[$slot['id']] = $slot;
                }
            }
            if (count($matches) > 1) throw new \DomainException('Multiple HV Bus Board slots for ' . $name . '; review configuration.');
            if ($matches) {
                $plan[] = ['action'=>'keep', 'type'=>$name, 'name'=>reset($matches)['name'], 'parent_type_id'=>(int)$type['id']];
                continue;
            }
            $slotName = 'drawer_' . $type['id'] . '_hv_bus_board_slot';
            foreach ($slots as $slot) {
                if ($slot['name'] === $slotName) throw new \DomainException('Slot name already exists without the expected permission: ' . $slotName);
            }
            $plan[] = ['action'=>'add', 'type'=>$name, 'name'=>$slotName, 'parent_type_id'=>(int)$type['id']];
        }
        return $plan;
    }
}
