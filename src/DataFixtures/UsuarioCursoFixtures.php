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
        $usuarioCurso->setIdUsuario($this->getReference('usuario-estudiante1', Usuario::class))
            ->setIdCurso($this->getReference('curso-introducción-a-la-programación', Curso::class))
            ->setMaterialesCompletados(2)
            ->setTareasCompletadas(1)
            ->setQuizzesCompletados(0)
            ->setPorcentajeCompletado('20.00')
            ->setUltimaActualizacion(new \DateTime());
        
        $manager->persist($usuarioCurso);
        $manager->flush();
        
        $this->addReference('usuario-curso-estudiante1', $usuarioCurso);
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            CursoFixtures::class
        ];
    }
}
