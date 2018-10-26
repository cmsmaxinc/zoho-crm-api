<?php

namespace Cmsmax\ZohoCrmApi;

class Request
{
    protected $baseUrl = 'https://www.zohoapis.com/crm/v2';
    protected $oauthBaseUrl = 'https://accounts.zoho.com/oauth/v2';
    protected $url = '';

    public $method = 'get';
    public $params = [];
    public $data = [];
    public $dataType = 'json';
    public $withAuth = true;

    public function __get($name)
    {
        $this->url .= "/$name";

        return $this;
    }

    public function oauth()
    {
        $this->baseUrl = $this->oauthBaseUrl;

        return $this;
    }

    public function withoutAuth()
    {
        $this->withAuth = false;

        return $this;
    }

    public function param($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function params($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function get()
    {
        $this->method = 'get';
        return $this;
    }

    public function post($data = null)
    {
        $this->method = 'post';

        if ($data) {
            $this->data = $data;
        }

        return $this;
    }

    public function formData()
    {
        $this->dataType = 'form';
    }

    public function url()
    {
        $url = $this->baseUrl . $this->url;

        if (! empty($this->params)) {
            $url .= '?' . http_build_query($this->params);
        }

        return $url;
    }
}
