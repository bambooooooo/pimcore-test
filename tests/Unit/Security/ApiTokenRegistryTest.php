<?php

namespace App\Tests\Unit\Security;

use App\Security\ApiTokenRegistry;
use Codeception\Test\Unit;

class ApiTokenRegistryTest extends Unit
{
    public function testResolvesClientByToken(): void
    {
        $registry = new ApiTokenRegistry('erp:secret-1, shop:secret-2');

        $this->assertSame('erp', $registry->getClient('secret-1'));
        $this->assertSame('shop', $registry->getClient('secret-2'));
    }

    public function testRejectsUnknownAndEmptyTokens(): void
    {
        $registry = new ApiTokenRegistry('erp:secret-1');

        $this->assertNull($registry->getClient('secret-2'));
        $this->assertNull($registry->getClient('SECRET-1'));
        $this->assertNull($registry->getClient(''));
        $this->assertNull($registry->getClient(null));
    }

    public function testEmptyConfigurationAcceptsNothing(): void
    {
        $registry = new ApiTokenRegistry('');

        $this->assertNull($registry->getClient(''));
        $this->assertNull($registry->getClient('anything'));
    }

    public function testTokenMayContainColons(): void
    {
        $registry = new ApiTokenRegistry('erp:abc:def');

        $this->assertSame('erp', $registry->getClient('abc:def'));
    }

    /**
     * @dataProvider invalidEntries
     */
    public function testRejectsMalformedEntries(string $config): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ApiTokenRegistry($config);
    }

    public static function invalidEntries(): array
    {
        return [
            'missing token' => ['erp:'],
            'missing client' => [':secret'],
            'no separator' => ['erp'],
        ];
    }
}
