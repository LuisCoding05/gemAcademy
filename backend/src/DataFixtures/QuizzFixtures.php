<?php

// src/DataFixtures/QuizzFixtures.php (actualizado)
namespace App\DataFixtures;

use App\Entity\Quizz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuizzFixtures extends Fixture
{
    public const QUIZZ_REFERENCE = 'main-quizz';

    public function load(ObjectManager $manager): void
    {
        $quizz = new Quizz();
        $quizz->setTitulo('Examen Symfony')
            ->setDescripcion('Prueba inicial de conocimientos')
            ->setFechaLimite(new \DateTime('+1 week'))
            ->setTiempoLimite(30)
            ->setPuntosTotales(100);
        
        $manager->persist($quizz);
        $this->addReference(self::QUIZZ_REFERENCE, $quizz);
        $manager->flush();
    }
}
