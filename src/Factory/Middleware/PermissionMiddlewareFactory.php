<?php

declare(strict_types=1);

namespace Akazakbaev\LaminasAuth\Factory\Middleware;

use Akazakbaev\LaminasAuth\Entity\AuthLevels;
use Akazakbaev\LaminasAuth\Entity\AuthPermissions;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Akazakbaev\LaminasAuth\Middleware\PermissionMiddleware;
use Laminas\Permissions\Rbac\Rbac;
use Zf\Infocom\Core\Service\CacheManager;

class PermissionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : PermissionMiddleware
    {
        return new PermissionMiddleware(
            $container->get('permissionManager')
        );
    }
}
