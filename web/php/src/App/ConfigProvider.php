<?php

declare(strict_types=1);

namespace App;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'laminas-cli'  => [
                'commands' => [
                    'db:init' => \App\Command\InitDbCommand::class,
                    'user:reset-password' => \App\Command\ResetPasswordCommand::class,
                ],
            ],
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories'  => [
                Handler\HomePageHandler::class => Factory\HomePageHandlerFactory::class,
                Handler\LoginHandler::class => Factory\LoginHandlerFactory::class,
                \Mezzio\Session\SessionPersistenceInterface::class => \Mezzio\Session\Ext\PhpSessionPersistenceFactory::class,
                \App\Command\InitDbCommand::class => function($container) {
                    return new \App\Command\InitDbCommand($container->get(\Doctrine\ORM\EntityManager::class));
                },
                \App\Command\ResetPasswordCommand::class => function($container) {
                    return new \App\Command\ResetPasswordCommand($container->get(\Doctrine\ORM\EntityManager::class));
                },
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => ['templates/app'],
                'error'  => ['templates/error'],
                'layout' => ['templates/layout'],
                'auth'   => ['templates/auth'],
            ],
        ];
    }
}
