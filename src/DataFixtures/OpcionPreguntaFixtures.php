<?php

// src/DataFixtures/OpcionPreguntaFixtures.php
namespace App\DataFixtures;

use App\Entity\OpcionPregunta;
use App\Entity\PreguntaQuizz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OpcionPreguntaFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $opciones = [
            ['texto' => 'Un framework PHP', 'correcta' => true, 'retro' => 'Correcto!'],
            ['texto' => 'Un lenguaje de programación', 'correcta' => false, 'retro' => 'Incorrecto'],
            ['texto' => 'Una base de datos', 'correcta' => false, 'retro' => 'Error']
        ];

        foreach ($opciones as $opcionData) {
            $opcion = new OpcionPregunta();
            $opcion->setTexto($opcionData['texto'])
                ->setEsCorrecta($opcionData['correcta'])
                ->setRetroalimentacion($opcionData['retro'])
                ->setIdPregunta($this->getReference(PreguntaQuizzFixtures::PREGUNTA_REFERENCE, PreguntaQuizz::class));
            
            $manager->persist($opcion);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [PreguntaQuizzFixtures::class];
    }
}
