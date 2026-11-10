<?php

namespace App\Tests\Integration\Allegro;

use App\Service\AllegroService;
use Codeception\Test\Unit;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class AllegroServiceTest extends Unit
{
    /** @var array<int, array{method: string, url: string, options: array}> */
    private array $requests = [];

    public function testProductionUsesLiveAllegroHosts(): void
    {
        $service = $this->service('prod', [$this->tokenResponse(), new MockResponse('{}')]);

        $service->request('GET', '/sale/categories');

        $this->assertSame('https://allegro.pl/auth/oauth/token', $this->requests[0]['url']);
        $this->assertSame('https://api.allegro.pl/sale/categories', $this->requests[1]['url']);
    }

    public function testOtherEnvironmentsUseSandbox(): void
    {
        $service = $this->service('dev', [$this->tokenResponse(), new MockResponse('{}')]);

        $service->request('GET', '/sale/categories');

        $this->assertSame('https://allegro.pl.allegrosandbox.pl/auth/oauth/token', $this->requests[0]['url']);
        $this->assertSame('https://api.allegro.pl.allegrosandbox.pl/sale/categories', $this->requests[1]['url']);
    }

    public function testTokenRequestUsesClientCredentials(): void
    {
        $service = $this->service('prod', [$this->tokenResponse('abc')]);

        $this->assertSame('abc', $service->getAccessToken());

        $token = $this->requests[0];
        $this->assertSame('POST', $token['method']);
        $this->assertContains('Authorization: Basic ' . base64_encode('client-id:client-secret'), $token['options']['headers']);
        $this->assertStringContainsString('grant_type=client_credentials', $token['options']['body']);
    }

    public function testTokenIsCachedBetweenRequests(): void
    {
        $service = $this->service('prod', [$this->tokenResponse('abc'), new MockResponse('{}'), new MockResponse('{}')]);

        $service->request('GET', '/a');
        $service->request('GET', '/b');

        $this->assertCount(3, $this->requests, 'token must be fetched only once');
    }

    public function testApiRequestSendsBearerTokenAndAllegroMediaType(): void
    {
        $service = $this->service('prod', [$this->tokenResponse('abc'), new MockResponse('{}')]);

        $service->request('GET', '/sale/categories', ['query' => ['parent.id' => 1]]);

        $headers = $this->requests[1]['options']['headers'];
        $this->assertContains('Authorization: Bearer abc', $headers);
        $this->assertContains('Accept: application/vnd.allegro.public.v1+json', $headers);
        $this->assertSame('https://api.allegro.pl/sale/categories?parent.id=1', $this->requests[1]['url']);
    }

    private function service(string $environment, array $responses): AllegroService
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn($environment);

        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses) {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            return array_shift($responses) ?? new MockResponse('', ['http_code' => 500]);
        });

        return new AllegroService($kernel, $client, 'client-id', 'client-secret', new ArrayAdapter());
    }

    private function tokenResponse(string $token = 'token'): MockResponse
    {
        return new MockResponse(json_encode(['access_token' => $token, 'expires_in' => 43199]));
    }
}
