<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\RedirectResponse;


class FileController extends AbstractController
{
    private $em;
    /**
     * @var $user User
     */
    private $user;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em  = $em;
        $this->verify($_REQUEST['token']);
    }

    public function verify($token) {
        $user = $this->em->getRepository(User::class)->findUserByToken($token);
        $expireTime = $user->getExpire();
        if ($expireTime <time()){
            $this->em->getRepository(User::class)->removeToken($token);
            return new Response("Do logout",
                Response::HTTP_BAD_REQUEST, ['content-type' => 'text/plain']);

        }
        $newExpireTime = time() + 86400;
        $this->user = $this->em->getRepository(User::class)->findUserByToken($token);
        $this->user->setExpire($newExpireTime);
        if (!$this->user) {
            echo new Response("Operation not allowed (User)".time(),  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
            die;
        }

        $DbToken = $this->user->getToken();
        if (!$token || $token === "null" || $token !== $DbToken )
        {
            new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
            die;
        }
    }

    /**
     * @Route("/upload", name="upload")
     */

    public function index(Request $request, string $uploadDir, FileUploader $uploader,ObjectManager $om)
    {
        $token = $request->get("token");
        $user = $this->em->getRepository(User::class)->findUserByToken($token);
        $images = $request->files->get('files');
               if (empty($images))
        {
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }
        foreach ($images as $image) {
            /**
             * @var $image UploadedFile
             * */
            $type = $image->getClientOriginalExtension();
            if ($type !== 'jpg' && $type !== 'jpeg' && $type !== 'jpe' && $type !== 'png'){
                return new Response($image->getErrorMessage());
            }
            $files = new File();
            //$currentHost = $currentUrl = $this->container->get('router')->getContext()->getHost();
            //$currentScheme = $currentUrl = $this->container->get('router')->getContext()->getScheme();
            $userName = $user->getId();
            $imagename = mt_rand(1,1000).$image->getClientOriginalName();
            $description = $request->get("description");
            $files->setImage(/*$currentScheme . '//' . $currentHost .*/ $uploadDir . DIRECTORY_SEPARATOR . $userName . DIRECTORY_SEPARATOR . $imagename);
            $files->setUser($user);
            $files->setDescription($description);
            $om->persist($files);
            $om->flush();
            $uploader->upload($uploadDir, $image, $userName, $imagename);
            
        }

        return  new Response("File uploaded",  Response::HTTP_OK,
            ['content-type' => 'text/plain']);
    }
    /**
     * @Route("/upload/description", name="description" )
     */
    public function  Description(Request $request)
    {
        $currentImage = $request->get("id");
        $imageDescription = $request->get("description");
        $file = $this->em->getRepository(File::class)->findFileByImageId($currentImage);
        $file->setDescription($imageDescription);
    }

    /**
     * @Route("/getImgId", name="getImgId", methods={"GET"})
     */
    public function  getImgId()
    {
        //$id = $this->user->getId();
        $imgId =$this->user->getFiles();
        $images = [];
       foreach($imgId as  $value) {
            $images[] = $value->getId();
        }
        return new JsonResponse([
            'imgId'=> $images,
        ]);
    }

    /**
     * @Route("/getImages", name="getImages", methods={"GET"})
     */

   public function imagesAction(Request $request){
        $id = $request->get('imageId');
        $filepath = $this->em->getRepository(File::class)->findFileByImageId($id)->getImage();
        $uploadedFile = new UploadedFile($filepath, $filepath);
        $type = $uploadedFile->getClientOriginalExtension();
        $uploadedFileName = $uploadedFile->getClientOriginalName();
        $response = new Response();
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $uploadedFileName);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'image/'.$type);
        $response = $response->setContent(file_get_contents($filepath));

        return $response;
    }

}
