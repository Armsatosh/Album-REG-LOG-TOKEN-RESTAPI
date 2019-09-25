<?php

namespace App\Security;

use App\Entity\User;
use App\Repository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Firebase\JWT\JWT;

class LoginAuthenticator extends AbstractGuardAuthenticator
{

    private $passwordEncoder;
    private $em;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em              = $em;
    }

    public function supports(Request $request)
    {
        return $request->get("_route") === "api_login"  && $request->isMethod("POST");
    }
    public function getCredentials(Request $request)
    {
        $data                       = json_decode($request->getContent(), true);
        return [
            $email                  =   $data['email'] ,
            $password               =   $data['password']                      /* 'password' => $request->request->get("password")*/
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider -> loadUserByUsername ($credentials[0]) ;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this -> passwordEncoder -> isPasswordValid ( $user , $credentials[1] ) ;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'error' => $exception->getMessageKey()
        ], 400);
    }

    public function onAuthenticationSuccess( Request $request, TokenInterface $token,   $providerKey)
    {
        $data = json_decode($request->getContent(), true);;
        $currentEmail = $data['email'];
        $expireTime = time() + 86400;
        $tokenPayload = [
               'exp'  => $expireTime
        ];
        $jwt = JWT::encode($tokenPayload, getenv("JWT_SECRET"));
        $this->em->getRepository(User::class)->filideUp($jwt, $currentEmail, $expireTime);
        $this->em->flush();
        $name = $this->em->getRepository(User::class)->findNameByEmail($currentEmail)->getName();
        return new JsonResponse([
            'token' => $jwt,
            'name'  => $name,
        ]);

    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse([
            'error' => 'Access Denied'
        ]);
    }

    public function supportsRememberMe()
    {
        return false;
    }

}
