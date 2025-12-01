<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OptimikService
{
    public function __construct(private HttpClientInterface $httpClient, private readonly string $url, private readonly CacheInterface $cache)
    {
        $this->httpClient = $this->httpClient->withOptions((new HttpOptions())
            ->setBaseUri($this->url)
            ->toArray()
        );
    }

    public function getBulkSheets(string $ids, string $product = null)
    {
        $url = '/orders/sheets?id=' . $ids;

        if($product){
            $url .= '&searchProduct=' . $product;
        }

        return $this->cache->get('optimik_' . md5($url), function (ItemInterface $item) use ($url){
            $item->expiresAfter(60 * 60 * 24);
            return $this->httpClient->request('GET', $url)->toArray();
        });
    }

    public function getUsedSheets(int $l, int $w, string $m)
    {
        return $this->httpClient->request('GET', '/orders/search-sheet?length=' . $l . '&width=' . $w . '&material=' . $m)->toArray();
    }
}
