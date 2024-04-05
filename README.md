# Planning Center OAuth2 client provider

This package provides [PlanningCenter](https://www.planningcenter.com) integration for [OAuth2 Client](https://github.com/thephpleague/oauth2-client) by the League.

## Installation

```sh
composer require arharp/oauth2-planningcenter
```

## Usage

The following is a basic example of how you would authenticate a user. 

Obtain a client ID and secret by creating an application in your [developer account](https://api.planningcenteronline.com/oauth/applications).

```php
$provider = new Arharp\OAuth2\Client\Provider\PlanningCenter([
    'clientId' => 'CLIENT_ID',
    'clientSecret' => 'SECRET',
    'redirectUri' => 'https://example.org/endpoint',
]);

if (! isset($_GET['code'])) {
    # Get the authorization URL...
    $url = $provider->getAuthorizationUrl(['scope' => 'people giving']);
    
    # Redirect the user...
    header('Location: ' . $url);
    
    exit;
    
} else {
    # Get the access token
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    
    # Do something with the access token...
}
```
