<?php
use CodeIgniter\HTTP\ResponseInterface;

function buildAPIResponse(
    int $status,
    mixed $data = null,
    string $message = '',
    array $metadata = []
): ResponseInterface {
    $res = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];
    $res = array_merge($res, $metadata);
    $response = \Config\Services::response();
    return ($response
        ->setJSON($res)
        ->setStatusCode($status, $message));
}

function failForbidden(): ResponseInterface
{
    return buildAPIResponse(403, message: 'Forbidden');
}

function failUnauthorized(): ResponseInterface
{
    return buildAPIResponse(
        401,
        message: 'Unauthorized'
    );
}

function failBadRequest(mixed $data = null, string $message = '', array $metadata = []): ResponseInterface
{
    return buildAPIResponse(
        400,
        $data,
        empty($message)? 'Bad Request': $message,
        $metadata
    );
}

function failInternalError(Throwable $e)
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
    return buildAPIResponse(500, $data, $message);
}

function addCorsHeaders(ResponseInterface $response): ResponseInterface
{
    $response->setHeader(
        'Access-Control-Allow-Origin',
        '*'
    );
    $response->setHeader(
        'Access-Control-Allow-Methods',
        'GET, POST, OPTIONS, PUT, DELETE, PATCH'
    );
    $response->setHeader(
        'Access-Control-Allow-Headers',
        '*'
    );
    $response->setHeader(
        'Access-Control-Allow-Credentials',
        'true'
    );
    $response->setHeader(
        'Access-Control-Expose-Headers',
        '*'
    );
    return $response;
}

function respondQueryResults(?array $data = [], array $inputData=[], array $validInput=[]): ResponseInterface
{
    $data = empty($data)? []: $data;
    $env = \Config\Services::environment();
    if ($env->isLocal() || $env->isDev()) {
        $metadata = [
            'input' => $inputData,
            'validInput' => $validInput
        ];
    } else{
        $metadata = [];
    }
    return buildAPIResponse(
        200,
        $data,
        'Ok',
        $metadata
    );
}

function failValidationErrors(array $errors, array $inputData): ResponseInterface
{
    $env = \Config\Services::environment();
    if ($env->isLocal() || $env->isDev()) {
        $metadata = [
            'input' => $inputData
        ];
    } else{
        $metadata = [];
    }
    return buildAPIResponse(
        400,
        $errors,
        'Invalid data',
        $metadata
    );
}

function respondUpdated(array|int|string $id = [], array $inputData=[], array $validInput=[]): ResponseInterface
{
    if (!is_array($id)){
        $id = [$id];
    }
    $env = \Config\Services::environment();
    if ($env->isLocal() || $env->isDev()) {
        $metadata = [
            'input' => $inputData,
            'validInput' => $validInput
        ];
    } else{
        $metadata = [];
    }
    return buildAPIResponse(
        200,
        $id,
        'Updated',
        $metadata
    );
}