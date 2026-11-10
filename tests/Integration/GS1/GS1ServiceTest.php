<?php

namespace App\Tests\Integration\GS1;

use App\Service\GS1Service;
use Codeception\Test\Unit;
use Pimcore\Model\DataObject\EanPool;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GS1ServiceTest extends Unit
{
    /**
     * @dataProvider knownEans
     */
    public function testCalculatesEan13CheckDigit(string $ean13): void
    {
        $this->assertSame(substr($ean13, -1), $this->service()->calculateEAN13CheckDigit(substr($ean13, 0, 12)));
    }

    public static function knownEans(): array
    {
        return [
            ['5901234123457'],
            ['4006381333931'],
            ['9780201379624'],
            ['5900000000008'],
        ];
    }

    public function testRejectsNonNumericOrWrongLengthInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service()->calculateEAN13CheckDigit('59012341234A');
    }

    public function testGeneratesEveryValidEanForPrefix(): void
    {
        $eans = $this->service()->getValidEans13WithPrefix('59012345678');

        $this->assertCount(10, $eans);
        $this->assertSame('5901234567800', $eans[0]);
        foreach ($eans as $ean)
        {
            $this->assertMatchesRegularExpression('/^59012345678\d{2}$/', $ean);
            $this->assertSame(substr($ean, -1), $this->service()->calculateEAN13CheckDigit(substr($ean, 0, 12)));
        }
    }

    /**
     * @dataProvider invalidPrefixes
     */
    public function testRejectsInvalidPrefix(string $prefix): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service()->getValidEans13WithPrefix($prefix);
    }

    public static function invalidPrefixes(): array
    {
        return [['5'], ['5901234567890']];
    }

    public function testRequiresCredentials(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new GS1Service(new MockHttpClient(), '', 'secret');
    }

    public function testFreeCodesExcludeEveryCodeTakenInGs1AcrossAllPages(): void
    {
        $requests = [];
        $pages = [
            ['data' => [['id' => '05901234567800'], ['id' => '05901234567817']], 'links' => ['next' => 'https://mojegs1.pl/api/v2/products?page=2']],
            ['data' => [['id' => '05901234567824']], 'links' => ['next' => null]],
        ];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests, &$pages) {
            $requests[] = compact('url', 'options');

            return new MockResponse(json_encode(array_shift($pages)));
        });

        $pool = new class extends EanPool {
            public array $saved = [];
            public function getPrefix(): ?string { return '59012345678'; }
            public function setAvailableCodes(?array $availableCodes): static { $this->saved = $availableCodes; return $this; }
            public function save(array $parameters = []): static { return $this; }
        };

        (new GS1Service($client, 'user', 'pass'))->updateFreeCodes($pool);

        $this->assertCount(2, $requests);
        $this->assertStringContainsString('filter[keyword]=59012345678', urldecode($requests[0]['url']));
        $this->assertContains('Authorization: Basic ' . base64_encode('user:pass'), $requests[0]['options']['headers']);

        $free = array_column($pool->saved, 0);
        $this->assertCount(7, $free);
        $this->assertNotContains('5901234567800', $free);
        $this->assertNotContains('5901234567817', $free);
        $this->assertNotContains('5901234567824', $free);
    }

    private function service(): GS1Service
    {
        return new GS1Service(new MockHttpClient(), 'user', 'pass');
    }
}
