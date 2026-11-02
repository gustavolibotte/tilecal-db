<?php
namespace App\Controllers;

use App\Entities\Equipment;
use Throwable;

/** Positions belong only to the PMT; a block exposes its current assembly. */
final class PmtPositions extends APIController
{
    private const BLOCKS = ['PMT Block', 'PMT Block (Standard)', 'PMT Block (Long Cable)'];
    private const FIELDS = ['phase1_position', 'box_position'];

    private function validId($id): bool
    {
        return is_string($id) && preg_match('/^[A-Z0-9]{7}$/D', $id) === 1;
    }

    // Locking reads serialize this operation with ordinary equipment updates and
    // assembly relation deletion/replacement. Both GET and POST use a transaction.
    private function snapshot(string $id): array
    {
        $db = db_connect();
        $owner = $db->query('SELECT e.*, t.name AS type_name FROM equipments e
            JOIN equipment_types t ON t.id=e.type_id
            WHERE e.id=? AND e.deleted_at IS NULL FOR UPDATE', [$id])->getRowArray();
        if (!$owner || ($owner['type_name'] !== 'PMT' && !in_array($owner['type_name'], self::BLOCKS, true))) {
            throw new \DomainException('An active PMT or PMT Block is required.');
        }
        $relations = [];
        $pmt = $owner;
        if ($owner['type_name'] !== 'PMT') {
            // Read all active slots: do not choose an arbitrary PMT if data is ambiguous.
            $relations = $db->query('SELECT r.id, r.child_equip_id, r.slot_id, r.updated_at,
                e.type_params, e.updated_at AS pmt_updated_at, e.deleted_at, t.name AS type_name
                FROM parent_child_equips r JOIN equipments e ON e.id=r.child_equip_id
                JOIN equipment_types t ON t.id=e.type_id
                WHERE r.parent_equip_id=? AND r.deleted_at IS NULL FOR UPDATE', [$id])->getResultArray();
            $pmts = array_values(array_filter($relations, static fn($r) => $r['type_name'] === 'PMT'));
            if (count($pmts) === 0) return ['equipment_id' => $id, 'pmt_id' => null];
            if (count($pmts) !== 1 || $pmts[0]['deleted_at'] !== null) {
                throw new \DomainException('Assembly is ambiguous or contains a deleted PMT. Review the assembly first.');
            }
            $r = $pmts[0];
            $pmt = ['id' => $r['child_equip_id'], 'type_params' => $r['type_params'], 'updated_at' => $r['pmt_updated_at']];
            $parents = $db->query('SELECT id FROM parent_child_equips WHERE child_equip_id=? AND deleted_at IS NULL FOR UPDATE', [$pmt['id']])->getResultArray();
            if (count($parents) !== 1) throw new \DomainException('PMT has multiple active parents. Review the assembly first.');
            $relations = [$r];
        }
        $params = $pmt['type_params'] === null ? [] : json_decode($pmt['type_params'], true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($params)) throw new \DomainException('Invalid PMT parameters. No changes were made.');
        return [
            'equipment_id' => $id, 'pmt_id' => $pmt['id'],
            'phase1_position' => $params['phase1_position'] ?? null,
            'box_position' => $params['box_position'] ?? null,
            'version' => hash('sha256', json_encode([$id, $pmt, $relations], JSON_THROW_ON_ERROR)),
            '_params' => $params,
        ];
    }

    public function get()
    {
        $id = $this->request->getGet('equipment_id');
        if (!$this->validId($id)) return $this->failBadRequest();
        $db = db_connect();
        try {
            $db->transException(true)->transBegin();
            $data = $this->snapshot($id);
            unset($data['_params']);
            if (!$db->transCommit()) throw new \RuntimeException('Could not read positions.');
            return $this->buildAPIResponse(200, [$data]);
        } catch (\DomainException $e) {
            $db->transRollback();
            return $this->buildAPIResponse(409, null, $e->getMessage());
        } catch (Throwable $e) {
            $db->transRollback();
            return $this->failInternalError($e);
        }
    }

    public function post()
    {
        if (!model('FeatureModel')->findName('equipment_edit')->isActive()) return $this->failServiceUnavailable();
        $db = db_connect();
        $started = false;
        try {
            $input = $this->request->getJSON(true);
            if (!is_array($input) || !$this->validId($input['equipment_id'] ?? null)
                || !$this->validId($input['pmt_id'] ?? null) || !is_string($input['version'] ?? null)
                || !is_array($input['changes'] ?? null) || !$input['changes']) return $this->failBadRequest();
            foreach ($input['changes'] as $field => $value) {
                if (!in_array($field, self::FIELDS, true)) return $this->failBadRequest();
                $min = $field === 'phase1_position' ? 1 : 0;
                $max = $field === 'phase1_position' ? 49 : 48;
                if ($value !== null && (!is_int($value) || $value < $min || $value > $max)) {
                    return $this->failBadRequest(null, 'Invalid position: ' . $field);
                }
            }
            $db->transException(true)->transBegin();
            $started = true;
            $current = $this->snapshot($input['equipment_id']);
            if (($current['pmt_id'] ?? null) !== $input['pmt_id'] || !hash_equals($current['version'] ?? '', $input['version'])) {
                $db->transRollback();
                return $this->buildAPIResponse(409, null, 'PMT or assembly changed. Cancel and reload before editing again.');
            }
            $params = $current['_params'];
            $changed = false;
            foreach ($input['changes'] as $field => $value) {
                if (($params[$field] ?? null) !== $value) {
                    $params[$field] = $value;
                    $changed = true;
                }
            }
            if ($changed && !model('EquipmentModel')->update($current['pmt_id'], new Equipment(['type_params' => $params]))) {
                throw new \RuntimeException('Could not save PMT positions.');
            }
            if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Could not commit PMT positions.');
            return $this->buildAPIResponse(200, null, 'PMT positions saved.');
        } catch (\DomainException $e) {
            if ($started) $db->transRollback();
            return $this->buildAPIResponse(409, null, $e->getMessage());
        } catch (Throwable $e) {
            if ($started) $db->transRollback();
            return $this->failInternalError($e);
        }
    }
}
