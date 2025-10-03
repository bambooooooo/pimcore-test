<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PrestashopService
{
    public function __construct(private readonly string $domain, private readonly string $apikey, private readonly HttpClientInterface $httpClient)
    {

    }

    public function get(string $resource, string $schema='default'): SimpleXMLElement
    {
        $data = [];
        if($schema == 'blank' || $schema == 'synopsis')
        {
            $data['schema'] = $schema;
        }

        $data = $this->httpClient->request("GET", $this->getUrl($resource, $data), [
            'auth_basic' => [$this->apikey, ''],
            'headers' => [
                'Host' => explode(":", $this->domain)[0],
                'Content-Type' => 'application/xml'
            ]
        ])->getContent();

        return new SimpleXMLElement($data);
    }

    public function head(string $resource): int
    {
        return $this->httpClient->request("HEAD", $this->getUrl($resource), [
            'auth_basic' => [$this->apikey, ''],
            'headers' => [
                'Host' => explode(":", $this->domain)[0]
            ]
        ])->getStatusCode();
    }

    public function delete(string $resource): void
    {
        $res = $this->httpClient->request("DELETE", $this->getUrl($resource), [
            'auth_basic' => [$this->apikey, ''],
            'headers' => [
                'Host' => explode(":", $this->domain)[0],
                'Content-Type' => 'application/xml'
            ]
        ]);
    }

    public function post(string $resource, SimpleXMLElement $xml): SimpleXMLElement
    {
        $data = $xml->asXML();
        $data = preg_replace("([\r]|[\n]|[\t])", "", $data);

        $res = $this->httpClient->request("POST", $this->getUrl($resource), [
            'auth_basic' => [$this->apikey, ''],
            'headers' => [
                'Host' => explode(":", $this->domain)[0],
                'Content-Type' => 'application/xml'
            ],
            'body' => $data
        ])->getContent();

        return new SimpleXMLElement($res);
    }

    public function patch(string $resource, SimpleXMLElement $xml): SimpleXMLElement
    {
        $data = $xml->asXML();
        $data = preg_replace("([\r]|[\n]|[\t])", "", $data);

        $res = $this->httpClient->request("PATCH", $this->getUrl($resource), [
            'auth_basic' => [$this->apikey, ''],
            'headers' => [
                'Host' => explode(":", $this->domain)[0],
                'Content-Type' => 'application/xml'
            ],
            'body' => $data
        ])->getContent();

        return new SimpleXMLElement($res);
    }

    public function put(string $resource, SimpleXMLElement $xml, bool $throwsException = true): SimpleXMLElement|null
    {
        try {
            $data = $xml->asXML();
            $data = preg_replace("([\r]|[\n]|[\t])", "", $data);

            $res = $this->httpClient->request("PUT", $this->getUrl($resource), [
                'auth_basic' => [$this->apikey, ''],
                'headers' => [
                    'Host' => explode(":", $this->domain)[0],
                    'Content-Type' => 'application/xml'
                ],
                'body' => $data
            ]);

            return new SimpleXMLElement($res->getContent());
        }
        catch (\Throwable|\Exception $e) {
            if($throwsException)
            {
                throw $e;
            }
        }

        return null;
    }

    public function uploadImage(string $resource, string $filepath, string $method = "POST"): void
    {
        $res = $this->httpClient->request("POST", $this->getUrl($resource), [
            'auth_basic' => [$this->apikey, ''],
            'headers' => [
                'Host' => explode(":", $this->domain)[0]
            ],
            'body' => [
                'ps_method' => $method,
                'image' => fopen($filepath, 'r'),
            ]
        ]);
    }

    private function getUrl($resource, $params = [])
    {
        $url = "https://" . rtrim($this->domain, "/") . "/api/" . rtrim(ltrim($resource, '/'), '/');

        if($params) {
            $url .= "?" . http_build_query($params);
        }

        return $url;
    }

    public function getLinkRewrite(string $name): string
    {
        // Map Polish characters (and some common diacritics)
        $charsMap = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l',
            'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ż' => 'z', 'ź' => 'z',
            'Ą' => 'a', 'Ć' => 'c', 'Ę' => 'e', 'Ł' => 'l',
            'Ń' => 'n', 'Ó' => 'o', 'Ś' => 's', 'Ż' => 'z', 'Ź' => 'z'
        ];

        // Replace Polish chars
        $name = strtr($name, $charsMap);

        // Convert to lowercase (multibyte safe)
        $name = mb_strtolower($name, 'UTF-8');

        // Replace any non-alphanumeric characters with hyphens
        $name = preg_replace('/[^a-z0-9]+/', '-', $name);

        // Trim hyphens from start and end
        $name = trim($name, '-');

        return $name ?: 'n-a'; // Fallback if empty
    }
}
