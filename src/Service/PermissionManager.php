<?php
/**
 * Created by PhpStorm.
 * User: akazakbaev@srs.lan
 * Date: 11/20/18
 * Time: 3:37 PM
 */
namespace Zf\Infocom\Auth\Service;

use Mezzio\Authentication\UserInterface;
use Laminas\Permissions\Rbac\Rbac;

class PermissionManager
{
    /**
     * @var Rbac
     */
    protected $rbac;

    /**
     * @var UserInterface
     */
    protected $authManager;

    public function __construct(Rbac $rbac, UserInterface $authManager)
    {
        $this->rbac = $rbac;

        $this->authManager = $authManager;
    }

    public function isGranted($name)
    {
        if($this->authManager)
            $viewer = $this->authManager->getViewer();
        else
            $viewer = null;

        if(!$viewer)
            $type = 'public';
        else
            $type = $viewer->getLevel()->getLevelName();

        return $this->rbac->isGranted($type, $name);
    }
}