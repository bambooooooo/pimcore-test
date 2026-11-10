<?php

namespace App\Tests\Integration\SubiektGT;

use App\Service\SubiektGTService;
use Codeception\Test\Unit;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SubiektGTServiceTest extends Unit
{
    public function testSendsJsonPayloadToHostAndPort(): void
    {
        $captured = null;
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured = compact('method', 'url', 'options');

            return new MockResponse('{"ok":true}');
        });

        $service = new SubiektGTService($client, 'http://sgt.local', 8080);
        $response = $service->request('POST', 'products', ['symbol' => 'ABC', 'name' => 'Szafka ŁÓŻKO']);

        $this->assertSame(['ok' => true], $response->toArray());
        $this->assertSame('POST', $captured['method']);
        $this->assertSame('http://sgt.local:8080/products', $captured['url']);
        $this->assertContains('Content-Type: application/json', $captured['options']['headers']);
        $this->assertSame(['symbol' => 'ABC', 'name' => 'Szafka ŁÓŻKO'], json_decode($captured['options']['body'], true));
    }
}
