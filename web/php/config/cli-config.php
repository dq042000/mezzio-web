<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand as MigrationsDiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand as MigrationsExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as MigrationsMigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand as MigrationsStatusCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand as MigrationsVersionCommand;

require_once __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';
$entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
$config = $container->get('config');

// HelperSet for ORM commands
$helperSet = ConsoleRunner::createHelperSet($entityManager);

// Migrations DependencyFactory using existing EntityManager
$migrationsConfig = new ConfigurationArray($config['doctrine']['migrations'] ?? []);

$dependencyFactory = DependencyFactory::fromEntityManager(
	$migrationsConfig,
	new ExistingEntityManager($entityManager)
);

// Expose migrations commands to Doctrine ORM CLI
$commands = [
	new MigrationsDiffCommand($dependencyFactory),
	new MigrationsExecuteCommand($dependencyFactory),
	new MigrationsMigrateCommand($dependencyFactory),
	new MigrationsStatusCommand($dependencyFactory),
	new MigrationsVersionCommand($dependencyFactory),
];

return $helperSet;