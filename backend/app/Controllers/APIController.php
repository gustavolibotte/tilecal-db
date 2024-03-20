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

    protected function failValidationError(string $message): ResponseInterface
    {
        return $this->buildAPIResponse(
            400,
            [$message],
            'Invalid data'
        );
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
}
