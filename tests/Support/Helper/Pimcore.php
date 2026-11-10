<?php

namespace App\Tests\Support\Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Module\Symfony;
use Codeception\TestInterface;
use Pimcore\Helper\LongRunningHelper;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\User;
use Pimcore\Model\Version;
use Pimcore\Security\User\User as SecurityUser;

/**
 * Pimcore fixtures for functional tests.
 *
 * Everything created through this helper lives under /_tests and is deleted after each test.
 * The suite refuses to run against any database other than test_db/*_test.
 */
class Pimcore extends Module
{
    public const FIXTURE_ROOT = '/_tests';
    public const ADMIN_USERNAME = 'test-admin';
    public const API_TOKEN = 'test-token';

    /** @var ElementInterface[] */
    private array $created = [];

    public function _beforeSuite(array $settings = []): void
    {
        $params = $this->symfony()->_getContainer()->get('doctrine.dbal.default_connection')->getParams();

        if (($params['host'] ?? '') !== 'test_db' || !str_ends_with($params['dbname'] ?? '', '_test'))
        {
            throw new ModuleException($this, sprintf(
                'Functional tests must run against test_db/*_test, got [%s@%s]. Use bin/run-tests.',
                $params['dbname'] ?? '?',
                $params['host'] ?? '?'
            ));
        }

        // Pimcore's kernel shutdown needs the static kernel - the Symfony module shuts it down before each test
        $this->attachKernel();
        Version::disable();
    }

    public function _before(TestInterface $test): void
    {
        $this->attachKernel();
    }

    public function _after(TestInterface $test): void
    {
        if (!$this->created)
        {
            return;
        }

        $this->attachKernel();

        foreach (array_reverse($this->created) as $element)
        {
            $fresh = $element::getById($element->getId(), ['force' => true]);
            $fresh?->delete();
        }

        $this->created = [];
        DataObject\Folder::getByPath(self::FIXTURE_ROOT)?->delete();
    }

    /**
     * Creates and saves a DataObject of the given class (short name, e.g. "Product").
     *
     * @param array<string, mixed> $values setter name without "set" => value
     */
    public function haveDataObject(string $class, array $values = [], ?string $key = null, bool $published = true): Concrete
    {
        $this->attachKernel();
        $fqcn = '\\Pimcore\\Model\\DataObject\\' . $class;

        /** @var Concrete $object */
        $object = new $fqcn();
        $object->setParent($this->fixtureFolder());
        $object->setKey($key ?? strtolower($class) . '-' . uniqid());
        $object->setPublished($published);

        foreach ($values as $field => $value)
        {
            $object->{'set' . ucfirst($field)}($value);
        }

        $object->save();
        $this->created[] = $object;

        return $object;
    }

    /**
     * Logs the browser in as a Pimcore admin, exactly like the admin UI session does.
     */
    public function amLoggedInAsPimcoreAdmin(): User
    {
        $this->attachKernel();
        $user = User::getByName(self::ADMIN_USERNAME);

        if (!$user)
        {
            $user = User::create([
                'parentId' => 0,
                'name' => self::ADMIN_USERNAME,
                'password' => \Pimcore\Tool\Authentication::getPasswordHash(self::ADMIN_USERNAME, bin2hex(random_bytes(8))),
                'active' => true,
                'admin' => true,
            ]);
        }

        $this->symfony()->amLoggedInAs(new SecurityUser($user), 'pimcore_admin');

        return $user;
    }

    /**
     * Sends the API token of the test client (API_TOKENS in .env.test) with following requests.
     */
    public function haveValidApiToken(): void
    {
        $this->symfony()->client->setServerParameter('HTTP_X_API_TOKEN', self::API_TOKEN);
    }

    public function haveApiToken(string $token): void
    {
        $this->symfony()->client->setServerParameter('HTTP_X_API_TOKEN', $token);
    }

    /**
     * Sends a request with a raw JSON body (the Symfony module's ajax helpers only send form fields).
     */
    public function sendJson(string $method, string $uri, mixed $data): void
    {
        $this->symfony()->client->request($method, $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
    }

    public function grabResponseContent(): string
    {
        return $this->symfony()->client->getInternalResponse()->getContent();
    }

    public function grabResponseHeader(string $name): ?string
    {
        return $this->symfony()->client->getInternalResponse()->getHeader($name);
    }

    private function fixtureFolder(): DataObject\Folder
    {
        return DataObject\Folder::getByPath(self::FIXTURE_ROOT)
            ?? DataObject\Service::createFolderByPath(self::FIXTURE_ROOT);
    }

    /**
     * Pimcore models use the static \Pimcore kernel, which the Symfony module may have shut down between requests.
     */
    private function attachKernel(): void
    {
        $kernel = $this->symfony()->kernel;
        $kernel->boot();
        \Pimcore::setKernel($kernel);

        // Pimcore's Kernel::shutdown() lazily creates this service, which needs the "doctrine.connections"
        // parameter that Codeception hides during shutdown - create it while the parameter is still there
        $kernel->getContainer()->get(LongRunningHelper::class);
    }

    private function symfony(): Symfony
    {
        /** @var Symfony $module */
        $module = $this->getModule('Symfony');

        return $module;
    }
}
