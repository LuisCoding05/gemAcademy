<?php

// src/DataFixtures/IntentoQuizzFixtures.php
namespace App\DataFixtures;

use App\Entity\IntentoQuizz;
use App\Entity\Quizz;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IntentoQuizzFixtures extends Fixture implements DependentFixtureInterface
{
    public const INTENTO_REFERENCE = "intento-ejemplo";
    public function load(ObjectManager $manager): void
    {
        $intento = new IntentoQuizz();
        $intento->setIdQuizz($this->getReference('quizz-introducción-a-la-programación-1', Quizz::class))
            ->setIdUsuario($this->getReference('usuario-estudiante1', Usuario::class))
            ->setFechaInicio(new \DateTime('-1 hour'))
            ->setFechaFin(new \DateTime())
            ->setPuntuacionTotal(85)
            ->setCompletado(true);

        $manager->persist($intento);
        $this->addReference(self::INTENTO_REFERENCE, $intento);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            QuizzFixtures::class,
            UsuarioFixtures::class
        ];
    }
}
