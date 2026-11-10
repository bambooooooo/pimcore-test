<?php

namespace App\Tests\Integration\Baselinker;

use App\Service\BaselinkerService;
use Codeception\Test\Unit;
use Pimcore\Model\DataObject\Baselinker;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class BaselinkerServiceTest extends Unit
{
    private array $requests = [];

    public function testGetCallsConnectorWithTokenMethodAndJsonParameters(): void
    {
        $service = $this->service([new MockResponse('{"status":"SUCCESS","inventories":[]}')]);

        $data = $service->get($this->account(), 'getInventoryProductsList', ['inventory_id' => 12]);

        $this->assertSame(['status' => 'SUCCESS', 'inventories' => []], $data);
        $request = $this->requests[0];
        $this->assertSame('POST', $request['method']);
        $this->assertSame('https://api.baselinker.com/connector.php', $request['url']);
        $this->assertContains('X-BLToken: bl-token', $request['options']['headers']);

        parse_str($request['options']['body'], $body);
        $this->assertSame('getInventoryProductsList', $body['method']);
        $this->assertSame(['inventory_id' => 12], json_decode($body['parameters'], true));
    }

    public function testErrorStatusBecomesException(): void
    {
        $service = $this->service([new MockResponse('{"status":"ERROR","error_code":"ERROR_BAD_TOKEN","error_message":"Invalid token"}')]);

        $this->expectExceptionMessage('ERROR_BAD_TOKEN: Invalid token');
        $service->get($this->account(), 'getInventories');
    }

    public function testPingSucceedsForValidAccount(): void
    {
        $service = $this->service([new MockResponse('{"status":"SUCCESS"}')]);

        $this->assertTrue($service->ping($this->account()));

        parse_str($this->requests[0]['options']['body'], $body);
        $this->assertSame('getInventories', $body['method']);
    }

    public function testPingFailsForRejectedAccount(): void
    {
        $service = $this->service([new MockResponse('{"status":"ERROR","error_code":"ERROR_BAD_TOKEN","error_message":"Invalid token"}')]);

        $this->expectException(\Exception::class);
        $service->ping($this->account());
    }

    private function account(): Baselinker
    {
        return new class extends Baselinker {
            public function getApiKey(): ?string { return 'bl-token'; }
        };
    }

    private function service(array $responses): BaselinkerService
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses) {
            $this->requests[] = compact('method', 'url', 'options');

            return array_shift($responses);
        });

        return new BaselinkerService($client);
    }
}
