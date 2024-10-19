<?php

namespace App\Utils;

use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FileUploader
{
    private string $targetDirectory;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * Upload file in the server and return the filename
     * 
     * @return string
     */

    public function upload(UploadedFile $file, array $allowedExtensions): string
    {

        if (!in_array($file->guessExtension(), $allowedExtensions)) {
            throw new InvalidArgumentException();
        }

        $filename = uniqid() . "." . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $filename);
        } catch (Exception $e) {
            throw new FileException("Could not upload file: " . $e->getMessage());
        }

        return $filename;

    }

    /**
     * Get the public url of a file
     * 
     */
    public function getFilePublicUrl(UrlGeneratorInterface $urlGenerator, string $fileName): string
    {
        return $urlGenerator->generate(
            'uploads_path',
            ['fileName' => $fileName],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * Get the directory where files are stored.
     *
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}