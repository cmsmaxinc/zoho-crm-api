# Zoho CRM API
A simple package that lets you communicate with Zoho's CRM REST API using a fluent request builder.

See https://www.zoho.com/crm/help/api/v2/ for details about the API.

## Installation

```
composer require cmsmax/zoho-crm-api
```

## Usage
Create a client and pass in your configuration.
```php
use Cmsmax\ZohoCrmApi\Client;
use Cmsmax\ZohoCrmApi\Request;

// Instantiate the client
$config = [
    'client_id' => 'YOUR CLIENT ID',
    'client_secret' => 'YOUR CLIENT SECRET',
    'access_token' => 'YOUR ACCESS TOKEN',
];
$client = new Client($config);

// Build a request
$request = (new Request)->settings->modules->get();

// Send the request
$response = $client->send($request);

// The data is a json-decoded object
$data = $response->data;
```

## Building Requests
Fluently build your API requests following the URL structure of the API.
For example, to make a request to `https://www.zohoapis.com/crm/v2/settings/modules` you would simply do
```php
$request->settings->modules;
```
#### Query Parameters
If you need to include query parameters use the `param()` method. 
```php
$request->settings->fields->param('module', 'Leads');
// https://www.zohoapis.com/crm/v2/settings/fields?module=Leads
```
If you want to attach multiple query parameters at once, pass an array to the `params()` method.
```php
$request->settings->fields->params(['modules' => 'Leads', 'foo' => 'bar']);
// https://www.zohoapis.com/crm/v2/settings/fields?module=Leads&foo=bar
```

#### Build a request using a callback
```php
$response = $client->send(function ($request) {
    $request->settings->modules;
});
```

#### POST Data
Use the `post()` method to submit data.
```php
$request->Leads->post([
    [
        'First_Name' => 'John',
        'Last_Name' => 'Doe',
        'Email' => 'john.doe@email.com',
    ],
]);
```
By default, the data will be submitted as JSON. If for some reason you need to submit the data as regular form data, call the `formData()` method.
```php
$request->post($data)->formData();
```

## Generating Tokens
The client provides a convenient method to generate tokens.
```php
$response = $client->generateTokens($grantToken, $redirectUri);

// Do something with the tokens
$accessToken = $response->access_token;
$refreshToken = $response->refresh_token;
$expiresInSec = $response->expires_in_sec;
$apiDomain = $response->api_domain;
$tokenType = $response->token_type;
$expiresIn = $response->expires_in;
```

## Refreshing Tokens
There is also a convenient method for refreshing tokens.
```php
$response = $client->refreshToken($refreshToken);

$accessToken = $response->access_token;
$expiresInSec = $response->expires_in_sec;
...
```

## Retrying Requests When Token Expires
The access tokens expire after a certain period of time. You can use a try catch block to retry requests in case the token has expired.

```php
$request = new Request;
$request->settings->modules;

try {
    $response = $client->send($request);
} catch (\Cmsmax\ZohoCrmApi\Exceptions\InvalidTokenException $e) {
    $client->refreshToken($refreshToken);
    $response = $client->retry();
}

$data = $response->data;
```
The `refreshToken()` method returns the response data if you need to do something with the newly generated tokens.
```php
$response = $client->refreshToken($refreshToken);

$newAccessToken = $response->access_token;
```

## Handling Errors
There are several exceptions you can catch in order to handle errors gracefully.
```php
try {
    $client->send($request);
} catch (\Cmsmax\ZohoCrmApi\Exceptions\InvalidTokenException $e) {
    // Supplied access token is invalid or expired
} catch (\Cmsmax\ZohoCrmApi\Exceptions\UnauthorizedException $e) {
    // Unathorized to make this request
}
```
Check if a response was successful using the `successful()` method.
```php
if (! $response->successful()) {
    // Handle errors
}
```
