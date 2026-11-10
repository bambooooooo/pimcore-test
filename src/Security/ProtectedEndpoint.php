<?php

namespace App\Security;

/**
 * Marks a controller action (or every action of a controller) that changes data.
 * Callers need a Pimcore admin session or a valid X-Api-Token header (see ApiTokenRegistry).
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class ProtectedEndpoint
{
}
