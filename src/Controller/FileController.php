<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\RedirectResponse;


class FileController extends AbstractController
{
    private $em;
    public function __construct( EntityManagerInterface $em)
    {
        $this->em  = $em;
    }

    /**
     * @Route("/upload", name="upload")
     */


    public function index(Request $request, string $uploadDir, FileUploader $uploader,ObjectManager $om)
    {

        $token = $request->get("token");
        $user = $this->em->getRepository(User::class)->findUserByToken($token);
        $DbToken = $user->getToken();

        if (!$token || $token === "null" || $token !== $DbToken )
        {
            return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }

        $images = $request->files->get('files');

        if (empty($images))
        {
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }
        $files = new File();
        $currentHost = $currentUrl = $this->container->get('router')->getContext()->getHost();
        $currentScheme = $currentUrl = $this->container->get('router')->getContext()->getScheme();
        $userName = $user->getName();
        foreach ($images as $image) {
            $imagename = $image->getClientOriginalName();
            $description = $request->get("description");
            $files->setImage($currentScheme . '//' . $currentHost . $uploadDir . DIRECTORY_SEPARATOR . $userName . DIRECTORY_SEPARATOR . $imagename);
            $files->setUser($user);
            $files->setDescription($description);
            $om->persist($files);
            $om->flush();

            /* $expireTime =$user->getExpire();
                 if ($expireTime > time()){
                 return $this->redirect('/logout');
                 print_r('OK');
             }*/
            $uploader->upload($uploadDir, $image, $userName, $imagename);
    print_r("ok");
        }
        return new Response("File uploaded",  Response::HTTP_OK,
            ['content-type' => 'text/plain']);

    }

}