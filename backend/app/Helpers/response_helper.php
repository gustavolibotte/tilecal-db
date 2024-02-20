<?php
use CodeIgniter\HTTP\ResponseInterface;

function buildAPIResponse(
    int $status,
    ?array $data = null,
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