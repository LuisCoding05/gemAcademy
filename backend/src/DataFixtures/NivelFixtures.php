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
            ['numero_nivel' => 1, 'nombre' => 'Novato Aprendiz', 'puntos' => 0, 'desc' => 'Acabas de llegar. ¡Empieza tu aventura!'],
            ['numero_nivel' => 2, 'nombre' => 'Explorador', 'puntos' => 100, 'desc' => 'Sigue descubriendo conocimientos.'],
            ['numero_nivel' => 3, 'nombre' => 'Guerrero', 'puntos' => 300, 'desc' => 'Tus habilidades están mejorando.'],
            ['numero_nivel' => 4, 'nombre' => 'Maestro Junior', 'puntos' => 600, 'desc' => 'Ya controlas lo básico. ¡A por más!'],
            ['numero_nivel' => 5, 'nombre' => 'Maestro', 'puntos' => 1000, 'desc' => 'Tus conocimientos brillan con fuerza.'],
            ['numero_nivel' => 6, 'nombre' => 'Gran Sabio', 'puntos' => 1600, 'desc' => 'La sabiduría te impulsa.'],
            ['numero_nivel' => 7, 'nombre' => 'Leyenda', 'puntos' => 2500, 'desc' => 'Tu nombre se cuenta en las historias.'],
            ['numero_nivel' => 8, 'nombre' => 'Ascensión Divina', 'puntos' => 4000, 'desc' => 'Has alcanzado la cúspide. ¡Eres una leyenda viva!']
        ];
        
        foreach ($niveles as $nivelData) {
            $nivel = new Nivel();
            $nivel->setNombre($nivelData['nombre'])
                ->setPuntosRequeridos($nivelData['puntos'])
                ->setDescripcion($nivelData['desc'])
                ->setNumNivel($nivelData['numero_nivel']);
            
            $manager->persist($nivel);
            
            // Set reference for the initial level
            if ($nivelData['numero_nivel'] === 1) {
                $this->addReference(self::NIVEL_PRINCIPIANTE, $nivel);
            }
        }

        $manager->flush();
    }
}
