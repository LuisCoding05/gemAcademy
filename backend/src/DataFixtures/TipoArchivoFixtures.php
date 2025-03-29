<?php

// src/DataFixtures/TipoArchivoFixtures.php
namespace App\DataFixtures;

use App\Entity\TipoArchivo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TipoArchivoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tipos = [
            [
                'extension' => 'pdf',
                'descripcion' => 'Documento PDF',
                'material' => true,
                'tarea' => true,
                'maxTamano' => 10
            ],
            [
                'extension' => 'docx',
                'descripcion' => 'Documento Word',
                'material' => true,
                'tarea' => true,
                'maxTamano' => 5
            ],
            [
                'extension' => 'pptx',
                'descripcion' => 'Presentación PowerPoint',
                'material' => true,
                'tarea' => false,
                'maxTamano' => 20
            ],
            [
                'extension' => 'zip',
                'descripcion' => 'Archivo comprimido',
                'material' => false,
                'tarea' => true,
                'maxTamano' => 50
            ],
            [
                'extension' => 'jpg',
                'descripcion' => 'Imagen JPEG',
                'material' => true,
                'tarea' => false,
                'maxTamano' => 2
            ]
        ];

        foreach ($tipos as $tipoData) {
            $tipo = new TipoArchivo();
            $tipo->setExtension($tipoData['extension'])
                ->setDescripcion($tipoData['descripcion'])
                ->setPermitidoMaterial($tipoData['material'])
                ->setPermitidoTarea($tipoData['tarea'])
                ->setMaxTamanoMb($tipoData['maxTamano']);
            
            $manager->persist($tipo);
        }

        $manager->flush();
    }
}
