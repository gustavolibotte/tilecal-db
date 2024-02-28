<?php

namespace App\Controllers;

use Throwable;

class Dev extends APIController
{
    public function headers()
    {
        try{
            return $this->__headers__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }
    public function __headers__()
    {
        $headers = [];
        foreach ($this->request->headers() as $headerName=>$header){
            $headers[$headerName] = [
                'name' => $header->getName(),
                'value' => $header->getValue(),
                'valueLine' => $header->getValueLine()
            ];
        }
        return $this->buildAPIResponse(
            200,
            $headers
        );
    }
}
