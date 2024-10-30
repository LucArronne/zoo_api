<?php

namespace App\Utils;

use App\Entity\Image;

class ImageMapper
{
    private FileUploader $uploader;

    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    public function convertToUrl(Image $image): Image
    {
        return (new Image())
            ->setId($image->getId())
            ->setPath($this->uploader->getFilePublicUrl($image->getPath()));
    }
    public function convertToUrlArray(array $images): array
    {
        return array_map(function (Image $image) {
            return $this->convertToUrl($image);
        }, $images);
    }
}
