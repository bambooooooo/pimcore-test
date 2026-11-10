<?php

namespace App\Tests\Integration\DeepL;

use App\Service\DeepLService;
use Codeception\Test\Unit;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

class DeepLServiceTest extends Unit
{
    private array $requests = [];

    public function testTranslatesHtmlBetweenLanguages(): void
    {
        $service = $this->service([new MockResponse(json_encode([
            'translations' => [['detected_source_language' => 'PL', 'text' => '<b>Wardrobe</b>']],
        ]))]);

        $this->assertSame('<b>Wardrobe</b>', $service->translate('<b>Szafa</b>', 'en-GB', 'pl'));

        $sent = json_decode($this->requests[0]['body'], true) ?? [];
        if (!$sent)
        {
            parse_str($this->requests[0]['body'], $sent);
        }
        $this->assertStringEndsWith('/v2/translate', $this->requests[0]['url']);
        $this->assertSame(['<b>Szafa</b>'], (array) $sent['text']);
        $this->assertSame('EN-GB', strtoupper($sent['target_lang']));
        $this->assertSame('PL', strtoupper($sent['source_lang']));
        $this->assertSame('html', $sent['tag_handling']);
    }

    public function testFreeApiKeyUsesFreeEndpoint(): void
    {
        $service = $this->service([new MockResponse('{"character_count":10,"character_limit":500000}')]);

        $usage = $service->usage();

        $this->assertStringStartsWith('https://api-free.deepl.com/', $this->requests[0]['url']);
        $this->assertSame(10, $usage->character->count);
    }

    public function testRenewDateIsConfiguredDayOfCurrentMonth(): void
    {
        $date = (new DeepLService('key:fx', '15'))->renewDate();

        $this->assertSame('15', $date->format('d'));
        $this->assertSame(date('Y-m'), $date->format('Y-m'));
    }

    private function service(array $responses): DeepLService
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses) {
            $this->requests[] = ['method' => $method, 'url' => $url, 'body' => $options['body'] ?? ''];

            return array_shift($responses);
        });

        return new DeepLService('test-key:fx', '1', ['http_client' => new Psr18Client($client)]);
    }
}
