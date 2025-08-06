<?php

namespace App\Service;

use App\Kernel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AllegroService
{
    private readonly string $AUTH_URL;
    private readonly string $API_BASE_URL;
    private FilesystemAdapter $cache;

    public function __construct(private readonly KernelInterface $kernel, private readonly HttpclientInterface $httpClient, private readonly string $clientId, private readonly string $clientSecret)
    {
        if($this->kernel->getEnvironment() === 'prod')
        {
            $allegroEnvironment = '';
        }
        else
        {
            $allegroEnvironment = '.allegrosandbox.pl';
        }

        $this->AUTH_URL = 'https://allegro.pl' . $allegroEnvironment . '/auth/oauth/token';
        $this->API_BASE_URL = 'https://api.allegro.pl' . $allegroEnvironment;

        $this->cache = new FilesystemAdapter('allegro', 11 * 60 * 60);
    }

    public function getAccessToken(): string
    {
        return $this->cache->get('allegro_device_flow_token', function(ItemInterface $item){
            $response = $this->httpClient->request('POST', $this->AUTH_URL, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/vnd.allegro.public.v1+json',
                ],
                'body' => [
                    'grant_type' => 'client_credentials',
                ]
            ]);

            $data = $response->toArray();

            return $data['access_token'];
        });
    }

    public function request(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        $accessToken = $this->getAccessToken();

        $options['headers']['Authorization'] = 'Bearer ' . $accessToken;
        $options['headers']['Accept'] = 'application/vnd.allegro.public.v1+json';

        return $this->httpClient->request($method, $this->API_BASE_URL . $endpoint, $options);
    }
}
