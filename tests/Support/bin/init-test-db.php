<?php

/**
 * Drops and re-creates the test database with an empty Pimcore installation.
 * Classes, bundles and migrations are handled by bin/run-tests afterwards.
 *
 * Refuses to touch anything that is not the dedicated test database.
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Pimcore\Bundle\InstallBundle\Installer;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

require dirname(__DIR__, 2) . '/bootstrap.php';

$kernel = new App\Kernel('test', true);
$kernel->boot();
\Pimcore::setKernel($kernel);

/** @var Connection $db */
$db = $kernel->getContainer()->get('doctrine.dbal.default_connection');
$params = $db->getParams();
$dbName = $params['dbname'] ?? '';

if ($kernel->getEnvironment() !== 'test' || ($params['host'] ?? '') !== 'test_db' || !str_ends_with($dbName, '_test')) {
    fwrite(STDERR, sprintf("Refusing to initialize [%s@%s] - only the test_db/*_test database may be reset.\n", $dbName, $params['host'] ?? '?'));
    exit(1);
}

unset($params['url'], $params['dbname']);
$server = DriverManager::getConnection($params);
$server->executeStatement(sprintf('DROP DATABASE IF EXISTS %s', $db->quoteIdentifier($dbName)));
$server->executeStatement(sprintf('CREATE DATABASE %s CHARSET=utf8mb4', $db->quoteIdentifier($dbName)));
$server->close();
$db->close();

$installer = new Installer(new NullLogger(), new EventDispatcher());
$installer->setImportDatabaseDataDump(false);
$errors = $installer->setupDatabase($db, ['username' => 'admin', 'password' => bin2hex(random_bytes(16))]);

if ($errors) {
    fwrite(STDERR, "Test database setup failed:\n" . implode("\n", $errors) . "\n");
    exit(1);
}

echo "Initialized test database $dbName\n";
