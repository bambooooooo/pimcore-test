<?php

namespace App\Tests\Integration\Optimik;

use App\Service\OptimikService;
use Codeception\Test\Unit;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class OptimikServiceTest extends Unit
{
    private array $urls = [];

    public function testBulkSheetsQueryUsesBaseUrlAndIds(): void
    {
        $service = $this->service([new MockResponse('[{"sheet":1}]')]);

        $this->assertSame([['sheet' => 1]], $service->getBulkSheets('1,2,3'));
        $this->assertSame('http://optimik.local/orders/sheets?id=1,2,3', $this->urls[0]);
    }

    public function testBulkSheetsCanFilterByProduct(): void
    {
        $service = $this->service([new MockResponse('[]')]);

        $service->getBulkSheets('1', 'ABC');

        $this->assertSame('http://optimik.local/orders/sheets?id=1&searchProduct=ABC', $this->urls[0]);
    }

    public function testBulkSheetsAreCachedPerQuery(): void
    {
        $service = $this->service([new MockResponse('[{"sheet":1}]'), new MockResponse('[{"sheet":2}]')]);

        $first = $service->getBulkSheets('1');
        $cached = $service->getBulkSheets('1');
        $other = $service->getBulkSheets('2');

        $this->assertSame($first, $cached);
        $this->assertSame([['sheet' => 2]], $other);
        $this->assertCount(2, $this->urls);
    }

    public function testUsedSheetsSearchPassesDimensionsAndMaterial(): void
    {
        $service = $this->service([new MockResponse('[]')]);

        $service->getUsedSheets(2800, 2070, 'MDF18');

        $this->assertSame('http://optimik.local/orders/search-sheet?length=2800&width=2070&material=MDF18', $this->urls[0]);
    }

    private function service(array $responses): OptimikService
    {
        $client = new MockHttpClient(function (string $method, string $url) use (&$responses) {
            $this->urls[] = $url;

            return array_shift($responses);
        });

        return new OptimikService($client, 'http://optimik.local', new ArrayAdapter());
    }
}
