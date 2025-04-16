<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileService
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public function deleteFile(string $filePath): bool
    {
        // Eliminar el prefijo /uploads/cursos/ si existe
        $filePath = str_replace('/uploads/cursos/', '', $filePath);
        $fullPath = $this->projectDir . '/public/uploads/cursos/' . $filePath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public function fileExists(string $filePath): bool
    {
        // Eliminar el prefijo /uploads/cursos/ si existe
        $filePath = str_replace('/uploads/cursos/', '', $filePath);
        $fullPath = $this->projectDir . '/public/uploads/cursos/' . $filePath;

        return file_exists($fullPath);
    }
}