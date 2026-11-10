<?php

namespace App\Security;

/**
 * API tokens for external systems, configured as API_TOKENS="client1:token1,client2:token2".
 */
class ApiTokenRegistry
{
    public const HEADER = 'X-Api-Token';

    /** @var array<string, string> client name => token */
    private array $tokens = [];

    public function __construct(string $apiTokens)
    {
        foreach (array_filter(array_map('trim', explode(',', $apiTokens))) as $entry)
        {
            [$client, $token] = array_pad(explode(':', $entry, 2), 2, '');

            if ($client === '' || $token === '')
            {
                throw new \InvalidArgumentException("Invalid API_TOKENS entry [$client] - expected client:token");
            }

            $this->tokens[$client] = $token;
        }
    }

    /**
     * Returns the client name owning the token, or null if the token is unknown.
     */
    public function getClient(?string $token): ?string
    {
        if ($token === null || $token === '')
        {
            return null;
        }

        foreach ($this->tokens as $client => $known)
        {
            if (hash_equals($known, $token))
            {
                return $client;
            }
        }

        return null;
    }
}
