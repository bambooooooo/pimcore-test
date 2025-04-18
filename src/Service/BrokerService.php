<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrokerService
{
    public function __construct(
        $host, $port, $user, $pwd, $vhost,
        private HttpClientInterface $httpClient)
    {
        $this->httpClient = $this->httpClient->withOptions((new HttpOptions())
            ->setBaseUri($host . ':' . $port . '/api/exchanges/' . urlencode($vhost) . "/")
            ->setAuthBasic($user, $pwd)
            ->toArray()
        );
    }

    public function publishByREST($exchange, $routingKey, $payload)
    {
        $payload = [
            "properties" => new \stdClass(),
            "routing_key" => $routingKey,
            "payload" => json_encode($payload),
            "payload_encoding" => "string"
        ];

        $this->httpClient->request(
            'POST',
            "{$exchange}/publish",
            [
                'body' => json_encode($payload)
            ]
        );
    }
}
