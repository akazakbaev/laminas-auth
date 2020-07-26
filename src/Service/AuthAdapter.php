<?php
/**
 * Created by PhpStorm.
 * User: akazakbaev@srs.lan
 * Date: 11/20/18
 * Time: 11:24 AM
 */
namespace Akazakbaev\LaminasAuth\Service;

use Doctrine\ORM\EntityManager;
use User\Entity\UserLogins;
use User\Entity\UserUsers;
use Laminas\Crypt\Password\Bcrypt;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;

class AuthAdapter implements UserRepositoryInterface
{
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var AuthManager
     */
    protected $authManager;

    protected $message = 'Error';

    /**
     * @param EntityManager $entityManager
     * @param UserInterface $userInterface
     */
    public function __construct(EntityManager $entityManager, UserInterface $userInterface)
    {
        $this->entityManager = $entityManager;

        $this->authManager = $userInterface;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    public function authenticate(string $credential, string $password = null) : ?UserInterface
    {
        /** @var UserUsers $user */
        $user = $this->entityManager->getRepository(UserUsers::class)
            ->findUser($credential);

        if($user == null)
        {
            $this->loginLog(null, $credential, UserLogins::STATUS_NOT_FOUND);

            $this->setMessage('Пользователь не найден');

            return null;
        }

        if(!$user->isStatus())
        {
            $this->loginLog($user, $credential, UserLogins::STATUS_DISABLED);

            $this->setMessage('Ваш аккаунт заблокирован');

            return null;
        }

        if(!$user->getLevel() || !$user->getState())
        {
            $this->loginLog($user, $credential, UserLogins::STATUS_USER_NOT_CONFIGURED);

            $this->setMessage('Ваш аккаунт не настроен, обратитесь к администратору');

            return null;
        }


        $bcrypt = new Bcrypt();
        $passwordHash = $user->getPassword();

        if (!$bcrypt->verify($password, $passwordHash))
        {
            $this->loginLog($user, $credential, UserLogins::STATUS_PASSWORD_BAD);

            $this->setMessage('Не правильный пароль');

            return null;
        }

        $this->authManager->setViewer($user);

        $user->setLastlogin(new \DateTime('now'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->loginLog($user, $credential, UserLogins::STATUS_SUCCESS);

        return $this->authManager;
    }

    public function loginLog($user = null, $username = '', $status = -1)
    {

        $remoteAddress = new \Laminas\Http\PhpEnvironment\RemoteAddress();

        $ip = '127.0.0.0';

        if ('cli' !== PHP_SAPI)
            $ip = $remoteAddress->getIpAddress();
        
        $conn = $this->entityManager->getConnection();
        $conn->beginTransaction();
        try{

            $login = new UserLogins();

            $login->setUser($user);
            $login->setUsername($username);
            $login->setCreationDate( new \DateTime());
            $login->setIp($ip);
            $login->setStatus($status);

            $this->entityManager->persist($login);

            // Apply changes to database.
            $this->entityManager->flush();


            $this->entityManager->getConnection()->commit();
        }catch(\Exception $e)
        {
            $this->entityManager->getConnection()->rollBack();
        }
    }

    public function getAuthManager()
    {
        return $this->authManager;
    }
}