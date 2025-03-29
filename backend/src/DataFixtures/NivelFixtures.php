<?php

// src/DataFixtures/NivelFixtures.php
namespace App\DataFixtures;

use App\Entity\Nivel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NivelFixtures extends Fixture
{
    public const NIVEL_PRINCIPIANTE = "nivel-inicial";
    public function load(ObjectManager $manager): void
    {
        $niveles = [
            ['nombre' => 'Principiante', 'puntos' => 0, 'desc' => 'Nivel inicial'],
            ['nombre' => 'Intermedio', 'puntos' => 1000, 'desc' => 'Nivel medio'],
            ['nombre' => 'Avanzado', 'puntos' => 5000, 'desc' => 'Nivel experto']
        ];
        
        foreach ($niveles as $nivelData) {
            $nivel = new Nivel();
            $nivel->setNombre($nivelData['nombre'])
                ->setPuntosRequeridos($nivelData['puntos'])
                ->setDescripcion($nivelData['desc']);
            
            $manager->persist($nivel);
            
            // Assuming this is the first iteration
            if ($nivelData['nombre'] === 'Principiante') {
                $this->addReference(self::NIVEL_PRINCIPIANTE, $nivel);
            }
        }

        $manager->flush();
    }
}
