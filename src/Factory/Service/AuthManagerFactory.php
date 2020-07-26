<?php

declare(strict_types=1);

namespace Akazakbaev\LaminasAuth\Factory\Service;

use Psr\Container\ContainerInterface;
use Akazakbaev\LaminasAuth\Service\AuthManager;
use State\Service\StateManager;
use Zf\Infocom\Core\Service\CacheManager;


class AuthManagerFactory
{
    public function __invoke(ContainerInterface $container) : AuthManager
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('config');
        $cacheManager = $container->get(CacheManager::class);

        $options = [];

        if(isset($config['jwtAuth']))
            $options = $config['jwtAuth'];

        return new AuthManager($entityManager, $cacheManager, $options);
    }
}
