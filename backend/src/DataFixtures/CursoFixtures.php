<?php

// src/DataFixtures/CursoFixtures.php
namespace App\DataFixtures;

use App\Entity\Curso;
use App\Entity\Usuario;
use App\Entity\Imagen;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CursoFixtures extends Fixture implements DependentFixtureInterface
{
    public const CURSO_REFERENCE = "main-curso";
    public function load(ObjectManager $manager): void
    {
        $curso = new Curso();
        $curso->setImagen($this->getReference(ImagenFixtures::DEFAULT_IMAGE_REFERENCE, Imagen::class));
        $curso->setProfesor($this->getReference(UsuarioFixtures::TEACHER_USER_REFERENCE, Usuario::class));
        $curso->setNombre("Curso de PHP");
        $curso->setDescripcion("Curso sobre php, y todo el contenido relacionado con backend de servidores web");
        $curso->setFechaCreacion(new DateTime());
        $manager->persist($curso);
        $this->addReference(self::CURSO_REFERENCE, $curso);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            ImagenFixtures::class
        ];
    }
}