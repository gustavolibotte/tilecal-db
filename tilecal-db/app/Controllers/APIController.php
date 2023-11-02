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

    protected function checkAjaxRequest(): ?string
    {
        if (!$this->request->isAJAX()){
            log_message('warning', 'API received a non AJAX request');
            return 'Requests must be AJAX.';
        }
        return null;
    }

    public function get(): ResponseInterface
    {
        $msg = $this->checkAjaxRequest();
        if (!empty($msg)){
            return $this->fail($msg, 400);
        }
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->fail($e->getMessage(), 500);
        }
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
