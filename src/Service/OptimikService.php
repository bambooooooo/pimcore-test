<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OptimikService
{
    public function __construct(private HttpClientInterface $httpClient, private readonly string $url)
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

        return $this->httpClient->request('GET', $url)->toArray();
    }

    public function getUsedSheets(int $l, int $w, string $m)
    {
        return $this->httpClient->request('GET', '/orders/search-sheet?length=' . $l . '&width=' . $w . '&material=' . $m)->toArray();
    }
}
