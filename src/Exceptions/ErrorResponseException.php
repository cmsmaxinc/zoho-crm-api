<?php

namespace Cmsmax\ZohoCrmApi\Exceptions;

class ErrorResponseException extends \Exception
{
    protected $errorCode;
    protected $response;

    public function __construct($errorCode, $response)
    {
        $this->errorCode = $errorCode;
        $this->response = $response;

        parent::__construct("API returned error response: " . $this->errorCode);
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
