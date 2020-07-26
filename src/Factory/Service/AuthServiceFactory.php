<?php

declare(strict_types=1);

namespace Akazakbaev\LaminasAuth\Factory\Service;

use Psr\Container\ContainerInterface;
use Akazakbaev\LaminasAuth\Service\AuthManager;
use Akazakbaev\LaminasAuth\Service\AuthService;

class AuthServiceFactory
{
    public function __invoke(ContainerInterface $container) : AuthService
    {
        $authManager = $container->get(AuthManager::class);

        return new AuthService($authManager);
    }
}
