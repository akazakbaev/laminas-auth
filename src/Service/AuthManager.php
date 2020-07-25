<?php
/**
 * Created by PhpStorm.
 * User: akazakbaev@srs.lan
 * Date: 11/20/18
 * Time: 10:10 AM
 */
namespace Zf\Infocom\Auth\Service;

use App\Classes\ItemRepository;
use App\Entity\ApplicationConsumers;
use Zf\Infocom\Auth\Entity\AuthConsumers;
use Zf\Infocom\Auth\Entity\AuthLevels;
use Zf\Infocom\Auth\Provider\ViewerInterface;
use Department\Entity\DepartmentDepartments;
use Doctrine\ORM\EntityManager;
use Employee\Classes\AbstractEmployeeFields;
use Employee\Entity\EmployeeEmployees;
use Firebase\JWT\JWT;
use Position\Entity\PositionPositions;
use Register\Entity\RegisterPostsGroups;
use State\Entity\StateStates;
use State\Service\StateManager;
use User\Entity\UserUsers;
use Mezzio\Authentication\UserInterface;
use Zf\Infocom\Core\Service\CacheManager;

class AuthManager implements UserInterface
{
    /**
     * @var UserUsers
     */
    protected  $viewer;

    protected $token;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected $cypherKey;

    protected $tokenAlgorithm;

    protected $cacheManager;

    public function __construct(EntityManager $entityManager, CacheManager $cacheManager, array $options)
    {
        $this->entityManager = $entityManager;


        $this->cacheManager = $cacheManager;

        foreach( $options as $key => $value )
        {
            if( property_exists($this, $key) )
                $this->$key = $value;
        }
    }

    public function getIdentity() : string
    {
        if($this->getViewer())
            return $this->getViewer()->getIdentity();

        return '';
    }

    public function getDetail(string $name, $default = null)
    {
        // TODO: Implement getDetail() method.
    }

    public function getDetails(): array
    {
        // TODO: Implement getDetails() method.
    }

    public function getRoles(): iterable
    {
        return [
            'test'
        ];
    }

    public function setViewer(ViewerInterface $viewer)
    {
        $this->viewer = $viewer;
    }

    public function getViewer()
    {
        return $this->viewer;
    }

    public function getTokenData()
    {
        //0 years, 0 months, 0 days, 0 hours, 0 minutes, 0 seconds ago

        //iat - issuedAt(время, когда был создан токен)
        //nbf - notBefore(не раньше)
        //exp - expire(истекает)
        $result = [
            'userId' => $this->viewer->getId(),
            'nbf'    => time(),
            'iat'    => time(),
            'exp'    => strtotime('+1 days')
        ];

        return $result;
    }

    public function generateJwtToken()
    {
        $this->token = JWT::encode($this->getTokenData(), $this->cypherKey, $this->tokenAlgorithm);

        return $this->token;
    }

    public function getUserById($userId)
    {
        return $this->entityManager->getRepository(UserUsers::class)->find($userId);
    }

    public function getConsumerById($cliendId)
    {
        return $this->entityManager->getRepository(AuthConsumers::class)->findOneBy(['title' => $cliendId]);
    }

    /**
     * @return mixed
     */
    public function getCypherKey()
    {
        return $this->cypherKey;
    }

    /**
     * @return mixed
     */
    public function getTokenAlgorithm()
    {
        return $this->tokenAlgorithm;
    }
}