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

    protected function buildAPIResponse(
        int $status,
        ?array $data = null,
        string $message = '',
        array $metadata = []
    ): ResponseInterface {
        $res = [
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
        $res = array_merge($res, $metadata);
        return $this->respond($res, $status);
    }

    protected function fail(
        ?array $data = null,
        string $message = '',
        array $metadata = []
    ): ResponseInterface {
        return $this->buildAPIResponse(
            400,
            $data,
            $message,
            $metadata
        );
    }

    protected function respondCreated()
    {
        throw new \BadMethodCallException('respondCreated is not Implemented');
    }

    protected function respondDeleted()
    {
        throw new \BadMethodCallException('respondDeleted is not Implemented');
    }

    protected function respondNoContent()
    {
        throw new \BadMethodCallException('respondNoContent is not Implemented');
    }

    protected function failUnauthorized(): ResponseInterface
    {
        return $this->buildAPIResponse(
            401,
            message: 'Unauthorized.'
        );
    }

    protected function failForbidden(): ResponseInterface
    {
        return $this->buildAPIResponse(
            403,
            message: 'Forbidden.'
        );
    }

    public function failNotFound(): ResponseInterface
    {
        return $this->buildAPIResponse(404, message: 'Not Found.');
    }

    protected function failValidationError(array $validationErrors): ResponseInterface
    {
        return $this->buildAPIResponse(
            400,
            $validationErrors,
            'Invalid data.'
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

    protected function throwError(Throwable $e)
    {
        $environment = \Config\Services::environment();
        if ($environment->isSafe()){
            $data = [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ];
            $message = $e->getMessage();
        } else{
            $data = null;
            $message = '';
        }
        return $this->buildAPIResponse(500, $data, $message);
    }

    protected function parseOrdering(string $varName, string $tableName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {
        $orderBy = $this->request->getVar($varName);
        if ($orderBy == 'updatedAt') {
            $queryBuilder = $queryBuilder->orderBy(sprintf('%s.updated_at', $tableName), 'desc');
        }
        return $queryBuilder;
    }

    protected function parseFilterCondition(string $varName, string $colName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {
        $var = $this->request->getVar($varName);
        if (!empty($var)) {
            if (is_array($var)) {
                $queryBuilder = $queryBuilder->whereIn($colName, $var);
            } else {
                $queryBuilder = $queryBuilder->where($colName, $var);
            }
        }
        return $queryBuilder;
    }

    protected function parseReturnType(string $varName, BaseBuilder|Model $queryBuilder): BaseBuilder|Model
    {
        if (!empty($this->request->getVar('relatedEquipmentId'))) {
            return $queryBuilder;
        }
        $varValue = $this->request->getVar($varName);
        if (empty($varValue)) {
            return $queryBuilder->fullQuery();
        }
        if ($varValue == 'minimal') {
            return $queryBuilder->minQuery();
        } else {
            return $queryBuilder->fullQuery();
        }
    }
}
