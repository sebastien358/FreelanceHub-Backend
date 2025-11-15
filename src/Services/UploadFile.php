<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFile
{
    private $image_directory;

    public function __construct(string $image_directory)
    {
        $this->image_directory = $image_directory;
    }

    public function upload(UploadedFile $file)
    {
        $newFileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($this->image_directory, $newFileName);
        return $newFileName;
    }

    public function deleteImageFile($image): void
    {
        $filePath = $this->getParameter('image_directory') . '/' . $image->getFilename();
        if ($image->getFilename() && file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
