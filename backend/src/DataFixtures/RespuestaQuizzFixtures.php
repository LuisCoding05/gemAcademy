<?php

// src/DataFixtures/RespuestaQuizzFixtures.php
namespace App\DataFixtures;

use App\Entity\IntentoQuizz;
use App\Entity\PreguntaQuizz;
use App\Entity\RespuestaQuizz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RespuestaQuizzFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $respuesta = new RespuestaQuizz();
        $respuesta->setRespuesta('Un framework PHP')
            ->setEsCorrecta(true)
            ->setPuntosObtenidos(10)
            ->setIdIntento($this->getReference(IntentoQuizzFixtures::INTENTO_REFERENCE, IntentoQuizz::class))
            ->setIdPregunta($this->getReference(PreguntaQuizzFixtures::PREGUNTA_REFERENCE, PreguntaQuizz::class));
        
        $manager->persist($respuesta);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            IntentoQuizzFixtures::class,
            PreguntaQuizzFixtures::class
        ];
    }
}
