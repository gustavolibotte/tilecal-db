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

    abstract protected function __get__(): ResponseInterface;
    abstract protected function __getHist__(): ResponseInterface;

    public function get(): ResponseInterface
    {
        $preCheck = $this->preRequestChecks();
        if (!is_null($preCheck)){
            return $preCheck;
        }
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function getHist(): ResponseInterface
    {
        $preCheck = $this->preRequestChecks();
        if (!is_null($preCheck)){
            return $preCheck;
        }
        try{
            return $this->__getHist__();
        } catch (Throwable $e){
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function preRequestChecks(): mixed
    {
        if (false){
            log_message('warning', 'API received a non AJAX request');
            $msg = 'Requests must be AJAX.';
            return $this->fail($msg, 400);
        }
        return null;
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
