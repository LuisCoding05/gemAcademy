<?php

namespace App\DataFixtures;

use App\Entity\MaterialCompletado;
use App\Entity\Material;
use App\Entity\UsuarioCurso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MaterialCompletadoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $materialCompletado = new MaterialCompletado();
        $materialCompletado->setMaterial($this->getReference('material-introducción-a-la-programación-1', Material::class))
            ->setUsuarioCurso($this->getReference('usuario-curso-estudiante1', UsuarioCurso::class))
            ->setFechaCompletado(new \DateTime());
        
        $manager->persist($materialCompletado);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MaterialFixtures::class,
            UsuarioCursoFixtures::class
        ];
    }
} 