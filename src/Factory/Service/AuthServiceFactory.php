<?php

declare(strict_types=1);

namespace Zf\Infocom\Auth\Factory\Service;

use Psr\Container\ContainerInterface;
use Zf\Infocom\Auth\Service\AuthManager;
use Zf\Infocom\Auth\Service\AuthService;

class AuthServiceFactory
{
    public function __invoke(ContainerInterface $container) : AuthService
    {
        $authManager = $container->get(AuthManager::class);

        return new AuthService($authManager);
    }
}
