<?php

// src/DataFixtures/PreguntaQuizzFixtures.php (actualizado)
namespace App\DataFixtures;

use App\Entity\PreguntaQuizz;
use App\Entity\Quizz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PreguntaQuizzFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREGUNTA_REFERENCE = 'pregunta-quizz';

    public function load(ObjectManager $manager): void
    {
        $pregunta = new PreguntaQuizz();
        $pregunta->setPregunta('¿Qué es Symfony?')
            ->setPuntos(10)
            ->setOrden(1)
            ->setIdQuizz($this->getReference(QuizzFixtures::QUIZZ_REFERENCE, Quizz::class));
        
        $manager->persist($pregunta);
        $this->addReference(self::PREGUNTA_REFERENCE, $pregunta);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [QuizzFixtures::class];
    }
}