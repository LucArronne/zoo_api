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
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(string $targetDirectory, UrlGeneratorInterface $urlGenerator)
    {
        $this->targetDirectory = $targetDirectory;
        $this->urlGenerator = $urlGenerator;
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
    public function getFilePublicUrl(string $fileName): string
    {
        return $this->urlGenerator->generate(
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
