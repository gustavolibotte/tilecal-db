<?php
namespace App\Controllers;

use App\Libraries\UpgradePosition;
use Throwable;

final class PmtPositions extends APIController
{
    private function validId($id): bool
    {
        return is_string($id) && preg_match('/^[A-Z0-9]{7}$/D', $id) === 1;
    }

    public function get()
    {
        $id = $this->request->getGet('equipment_id');
        if (!$this->validId($id)) return $this->failBadRequest();
        $db = db_connect();
        try {
            $db->transException(true)->transBegin();
            $data = UpgradePosition::snapshot($id);
            unset($data['_context']);
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
        $db = db_connect(); $started = false;
        try {
            $input = $this->request->getJSON(true);
            if (!is_array($input) || !$this->validId($input['equipment_id'] ?? null)
                || !array_key_exists('pmt_id', $input)
                || ($input['pmt_id'] !== null && !$this->validId($input['pmt_id']))
                || !is_string($input['version'] ?? null)
                || !is_array($input['changes'] ?? null) || !$input['changes']) return $this->failBadRequest();
            foreach ($input['changes'] as $field => $value) {
                if (!in_array($field, ['phase1_position', 'box_position'], true)) return $this->failBadRequest();
                $min = $field === 'phase1_position' ? 1 : 0;
                $max = $field === 'phase1_position' ? 49 : 48;
                if ($value !== null && (!is_int($value) || $value < $min || $value > $max)) {
                    return $this->failBadRequest(null, 'Invalid position: ' . $field);
                }
            }
            $db->transException(true)->transBegin(); $started = true;
            $current = UpgradePosition::snapshot($input['equipment_id']);
            if ($current['pmt_id'] !== $input['pmt_id'] || !hash_equals($current['version'], $input['version'])) {
                $db->transRollback();
                return $this->buildAPIResponse(409, null, 'Positions or assembly changed. Reload before saving.');
            }
            $c = $current['_context'];
            if (!$c['pmt'] && array_key_exists('phase1_position', $input['changes'])) {
                $db->transRollback();
                return $this->failBadRequest(null, 'Phase1 Position requires a mounted PMT.');
            }
            if ($c['pmt']) UpgradePosition::write($c['pmt'], $input['changes']);
            if ($c['block'] && array_key_exists('box_position', $input['changes'])) {
                UpgradePosition::write($c['block'], [UpgradePosition::RESERVED => $input['changes']['box_position']]);
            }
            if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Could not commit positions.');
            return $this->buildAPIResponse(200, null, 'Positions saved.');
        } catch (\DomainException $e) {
            if ($started) $db->transRollback();
            return $this->buildAPIResponse(409, null, $e->getMessage());
        } catch (Throwable $e) {
            if ($started) $db->transRollback();
            return $this->failInternalError($e);
        }
    }
}
