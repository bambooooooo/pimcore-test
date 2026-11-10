<?php

namespace App\Tests\Integration\Prestashop;

use App\Service\PrestashopService;
use Codeception\Test\Unit;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PrestashopServiceTest extends Unit
{
    private array $requests = [];

    public function testGetBuildsWebserviceUrlAndParsesXml(): void
    {
        $service = $this->service(true, [new MockResponse('<prestashop><product><id>5</id></product></prestashop>')]);

        $xml = $service->get('/products/5/', 'synopsis', ['display' => 'full']);

        $this->assertSame('5', (string) $xml->product->id);
        $this->assertSame('GET', $this->requests[0]['method']);
        $this->assertSame('https://ps.local/api/products/5?schema=synopsis&display=full', $this->requests[0]['url']);
    }

    public function testDefaultSchemaIsNotSent(): void
    {
        $service = $this->service(false, [new MockResponse('<prestashop/>')]);

        $service->get('products');

        $this->assertSame('http://ps.local/api/products', $this->requests[0]['url']);
    }

    public function testAuthenticatesWithApiKeyAndShopDomainHost(): void
    {
        $service = $this->service(true, [new MockResponse('', ['http_code' => 200])]);

        $this->assertSame(200, $service->head('products'));

        $headers = $this->requests[0]['options']['headers'];
        $this->assertContains('Authorization: Basic ' . base64_encode('api-key:'), $headers);
        $this->assertContains('Host: shop.example.com', $headers);
    }

    public function testPostSendsXmlWithoutLineBreaks(): void
    {
        $service = $this->service(true, [new MockResponse('<prestashop><product><id>9</id></product></prestashop>')]);
        $xml = new \SimpleXMLElement("<prestashop>\n\t<product>\n\t\t<name>Szafa</name>\n\t</product>\n</prestashop>");

        $result = $service->post('products', $xml, ['id_shop' => 2]);

        $this->assertSame('9', (string) $result->product->id);
        $this->assertSame('https://ps.local/api/products?id_shop=2', $this->requests[0]['url']);
        $this->assertStringNotContainsString("\n", $this->requests[0]['options']['body']);
        $this->assertStringContainsString('<name>Szafa</name>', $this->requests[0]['options']['body']);
    }

    public function testDeleteTargetsShopGroup(): void
    {
        $service = $this->service(true, [new MockResponse('', ['http_code' => 200])]);

        $this->assertSame(200, $service->delete('products/5'));
        $this->assertSame('DELETE', $this->requests[0]['method']);
        $this->assertSame('https://ps.local/api/products/5?id_shop_group=1', $this->requests[0]['url']);
    }

    public function testPutCanSwallowErrors(): void
    {
        $service = $this->service(true, [new MockResponse('error', ['http_code' => 500])]);

        $this->assertNull($service->put('products/5', new \SimpleXMLElement('<prestashop/>'), false));
    }

    public function testPutThrowsByDefault(): void
    {
        $service = $this->service(true, [new MockResponse('error', ['http_code' => 500])]);

        $this->expectException(\Throwable::class);
        $service->put('products/5', new \SimpleXMLElement('<prestashop/>'));
    }

    /**
     * @dataProvider linkRewrites
     */
    public function testLinkRewriteIsUrlSafeSlug(string $name, string $expected): void
    {
        $this->assertSame($expected, $this->service(true, [])->getLinkRewrite($name));
    }

    public static function linkRewrites(): array
    {
        return [
            'polish characters' => ['Zażółć gęślą jaźń', 'zazolc-gesla-jazn'],
            'uppercase polish' => ['ŁÓŻKO ŚLĄSKIE', 'lozko-slaskie'],
            'punctuation collapses' => ['Szafa  80/200 -- biała!', 'szafa-80-200-biala'],
            'empty falls back' => ['---', 'n-a'],
        ];
    }

    private function service(bool $ssl, array $responses): PrestashopService
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses) {
            $this->requests[] = compact('method', 'url', 'options');

            return array_shift($responses);
        });

        return new PrestashopService('ps.local', 'shop.example.com', 'api-key', $ssl, $client);
    }
}
