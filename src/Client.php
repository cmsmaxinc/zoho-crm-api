<?php

namespace Cmsmax\ZohoCrmApi;

class Client
{
    protected $config = [];
    protected $request;

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function send($request)
    {
        if ($request instanceof \Closure) {
            $callback = $request;
            $request = new Request();
            call_user_func($callback, $request);
        }

        $this->request = $request;

        return $this->makeRequest();
    }

    public function retry()
    {
        return $this->makeRequest();
    }

    protected function makeRequest()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->request->url());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($this->request->withAuth) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$this->config['access_token']}"]);
        }

        if ($this->request->method === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            $data = $this->request->dataType === 'json'
                ? json_encode(['data' => $this->request->data])
                : $this->request->data;

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $response = new Response($httpCode, $response);

        return $this->processResponse($response);
    }

    protected function processResponse(Response $response)
    {
        if (substr($response->code, 0, 1) == '2') {
            return $response;
        }

        if ($response->code == '401') {
            if ($response->data->code == 'INVALID_TOKEN') {
                throw new InvalidTokenException();
            }

            throw new UnauthorizedException();
        }

        return $response;
    }

    public function getTokens($code, $redirectUri)
    {
        $request = (new Request)->oauth()->token->withoutAuth()->params([
            'code' => $code,
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ])->post();

        $response = $this->send($request);

        return $response->data;
    }

    public function refreshToken($refreshToken = null)
    {
        $request = (new Request)->oauth()->token->withoutAuth()->params([
            'refresh_token' => $refreshToken,
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => 'refresh_token',
        ])->post();

        $response = $this->send($request);

        $this->config['access_token'] = $response->data->access_token;

        return $response->data;
    }
}
