<?php

// src/DataFixtures/UsuarioCursoFixtures.php
namespace App\DataFixtures;

use App\Entity\Curso;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UsuarioCursoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $usuarioCurso = new UsuarioCurso();
        $usuarioCurso->setIdUsuario($this->getReference(UsuarioFixtures::STUDENT_USER_REFERENCE, Usuario::class))
            ->setIdCurso($this->getReference(CursoFixtures::CURSO_REFERENCE, Curso::class))
            ->setMaterialesCompletados(2)
            ->setMaterialesTotales(10)
            ->setTareasCompletadas(1)
            ->setTareasTotales(5)
            ->setQuizzesCompletados(0)
            ->setQuizzesTotales(3)
            ->setPorcentajeCompletado('20.00')
            ->setUltimaActualizacion(new \DateTime());
        
        $manager->persist($usuarioCurso);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            CursoFixtures::class
        ];
    }
}
