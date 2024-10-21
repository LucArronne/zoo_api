<?php

namespace App\Utils;

use App\Entity\Image;

class ImageToUrlSerializer
{
    private FileUploader $uploader;

    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    public function serialize(Image $image): array
    {
        return [
            "id" => $image->getId(),
            "path" => $this->uploader->getFilePublicUrl($image->getPath()),
        ];
    }
    public function serializeArray(array $images): array
    {
        return array_map(function (Image $image) {
            return $this->serialize($image);
        }, $images);
    }
}
