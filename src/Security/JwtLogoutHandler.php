<?php
namespace App\Security;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
class JwtLogoutHandler implements LogoutSuccessHandlerInterface
{
    private $em;
    public function __construct( EntityManagerInterface $em)
    {
        $this->em  = $em;
    }

    public function onLogoutSuccess(Request $request)
    {
        $postData = $request->getContent();
        $token = json_decode($postData,true);
        $jwt = $token['token'];
        $affectRow = $this->em->getRepository(User::class)->removeToken($jwt);
        if ($affectRow === 1){
            $message = "Token removed";
        }else {
            $message = "Token cant be removed";
        }
        return new Response($message);
        //return new Response("Token removed");
       //$response->headers->clearCookie("jwt");
        //return $response;
    }
}