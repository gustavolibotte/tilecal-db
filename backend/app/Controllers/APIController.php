<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use Throwable;

abstract class APIController extends BaseController
{
    use ResponseTrait;

    protected function buildAPIResponse(int $status, ?array $data=[], string $message=''): ResponseInterface
    {
        if (is_null($data)){
            $data = [];
        }
        $res = [
            'status' => $status,
            'data' => $data
        ];
        return $this->respond($res, $status, $message);
    }

    protected function throwError(int $code, Throwable $e, bool $withTrace=false)
    {
        $data = [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ];
        if ($withTrace){
            $data['trace'] = $e->getTrace();
        }
        return $this->fail(
            $data,
            $code,
            $code,
            $e->getMessage()
        );
    }

    protected function parseOrdering(string $varName, string $tableName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {
        $orderBy = $this->request->getVar($varName);
        if ($orderBy == 'updatedAt'){
            $queryBuilder = $queryBuilder->orderBy(sprintf('%s.updated_at', $tableName), 'desc');
        }
        return $queryBuilder;
    }

    protected function parseFilterCondition(string $varName, string $colName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {
        $var = $this->request->getVar($varName);
        if (!empty($var)){
            if (is_array($var)){
                $queryBuilder = $queryBuilder->whereIn($colName, $var);
            } else{
                $queryBuilder = $queryBuilder->where($colName, $var);
            }
        }
        return $queryBuilder;
    }

    protected function parseReturnType(string $varName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {   
        if (!empty($this->request->getVar('relatedEquipmentId'))){
            return $queryBuilder;
        }
        $varValue = $this->request->getVar($varName);
        if (empty($varValue)){
            return $queryBuilder->fullQuery();
        }
        if ($varValue == 'minimal'){
            return $queryBuilder->minQuery();
        } else {
            return $queryBuilder->fullQuery();
        }
    }
}
