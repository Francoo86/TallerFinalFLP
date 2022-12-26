<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class Uploader
{  
    private $postDirectory;
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, $postDirectory, SluggerInterface $slugger)
    {
        $this->postDirectory = $postDirectory;
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file, $isPost = false)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $finalPath = $isPost ? $this->getPostDirectory() : $this->getTargetDirectory();

        try {
            $file->move($finalPath, $fileName);
        } catch (FileException $e) {
            dd($e);
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function getPostDirectory() {
        return $this->postDirectory;
    }
}