<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SubiektGTService
{
    public function __construct(private HttpClientInterface $httpClient, $host, $port)
    {
        $this->httpClient = $this->httpClient->withOptions((new HttpOptions())
            ->setBaseUri($host . ':' . $port . '/')
            ->toArray()
        );
    }

    public function request($method, $endpoint, $payload)
    {
        $res = $this->httpClient->request($method, $endpoint, [
            'body' => json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
                'body' => json_encode($payload),
            ]
        ]);

        return $res;
    }
}
