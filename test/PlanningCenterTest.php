<?php

namespace Arharp\OAuth2\Client\Test\Provider;

use Arharp\OAuth2\Client\Provider\PlanningCenter;
use Arharp\OAuth2\Client\Provider\PlanningCenterResourceOwner;
use PHPUnit\Framework\TestCase;

class PlanningCenterTest extends TestCase
{
    /**
     * @var PlanningCenter
     */
    private $provider;

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertEquals('api.planningcenteronline.com', $uri['host']);
        $this->assertEquals('/oauth/authorize', $uri['path']);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertEquals('code', $query['response_type']);

        $this->assertNotNull($this->provider->getState());
    }

    public function testGetBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);

        $this->assertEquals('https://api.planningcenteronline.com/oauth/token', $url);
    }

    public function testGetAccessToken()
    {
        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');

        $response->expects($this->any())
            ->method('getHeader')
            ->willReturn(['content-type' => 'json']);

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);

        $client = $this->getMock('GuzzleHttp\ClientInterface');
        $this->provider->setHttpClient($client);

        $client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertGreaterThan(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    /**
     * @expectedException \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @expectedExceptionCode 400
     * @expectedExceptionMessage bad_verification_code: Error message
     */
    public function testThrowExceptionWhenCouldNotGetAccessToken()
    {
        $response = $this->getMock('Psr\Http\Message\ResponseInterface');

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn('{"error_description":"Error message","error":"bad_verification_code"}');

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(400);

        $client = $this->getMock('GuzzleHttp\ClientInterface');
        $this->provider->setHttpClient($client);

        $client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $token = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getToken')
            ->willReturn('mock_access_token');

        $url = $this->provider->getResourceOwnerDetailsUrl($token);

        $this->assertEquals('https://api.planningcenteronline.com/people/v2/me', $url);
    }

    public function testGetResourceOwner()
    {
        $response = json_decode('{"id":"1000034426","name":"User Name"}', true);

        $token = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = $this->getMockBuilder(PlanningCenter::class)
            ->setMethods(array('fetchResourceOwnerDetails'))
            ->getMock();

        $provider->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->identicalTo($token))
            ->willReturn($response);

        /** @var PlanningCenterResourceOwner $resource */
        $resource = $provider->getResourceOwner($token);

        $this->assertInstanceOf(PlanningCenterResourceOwner::class, $resource);

        $this->assertEquals('1000034426', $resource->getId());
        $this->assertEquals('User Name', $resource->getName());
    }

    protected function setUp(): void
    {
        $this->provider = new PlanningCenter([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
        ]);
    }

    protected function tearDown(): void
    {
        $this->provider = null;
    }
}
