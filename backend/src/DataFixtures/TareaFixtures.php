<?php

// src/DataFixtures/TareaFixtures.php
namespace App\DataFixtures;

use App\Entity\Curso;
use App\Entity\Tarea;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TareaFixtures extends Fixture implements DependentFixtureInterface
{
    public const TAREA_REFERENCE = 'main-tarea';

    public function load(ObjectManager $manager): void
    {
        $tarea = new Tarea();
        $tarea->setTitulo('Primera tarea de Symfony')
            ->setDescripcion('Crear un controlador básico')
            ->setFechaLimite(new \DateTime('+3 days'))
            ->setPuntosMaximos(100)
            ->setEsObligatoria(true)
            ->setIdCurso($this->getReference(CursoFixtures::CURSO_REFERENCE, Curso::class));
        
        $manager->persist($tarea);
        $this->addReference(self::TAREA_REFERENCE, $tarea);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CursoFixtures::class];
    }
}