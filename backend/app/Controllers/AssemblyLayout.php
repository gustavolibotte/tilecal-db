<?php
namespace App\Controllers;

use App\Libraries\DrawerAssembly;
use App\Libraries\DrawerLayout;
use App\Libraries\UpgradePosition;

final class AssemblyLayout extends APIController
{
    public function get()
    {
        if (!model('FeatureModel')->findName('parent_relation')->isActive()) return $this->failServiceUnavailable();
        $id = $this->request->getGet('parent_id');
        if (!is_string($id) || !preg_match('/^[A-Z0-9]{7}$/D', $id)) return $this->failBadRequest();
        $db = db_connect();
        try {
            $db->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $db->transException(true)->transBegin();
            $parent = UpgradePosition::equipment($id);
            $types = $db->table('equipment_types')->where('deleted_at', null)->where('active', 1)->get()->getResultArray();
            $locations = $db->table('detector_locations')->select('name')->where('deleted_at', null)->orderBy('name')->get()->getResultArray();
            $locations = array_values(array_filter(array_column($locations, 'name'), static function ($name) {
                return (bool)preg_match('/^(LBA|LBC|EBA|EBC)(0[1-9]|[1-5][0-9]|6[0-4])$/D', $name);
            }));
            $result = ['managed' => DrawerLayout::managed($parent['type_name']), 'locations' => $locations,
                'current_location' => DrawerAssembly::location($parent), 'slots' => [], 'issues' => [], 'ready' => false];
            if ($result['managed']) {
                try {
                    $profile = DrawerAssembly::resolve($parent, $this->request->getGet());
                    $result['profile'] = $profile;
                    $slots = DrawerAssembly::slots($parent, $profile);
                    foreach ($slots as &$slot) {
                        $slot['allowed_type_ids'] = array_values(array_map(static fn($t) => (string)$t['id'],
                            array_filter($types, static fn($t) => in_array($t['name'], $slot['types'], true))));
                        if ($slot['slot_id'] === null) $result['issues'][] = 'Missing slot metadata: ' . $slot['name'] . '. Run assembly:configure-layouts.';
                        if (!$slot['allowed_type_ids']) $result['issues'][] = 'Missing active equipment types for ' . $slot['label'];
                    }
                    unset($slot);
                    $result['slots'] = $slots;
                    $result['occupied'] = DrawerAssembly::validateExisting($parent, $profile);
                    $result['ready'] = !$result['issues'];
                    if ($profile['unit'] !== '') $result['physical'] = DrawerLayout::unit($profile['configuration'], $profile['unit']);
                } catch (\DomainException $e) { $result['issues'][] = $e->getMessage(); }
            }
            if (!$db->transCommit()) throw new \RuntimeException('Could not read layout.');
            return $this->buildAPIResponse(200, [$result]);
        } catch (\DomainException $e) {
            $db->transRollback(); return $this->buildAPIResponse(409, null, $e->getMessage());
        } catch (\Throwable $e) {
            $db->transRollback(); return $this->failInternalError($e);
        }
    }
}
