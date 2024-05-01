<?php
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Build an API response
 * ```php
 * $response = buildAPIResponse(
 *    200,
 *    ['id' => 1],
 *    'Ok',
 *    ['input' => $inputData]
 * );
 * ```
 * The response has a json with the following format:
 * ```json
 * {
 *   "status": 200,
 *   "message": "Ok",
 *   "data": {
 *      "id": 1
 *   },
 *   "input": {
 *   "name": "John Doe",
 *   "email": ""
 *   }
 * }
 * ```
 * @param int $status HTTP status code
 * @param mixed|null $data Data to return
 * @param string $message Message to return
 * @param array $metadata Additional metadata to return
 * @return ResponseInterface
 * 
 */
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

/**
 * Return a 403 Forbidden response
 * The response has a json with the following format:
 * ```json
 * {
 *  "status": 403,
 * "message": "Forbidden",
 * "data": null
 * }
 * ```
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function failForbidden(): ResponseInterface
{
    return buildAPIResponse(403, message: 'Forbidden');
}

/**
 * Return a 401 Unauthorized response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 401,
 * "message": "Unauthorized",
 * "data": null
 * }
 * ```
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function failUnauthorized(): ResponseInterface
{
    return buildAPIResponse(
        401,
        message: 'Unauthorized'
    );
}

/**
 * Return a 400 Bad Request response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 400,
 * "message": "Bad Request",
 * "data": null
 * }
 * ```
 * @param mixed|null $data Data to return
 * @param string $message Message to return
 * @param array $metadata Additional metadata to return
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function failBadRequest(mixed $data = null, string $message = '', array $metadata = []): ResponseInterface
{
    return buildAPIResponse(
        400,
        $data,
        empty($message)? 'Bad Request': $message,
        $metadata
    );
}

/**
 * Return a 500 Internal Server Error response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 500,
 * "message": "",
 * "data": null
 * }
 * ```
 * If the environment is safe the response will include the line, file and trace of the error
 * as the json bellow:
 * ```json
 * {
 * "status": 500,
 * "message": "Internal Server Error",
 * "data": {
 * "line": 10,
 * "file": "path/to/file",
 * "trace": []
 * }
 * }
 * ```
 * @param Throwable $e Exception that caused the error
 * @return ResponseInterface
 * @see buildAPIResponse
 */
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

/**
 * Add CORS headers to a response
 * @param ResponseInterface $response Response to add the headers
 * @return ResponseInterface
 */
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

/**
 * Return a query result response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 200,
 * "message": "Ok",
 * "data": [
 * {
 * "id": 1,
 * "name": "John Doe"
 * }
 * ],
 * "input": {
 * "name": "John Doe",
 * "email": ""
 * },
 * "validInput": {
 * "name": "John Doe"
 * }
 * }
 * ```
 * If the environment is not safe the input and validInput keys will not be present
 * @param array|null $data Data to return
 * @param array $inputData Input data used to generate the response
 * @param array $validInput Validated input data used to generate the response
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function respondQueryResults(?array $data = [], array $inputData=[], array $validInput=[]): ResponseInterface
{
    $data = empty($data)? []: $data;
    $env = \Config\Services::environment();
    if ($env->isSafe()) {
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

/**
 * Return a validation error response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 400,
 * "message": "Invalid data",
 * "data": {
 * "name": "Name is required",
 * "email": "Email is required"
 * },
 * "input": {
 * "name": "John Doe",
 * "email": ""
 * }
 * }
 * ```
 * If the environment is not safe the input key will not be present
 * @param array $errors Validation errors
 * @param array $inputData Input data used to generate the response
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function failValidationErrors(array $errors, array $inputData): ResponseInterface
{
    $env = \Config\Services::environment();
    if ($env->isSafe()) {
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

/**
 * Return an Updated response
 * The response has a json with the following format:
 * ```json
 *  {
 * "status": 200,
 * "message": "Updated",
 * "data": [
 * 1
 * ],
 * "input": {
 * "name": "John Doe",
 * "email": ""
 * },
 * "validInput": {
 * "name": "John Doe"
 * }
 * }
 * ```
 * If the environment is not safe the input and validInput keys will not be present
 * @param array|int|string $id Id of the updated resource
 * @param array $inputData Input data used to generate the response
 * @param array $validInput Validated input data used to generate the response
 * @return ResponseInterface
 * @see buildAPIResponse
 */
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

/**
 * Return a Created response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 201,
 * "message": "Created",
 * "data": [
 * 1
 * ],
 * "input": {
 * "name": "John Doe",
 * "email": ""
 * },
 * "validInput": {
 * "name": "John Doe"
 * }
 * }
 * ```
 * If the environment is not safe the input and validInput keys will not be present
 * @param array|int|string $id Id of the created resource
 * @param array $inputData Input data used to generate the response
 * @param array $validInput Validated input data used to generate the response
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function respondCreated(array|int|string $id = [], array $inputData=[], array $validInput=[]): ResponseInterface
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
        201,
        $id,
        'Created',
        $metadata
    );
}

/**
 * Return a Deleted response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 200,
 * "message": "Deleted",
 * "data": [
 * 1
 * ],
 * "input": {
 * "name": "John Doe",
 * "email": ""
 * },
 * "validInput": {
 * "name": "John Doe"
 * }
 * }
 * ```
 * If the environment is not safe the input and validInput keys will not be present
 * @param array|int|string $id Id of the deleted resource
 * @param array $inputData Input data used to generate the response
 * @param array $validInput Validated input data used to generate the response
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function respondDeleted(array|int|string $id = [], array $inputData=[], array $validInput=[]): ResponseInterface
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
        'Deleted',
        $metadata
    );
}

function respondRestored(array|int|string $id=[], array $inputData=[], array $validInput=[]): ResponseInterface
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
        'Restored',
        $metadata
    );
}

/**
 * Return a not found response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 404,
 * "message": "Not Found",
 * "data": null
 * }
 * ```
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function failNotFound(): ResponseInterface
{
    return buildAPIResponse(404, message: 'Not Found');
}

/**
 * Return a service unavailable response
 * The response has a json with the following format:
 * ```json
 * {
 * "status": 503,
 * "message": "Service Unavailable",
 * "data": null
 * }
 * ```
 * @return ResponseInterface
 * @see buildAPIResponse
 */
function failServiceUnavailable(): ResponseInterface
{
    return buildAPIResponse(503, message:'Service Unavailable');
}