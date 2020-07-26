<?php

declare(strict_types=1);

namespace Akazakbaev\LaminasAuth\Factory\Service;

use Akazakbaev\LaminasAuth\Entity\AuthLevels;
use Akazakbaev\LaminasAuth\Entity\AuthPermissions;
use Akazakbaev\LaminasAuth\Service\PermissionManager;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Laminas\Permissions\Rbac\Rbac;
use Zf\Infocom\Core\Service\CacheManager;

class PermissionManagerFactory
{
    public function __invoke(ContainerInterface $container) : PermissionManager
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var CacheManager $cacheManager */
        $cacheManager = $container->get(CacheManager::class);

        $this->cache = $cacheManager->getCache();

        $result = false;
        $rbac = $this->cache->getItem('rbac_container', $result);

        if(!$rbac)
        {
            $rbac = new Rbac();

            $rbac->setCreateMissingRoles(true);

            $roles = $entityManager->getRepository(AuthLevels::class)
                ->findBy([], ['id' => 'ASC']);

            /** @var AuthLevels $role */
            foreach ($roles as $role) {
                $roleName = $role->getLevelName();

                $rbac->addRole($roleName, []);

                /** @var AuthPermissions $permission */
                foreach ($role->getPermission() as $permission)
                    $rbac->getRole($roleName)->addPermission($permission->getName());
            }

            $this->cache->setItem('rbac_container', $rbac);
        }

        $authManager = $container->get('authManager');

        return new PermissionManager($rbac, $authManager);
    }
}
