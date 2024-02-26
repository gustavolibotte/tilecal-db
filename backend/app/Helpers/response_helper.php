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