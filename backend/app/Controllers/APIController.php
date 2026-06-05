<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use Throwable;

class APIController extends BaseController
{
    protected function buildAPIResponse(
        int $status,
        ?array $data = null,
        string $message = '',
        array $metadata = []
    ): ResponseInterface {
        helper('response');
        return buildAPIResponse(
            $status,
            $data,
            $message,
            $metadata
        );
    }

    protected function failBadRequest(
        ?array $data = null,
        string $message = '',
        array $metadata = []
    ): ResponseInterface {
        helper('response');
        return failBadRequest($data, $message, $metadata);
    }

    protected function respondWithId(array|int|string $id, string $message): ResponseInterface
    {
        if (!is_array($id)){
            $id = [$id];
        }
        return $this->buildAPIResponse(
            201,
            $id,
            $message
        );

    }

    protected function respondCreated(array|int|string $id=[])
    {
        return $this->respondWithId(
            $id,
            'Created'
        );
    }

    protected function respondUpdated(array|int|string $id=[])
    {
        return $this->respondWithId(
            $id,
            'Updated'
        );
    }

    protected function respondQueryResults(?array $data=[])
    {
        $data = empty($data)? []: $data;
        return $this->buildAPIResponse(
            200,
            $data
        );
    }

    protected function respondDeleted(array|int|string $id=[])
    {

        return $this->respondWithId(
            $id,
            'Deleted'
        );
    }

    protected function respondRestored(array|int|string $id=[])
    {
        return $this->respondWithId(
            $id,
            'Restored'
        );
    }

    protected function respondPurged(array|int|string $id=[])
    {
        return $this->respondWithId(
            $id,
            'Purged'
        );
    }

    protected function failUnauthorized(): ResponseInterface
    {
        return $this->buildAPIResponse(
            401,
            message: 'Unauthorized'
        );
    }

    protected function failForbidden(): ResponseInterface
    {
        return $this->buildAPIResponse(
            403,
            message: 'Forbidden'
        );
    }

    public function failNotFound(): ResponseInterface
    {
        return $this->buildAPIResponse(404, message: 'Not Found');
    }

    protected function failServiceUnavailable(): ResponseInterface
    {
        return $this->buildAPIResponse(503, message:'Service Unavailable');
    }

    protected function failValidationErrors(array $validationErrors, ?string $message=''): ResponseInterface
    {
        return $this->buildAPIResponse(
            400,
            $validationErrors,
            empty($message)? 'Invalid data': $message
        );
    }

    protected function failResourceExists(): ResponseInterface
    {
        throw new \BadMethodCallException('failResourceExists is not Implemented');
    }

    protected function failResourceGone(): ResponseInterface
    {
        throw new \BadMethodCallException('failResourceGone is not Implemented');
    }

    protected function failTooManyRequests(): ResponseInterface
    {
        throw new \BadMethodCallException('failTooManyRequests is not Implemented');
    }

    protected function failInternalError(Throwable $e)
    {
        helper('response');
        return failInternalError($e);
    }

    protected function parseOrdering(string $varName, string $tableName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {
        $orderBy = $this->request->getVar($varName);
        if ($orderBy == 'updatedAt') {
            $queryBuilder = $queryBuilder->orderBy(sprintf('%s.updated_at', $tableName), 'desc');
        }
        return $queryBuilder;
    }

    protected function parseFilterCondition(mixed $var, string $colName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {   
        if (empty($var)){
            return $queryBuilder;
        }
        if (is_array($var)) {
            $queryBuilder = $queryBuilder->whereIn($colName, $var);
        } else {
            $queryBuilder = $queryBuilder->where($colName, $var);
        }
        return $queryBuilder;
    }

    protected function parseDynamicFilter(mixed $var, ?string $matchMode, string $colName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {   
        if (empty($var) && $var !== '0') {
            return $queryBuilder;
        }

        $matchMode = $matchMode ?? 'contains';

        if (is_array($var)) {
            $varLower = array_map('strtolower', $var);
            
            if ($matchMode === 'notEquals') {
                return $queryBuilder->whereNotIn("LOWER($colName)", $varLower);
            } elseif (in_array($matchMode, ['contains', 'startsWith', 'endsWith'])) {
                $queryBuilder->groupStart();
                foreach ($varLower as $val) {
                    if ($matchMode === 'startsWith') {
                        $queryBuilder->orLike("LOWER($colName)", $val, 'after');
                    } elseif ($matchMode === 'endsWith') {
                        $queryBuilder->orLike("LOWER($colName)", $val, 'before');
                    } else {
                        $queryBuilder->orLike("LOWER($colName)", $val, 'both');
                    }
                }
                $queryBuilder->groupEnd();
                return $queryBuilder;
            }
            return $queryBuilder->whereIn("LOWER($colName)", $varLower);
        }

        $varLower = strtolower((string)$var);

        switch ($matchMode) {
            case 'startsWith':
                $queryBuilder = $queryBuilder->like("LOWER($colName)", $varLower, 'after');
                break;
            case 'endsWith':
                $queryBuilder = $queryBuilder->like("LOWER($colName)", $varLower, 'before');
                break;
            case 'equals':
                $queryBuilder = $queryBuilder->where("LOWER($colName)", $varLower);
                break;
            case 'notEquals':
                $queryBuilder = $queryBuilder->where("LOWER($colName) !=", $varLower);
                break;
            case 'notContains':
                $queryBuilder = $queryBuilder->notLike("LOWER($colName)", $varLower, 'both');
                break;
            case 'contains':
            default:
                $queryBuilder = $queryBuilder->like("LOWER($colName)", $varLower, 'both');
                break;
        }

        return $queryBuilder;
    }

    protected function getValidatedData(array $rules): array
    {
        $data = $this->getRequestValuesFromRules($rules);
        $res = [
            'valid' => $this->validateData($data, $rules),
            'data' => $data
        ];
        if ($res['valid']){
            return $res;
        }
        $res['errors'] = $this->validator->getErrors();
        return $res;
    }

    protected function getRequestValuesFromRules(array $rules): array
    {
        $values = [];
        foreach (array_keys($rules) as $fieldName){
            $values[$fieldName] = $this->request->getVar($fieldName);
        }
        return $values;
    }

    public function parseDeletion(BaseBuilder|Model $queryBuilder, bool $onlyDeleted): BaseBuilder|Model
    {
        $user = \Config\Services::user();
        if ($user->isAuthorized('delete')){
            if ($onlyDeleted){
                $queryBuilder = $queryBuilder->onlyDeleted();
            } else{
                $queryBuilder = $queryBuilder->withDeleted();
            }
        }
        return $queryBuilder;
    }

    /**
     * Retrieves the global status of all 256 calorimeter modules.
     * Used for the Macro 3D View.
     */
    public function getCalorimeterStatus(): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        // Fast, optimized query targeting only the main modules
        $sql = "
            SELECT 
                dl.name as location_name,
                e.id as equipment_id,
                s.name as status_name
            FROM detector_locations dl
            LEFT JOIN equipments e ON e.detector_location_id = dl.id AND e.deleted_at IS NULL
            LEFT JOIN statuses s ON e.status_id = s.id
            WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$'
              AND dl.name NOT LIKE '%00'
        ";
        
        try {
            $query = $db->query($sql);
            $results = $query->getResultArray();

            $data = [
                'EBA' => array_fill(0, 64, null),
                'LBA' => array_fill(0, 64, null),
                'LBC' => array_fill(0, 64, null),
                'EBC' => array_fill(0, 64, null),
            ];
            
            foreach ($results as $row) {
                $barrel = substr($row['location_name'], 0, 3);
                $moduleIndex = (int)substr($row['location_name'], 3, 2) - 1; 
                
                $currentStatus = $data[$barrel][$moduleIndex]['status'] ?? 'NOMINAL';
                $dbStatus = strtoupper($row['status_name'] ?? 'NOMINAL');
                
                if (!in_array($dbStatus, ['NOMINAL', 'GOOD', 'OK', 'WORKING', 'INSTALLED', 'TESTED', 'DELIVERED'])) {
                    $currentStatus = 'ALERT';
                }

                $data[$barrel][$moduleIndex] = [
                    'status'      => $currentStatus,
                    'temperature' => 24.5, // Fixed mock until specific Temp sensor table is defined
                    'powerDraw'   => 4.2,  // Fixed mock
                    'serial'      => $row['equipment_id'] ?? 'N/A'
                ];
            }
            
            foreach ($data as $barrel => &$modules) {
                foreach ($modules as $index => &$mod) {
                    if ($mod === null) {
                        $mod = [
                            'status' => 'UNKNOWN',
                            'temperature' => 0,
                            'powerDraw' => 0,
                            'serial' => 'EMPTY'
                        ];
                    }
                }
            }

            return $this->respondQueryResults($data);
        } catch (\Exception $e) {
            return $this->failInternalError($e);
        }
    }

    /**
     * Retrieves the specific PMTs and components for a given module.
     * Used for the Micro (Cell) 3D View.
     */
    public function getModuleDetails(string $locationName): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        // Fetches all PMTs directly associated with this module
        $sql = "
            SELECT 
                e.id as equipment_id,
                e.status_id,
                e.type_params,
                s.name as status_name
            FROM equipments e
            JOIN detector_locations dl ON e.detector_location_id = dl.id
            JOIN equipment_types et ON e.type_id = et.id
            JOIN statuses s ON e.status_id = s.id
            WHERE dl.name = ? 
              AND e.deleted_at IS NULL
              AND et.name LIKE '%PMT%'
        ";
        
        $query = $db->query($sql, [$locationName]);
        $pmts = $query->getResultArray();

        $channels = [];
        
        foreach ($pmts as $pmt) {
            $params = json_decode($pmt['type_params'], true) ?? [];
            
            // phase1_position indicates the physical channel (1 to 48)
            $channel = $params['phase1_position'] ?? null;
            
            if ($channel !== null) {
                $channels[$channel] = [
                    'serial' => $pmt['equipment_id'],
                    'status' => $pmt['status_name'],
                    'qe'     => $params['qe'] ?? 'N/A',
                    'hv'     => $params['nominal_hv'] ?? 'N/A',
                    'model'  => $params['model'] ?? 'N/A'
                ];
            }
        }

        return $this->respondQueryResults($channels);
    }
}