<?php

namespace App\Tests\Integration\Broker;

use App\Service\BrokerService;
use Codeception\Test\Unit;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class BrokerServiceTest extends Unit
{
    public function testPublishesThroughRabbitMqManagementApi(): void
    {
        $captured = null;
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured = compact('method', 'url', 'options');

            return new MockResponse('{"routed":true}');
        });

        $service = new BrokerService('http://rabbit.local', 15672, 'user', 'pass', 'pim/prod', $client);
        $service->publishByREST('products', 'product.updated', ['id' => 7]);

        $this->assertSame('POST', $captured['method']);
        $this->assertSame('http://rabbit.local:15672/api/exchanges/pim%2Fprod/products/publish', $captured['url']);
        $this->assertContains('Authorization: Basic ' . base64_encode('user:pass'), $captured['options']['headers']);

        $body = json_decode($captured['options']['body'], true);
        $this->assertSame('product.updated', $body['routing_key']);
        $this->assertSame('string', $body['payload_encoding']);
        $this->assertSame(['id' => 7], json_decode($body['payload'], true));
        $this->assertSame([], $body['properties']);
        $this->assertStringContainsString('"properties":{}', $captured['options']['body'], 'properties must be a JSON object, not an array');
    }
}
