<?php

namespace App\Tests\Unit\Service;

use App\Service\GotenbergPdfService;
use Codeception\Test\Unit;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Covers the request sent to Gotenberg - the contract the factory printouts rely on.
 */
class GotenbergPdfServiceTest extends Unit
{
    private ?string $url = null;
    private array $fields = [];
    private string $html = '';

    public function testPostsHtmlToChromiumEndpointAndReturnsPdf(): void
    {
        $pdf = $this->render('<h1>Test</h1>', ['paperWidth' => '210mm']);

        $this->assertSame('%PDF-fake', $pdf);
        $this->assertSame('http://gotenberg.local:3000/forms/chromium/convert/html', $this->url);
        $this->assertStringContainsString('<h1>Test</h1>', $this->html);
    }

    public function testDefaultsMatchFormerWebToPrintProcessor(): void
    {
        $this->render('<p>x</p>');

        $this->assertSame('1', $this->fields['printBackground'] ?? null);
        $this->assertArrayNotHasKey('landscape', $this->fields);
        $this->assertSame('0.39', $this->fields['marginTop']);
        $this->assertSame('0.39', $this->fields['marginRight']);
        $this->assertArrayNotHasKey('paperWidth', $this->fields);
    }

    public function testPassesPaperSizeMarginsAndMetadata(): void
    {
        $this->render('<p>x</p>', [
            'paperWidth' => '210mm',
            'paperHeight' => '297mm',
            'marginTop' => '3mm',
            'marginBottom' => 0,
            'marginLeft' => '3mm',
            'marginRight' => '3mm',
            'landscape' => true,
            'metadata' => ['Title' => 'Harmonogram', 'Author' => 'pim'],
        ]);

        $this->assertSame('210mm', $this->fields['paperWidth']);
        $this->assertSame('297mm', $this->fields['paperHeight']);
        $this->assertSame('3mm', $this->fields['marginTop']);
        $this->assertSame('0', $this->fields['marginBottom']);
        $this->assertSame('1', $this->fields['landscape']);
        $this->assertSame(['Title' => 'Harmonogram', 'Author' => 'pim'], json_decode($this->fields['metadata'], true));
        $this->assertArrayNotHasKey('printBackground', $this->fields, 'explicit params replace the defaults');
    }

    public function testRelativeAssetUrlsPointToAssetHost(): void
    {
        $this->render('<img src="/var/assets/a.png"><link href="/static/css/print.css" rel="stylesheet"><a href="https://example.com/x">x</a>');

        $this->assertStringContainsString('src="http://nginx:80/var/assets/a.png"', $this->html);
        $this->assertStringContainsString('href="http://nginx:80/static/css/print.css"', $this->html);
        $this->assertStringContainsString('href="https://example.com/x"', $this->html, 'absolute URLs stay untouched');
    }

    private function render(string $html, array $params = []): string
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options) {
            $this->url = $url;
            $this->parseMultipart($options);

            return new MockResponse('%PDF-fake', ['response_headers' => ['Content-Type' => 'application/pdf']]);
        });

        $service = new GotenbergPdfService('http://gotenberg.local:3000', 'http://nginx:80', new Psr18Client($client));

        return $service->getPdfFromString($html, $params);
    }

    private function parseMultipart(array $options): void
    {
        $contentType = implode("\n", $options['headers']);
        preg_match('/boundary=([^\s;]+)/', $contentType, $m);
        $body = is_string($options['body']) ? $options['body'] : implode('', iterator_to_array(($options['body'])()));

        foreach (explode('--' . trim($m[1], '"'), $body) as $part)
        {
            if (!preg_match('/name="([^"]+)"(; filename="[^"]*")?.*?\r\n\r\n(.*)\r\n$/s', $part, $p))
            {
                continue;
            }

            $p[2] ? $this->html = $p[3] : $this->fields[$p[1]] = $p[3];
        }
    }
}
