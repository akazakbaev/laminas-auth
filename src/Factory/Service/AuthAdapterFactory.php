<?php

declare(strict_types=1);

namespace Zf\Infocom\Auth\Factory\Service;

use Psr\Container\ContainerInterface;
use Zf\Infocom\Auth\Service\AuthAdapter;
use Mezzio\Authentication\UserInterface;

class AuthAdapterFactory
{
    public function __invoke(ContainerInterface $container) : AuthAdapter
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $userInterface = $container->get(UserInterface::class);

        return new AuthAdapter($entityManager, $userInterface);
    }
}
