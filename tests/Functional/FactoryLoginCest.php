<?php

namespace App\Tests\Functional;

use App\Tests\FunctionalTester;
use Pimcore\Model\User;
use Pimcore\Tool\Authentication;

/**
 * Factory panel login (firewall "factory", session shared with Pimcore Studio).
 */
class FactoryLoginCest
{
    private const PASSWORD = 'factory-secret-123';

    public function anonymousUsersAreSentToTheLoginForm(FunctionalTester $I): void
    {
        $I->stopFollowingRedirects();
        $I->amOnPage('/factory/pl/schedule');

        $I->seeResponseCodeIsRedirection();
        $I->assertStringEndsWith('/factory/login', $I->grabResponseHeader('Location'));
    }

    public function factoryWorkerLandsInTheFactoryPanel(FunctionalTester $I): void
    {
        $this->user('test-factory-worker', admin: false, permissions: ['factory']);

        $this->login($I, 'test-factory-worker', self::PASSWORD);

        $I->seeResponseCodeIsRedirection();
        $I->assertMatchesRegularExpression('#/factory(/pl)?$#', $I->grabResponseHeader('Location'), 'factory_home, "pl" is the default locale');
    }

    public function administratorLandsInStudio(FunctionalTester $I): void
    {
        $this->user('test-factory-admin', admin: true);

        $this->login($I, 'test-factory-admin', self::PASSWORD);

        $I->seeResponseCodeIsRedirection();
        $I->assertStringEndsWith('/pimcore-studio/', $I->grabResponseHeader('Location'));
    }

    public function wrongPasswordShowsTheFormAgain(FunctionalTester $I): void
    {
        $this->user('test-factory-worker', admin: false, permissions: ['factory']);

        $this->login($I, 'test-factory-worker', 'wrong');
        $I->followRedirect();

        $I->seeCurrentUrlEquals('/factory/login');
        $I->assertStringContainsString('<div class="alert alert-danger py-2">Invalid credentials.</div>', $I->grabResponseContent());
    }

    private function login(FunctionalTester $I, string $username, string $password): void
    {
        $I->amOnPage('/factory/login');
        $I->seeResponseCodeIs(200);
        $I->stopFollowingRedirects();
        $I->submitForm('form', ['username' => $username, 'password' => $password]);
    }

    private function user(string $name, bool $admin, array $permissions = []): User
    {
        foreach ($permissions as $permission)
        {
            if (!User\Permission\Definition::getByKey($permission))
            {
                User\Permission\Definition::create($permission);
            }
        }

        User::getByName($name)?->delete();

        $user = User::create([
            'parentId' => 0,
            'name' => $name,
            'password' => Authentication::getPasswordHash($name, self::PASSWORD),
            'active' => true,
            'admin' => $admin,
            'language' => 'pl',
        ]);
        $user->setPermissions($permissions);
        $user->save();

        return $user;
    }
}
