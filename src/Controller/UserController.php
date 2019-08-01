<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Twig\Token;

class UserController extends AbstractController
{
    /**
     * @Route("/register", name="api_register", methods={"POST"})
     */
    public function register(ObjectManager $om, UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {
        $user = new User();
        $data                   = json_decode($request->getContent(), true);
        $name                   =   $data['name'] ;
        $email                  =   $data['email'] ;                                        /*$request->request->get("email")*/
        $password               =   $data['password'] ;                                     /*$request->request->get("password");*/
        $passwordConfirmation   =   $data['password_confirmation'] ;                        /*$request->request->get("password_confirmation");*/
        $errors = [];
        if($password != $passwordConfirmation)
        {
            $errors[] = "Password does not match the password confirmation.";
        }
        if(strlen($password) < 6)
        {
            $errors[] = "Password should be at least 6 characters.";
        }
        if(!$errors)
        {
            $expireTime = time() + 86400;
            $tokenPayload = ['exp' => $expireTime];
            $jwt = JWT::encode($tokenPayload, getenv("JWT_SECRET"));
            $encodedPassword = $passwordEncoder->encodePassword($user, $password);
            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword($encodedPassword);
            $user->setToken($jwt);
            $user->setStatus("1");

            try
            {
                $om->persist($user);
                $om->flush();

                return $this->json([
                    'Token' => $jwt
                ]);
            }

            catch( UniqueConstraintViolationException $e)
            {
                $errors[] = "The email provided already has an account!";
            }
            catch(\Exception $e)
            {
                $errors[] = "Unable to save new user at this time.";
            }
        }
        return $this->json([
            'errors' => $errors
        ], 400);
    }


    /**
     * @Route("/login", name="api_login", methods={"POST"})
     */
    public function login(){
        return $this -> json( [

        ] ) ;
    }


    /**
     * @Route("/profile", name="api_profile")
     * @IsGranted("ROLE_USER")
     */
    public function profile()
    {
        return $this->json([
            'user' => $this->getUser()
        ],
            200,
            [],
            [
                'groups' => ['api']
            ]
        );
    }
    /**
     * @Route("/", name="api_home")
     */
    public function home()
    {
        return $this->json(['result' => "Home"]);
    }


}
