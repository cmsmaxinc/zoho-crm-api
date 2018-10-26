<?php

namespace Cmsmax\ZohoCrmApi;

class Response
{
    public $code;
    public $data;

    public function __construct($code, $data)
    {
        $this->code = $code;
        $this->data = is_string($data) ? json_decode($data) : $data;
    }

    public function successful()
    {
        if (! substr($this->code, 0, 1) == '2') {
            return false;
        }

        if (isset($this->data->error) && ! empty($this->data->error)) {
            return false;
        }

        if (isset($this->data->status) && $this->data->status == 'error') {
            return false;
        }

        return true;
    }
}
