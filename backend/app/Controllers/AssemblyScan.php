<?php
namespace App\Controllers;

use App\Libraries\UpgradePosition;
use App\Validation\EquipmentRules;
use Throwable;

/** Read-only validation; assembly repeats these checks inside its write transaction. */
final class AssemblyScan extends APIController
{
    public function get()
    {
        if (!model('FeatureModel')->findName('parent_relation')->isActive()) return $this->failServiceUnavailable();
        $parent = $this->request->getGet('parent_id');
        $child = $this->request->getGet('equipment_id');
        $slot = $this->request->getGet('slot');
        foreach ([$parent, $child] as $id) {
            if (!is_string($id) || !preg_match('/^[A-Z0-9]{7}$/D', $id)) return $this->failBadRequest();
        }
        if (!is_string($slot) || strlen($slot) > 100) return $this->failBadRequest();
        $db = db_connect();
        try {
            $db->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $db->transException(true)->transBegin();
            if (!model('EquipmentModel')->find($child)) {
                $db->transRollback();
                return $this->buildAPIResponse(404, null, 'Equipment is not registered or has been deleted.');
            }
            $data = ['detector_location_name' => $this->request->getGet('detector_location_name'),
                'drawer_unit' => $this->request->getGet('drawer_unit'), 'id' => $parent, 'slots' => [['name' => $slot, 'equipment_id' => $child]]];
            $error = '';
            if ($parent === $child || !EquipmentRules::validAssemblySlots('slots', $data, $error, ['parent_id_field' => 'id'])) {
                $db->transRollback();
                return $this->buildAPIResponse(409, null, $error ?: 'Equipment cannot be mounted into itself.');
            }
            $equipment = UpgradePosition::equipment($child);
            $result = ['id' => $child, 'type_name' => $equipment['type_name']];
            if (in_array($equipment['type_name'], UpgradePosition::BLOCKS, true)) {
                $snapshot = UpgradePosition::snapshot($child);
                unset($snapshot['_context']);
                $result['positions'] = $snapshot;
            }
            if (!$db->transCommit()) throw new \RuntimeException('Could not validate equipment.');
            return $this->buildAPIResponse(200, [$result]);
        } catch (\DomainException $e) {
            $db->transRollback();
            return $this->buildAPIResponse(409, null, $e->getMessage());
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Assembly scan failed: {exception}', ['exception' => $e]);
            return $this->failInternalError($e);
        }
    }
}
