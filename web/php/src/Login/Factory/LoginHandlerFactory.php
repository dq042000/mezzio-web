<?php

declare(strict_types=1);

namespace Login\Factory;

use Login\Handler\LoginHandler;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Csrf\CsrfGuardFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

class LoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        return new LoginHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(CsrfGuardFactoryInterface::class),
            $container->get(EntityManagerInterface::class)
        );
    }
}
