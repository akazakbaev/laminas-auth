<?php
/**
 * Created by PhpStorm.
 * User: akazakbaev@srs.lan
 * Date: 11/20/18
 * Time: 3:37 PM
 */
namespace Zf\Infocom\Auth\Service;

use App\Entity\ApplicationConsumers;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Firebase\JWT\JWT;
use Zf\Infocom\Auth\Entity\AuthConsumers;
use Zf\Infocom\Core\RestDispatchTrait;

class AuthService implements AuthenticationInterface
{
    use RestDispatchTrait;
    /**
     * @var AuthManager
     */
    protected $authManager;

    /**
     *
     * @var type string
     */
    public $token;

    /**
     *
     * @var type Object or Array
     */
    public $tokenPayload;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        $jwtToken = $this->findJwtToken($request);

        if ($jwtToken)
        {
            $this->token = $jwtToken;

            $this->decodeJwtToken();

            if (is_object($this->tokenPayload) && property_exists($this->tokenPayload, 'userId'))
            {
                $user = $this->authManager->getUserById($this->tokenPayload->userId);

                if($user)
                {
                    $this->authManager->setViewer($user);

                    if(method_exists($user, 'getLocale'))
                    {
                        $locale = $user->getLocale();

                        \Locale::setDefault($locale);
                    }

                    return $this->authManager;
                }
            }

            return null;
        }
        elseif ($consumer = $this->authByConsumer($request))
        {

            $this->authManager->setViewer($consumer);

            return $this->authManager;
        }

        return null;
    }

    public function isGranted(string $role, ServerRequestInterface $request)
    {
        return false;
    }

    protected function decodeJwtToken()
    {
        if (!$this->token) {
            $this->tokenPayload = false;
        }

        try {
            $decodeToken = JWT::decode($this->token, $this->authManager->getCypherKey(), [$this->authManager->getTokenAlgorithm()]);
            $this->tokenPayload = $decodeToken;
        } catch (\Exception $e) {
            $this->tokenPayload = $e->getMessage();
        }
    }

    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->createErrorResponse(false, 'Не авторизованы', [], 401);
    }

    private function findJwtToken(ServerRequestInterface $request)
    {
        $authHeaders = $request->getHeader('Authorization');

        $authHeader = array_shift($authHeaders);

        $jwtToken = $authHeader;

        if ($jwtToken) {
            $jwtToken = trim(trim($jwtToken, "Bearer"), " ");

            return $jwtToken;
        }

        if ($request->getMethod() == RequestMethodInterface::METHOD_GET)
        {
            $requestParams = $request->getQueryParams();
            $jwtToken = $requestParams['token'] ?? null;
        }

        if ($request->getMethod() == RequestMethodInterface::METHOD_POST)
        {
            $body = $request->getParsedBody();

            $jwtToken = $body['token'] ?? null;
        }

        return $jwtToken;
    }

    private function authByConsumer(ServerRequestInterface $request)
    {
        $data = [];

        if ($request->getMethod() == RequestMethodInterface::METHOD_GET)
            $data = $request->getQueryParams();

        if ($request->getMethod() == RequestMethodInterface::METHOD_POST)
            $data = $request->getParsedBody();

        $client_id = $hash = $timestamp = $random = '';

        if(isset($data['clientId']))
            $client_id = $data['clientId'];

        if(isset($data['hash']))
            $hash = $data['hash'];

        if(isset($data['timestamp']))
            $timestamp = $data['timestamp'];

        if(isset($data['random']))
            $random = $data['random'];

        $parameters = [
            'clientId' => $client_id,
            'timestamp' => $timestamp,
            'random' => $random
        ];

        ksort($parameters);

        $sig = '';

        foreach ($parameters as $key => $value)
            $sig .= $key . '=' . $value;

        /** @var AuthConsumers $consumer */
        $consumer = $this->authManager->getConsumerById($client_id);

        if($consumer)
            $sig .= $consumer->getAccessToken();
        else
            return null;

        if( md5($sig) == $hash)
            return $consumer;
        else
            return null;

    }
}