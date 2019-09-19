<?php
namespace App\Security;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
class JwtLogoutHandler implements LogoutSuccessHandlerInterface
{
    public function onLogoutSuccess(Request $request)
    {
        $token = $request->request->get("token");
        $response = new JsonResponse(['result777' => $token]);
       // $response->headers->clearCookie("jwt");
        return $response;
    }
}