<?php

namespace App\Service;

use DeepL\DeepLClient;
use DeepL\DeepLException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeepLService
{
    private DeeplClient $deepLClient;

    public function __construct(private string $apikey)
    {
        $this->deepLClient = new DeepLClient($this->apikey);
    }

    /**
     * Translate given text
     *
     * @param string $text
     * @param string $targetLanguage
     * @param string|null $sourceLanguage
     * @return string
     * @throws DeepLException
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = null): string
    {
        return $this->deepLClient->translateText($text, $sourceLanguage, $targetLanguage)->text;
    }

    /**
     * Get current usage of service plan
     *
     * @return \DeepL\Usage
     * @throws DeepLException
     */
    public function usage()
    {
        return $this->deepLClient->getUsage();
    }
}
