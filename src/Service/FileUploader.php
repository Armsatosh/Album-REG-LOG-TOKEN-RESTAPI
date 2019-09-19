<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

class FileUploader
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function upload($uploadDir, $image,$userName, $filename)
    {
        try {

            $image->move($uploadDir.\DIRECTORY_SEPARATOR.$userName, mt_rand(1,1000).$filename);
        } catch (FileException $e){

            $this->logger->error('failed to upload image: ' . $e->getMessage());
            throw new FileException('Failed to upload file');
        }
    }
}