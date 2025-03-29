<?php

// src/DataFixtures/ForoFixtures.php
namespace App\DataFixtures;

use App\Entity\Foro;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ForoFixtures extends Fixture
{
    public const FORO_REFERENCE = 'default-foro';
    public function load(ObjectManager $manager): void
    {
        $foro = new Foro();
        $foro->setTitulo('Foro Principal')
            ->setDescripcion('Discusiones generales sobre cursos')
            ->setFechaCreacion(new \DateTime());

        $manager->persist($foro);
        $this->addReference(self::FORO_REFERENCE, $foro);
        $manager->flush();
    }
}
