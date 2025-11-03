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

    public function getBulkSheets(string $ids)
    {
        return $this->httpClient->request('GET', '/orders/sheets?id=' . $ids)->toArray();
    }
}
