<?php

// src/DataFixtures/MensajeForoFixtures.php
namespace App\DataFixtures;

use App\Entity\Foro;
use App\Entity\MensajeForo;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MensajeForoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Mensaje padre
        $mensajePadre = new MensajeForo();
        $mensajePadre->setContenido('Hola, ¿cómo van con el curso?')
            ->setFechaPublicacion(new \DateTime('-1 day'))
            ->setIdForo($this->getReference(ForoFixtures::FORO_REFERENCE, Foro::class))
            ->setIdUsuario($this->getReference(UsuarioFixtures::TEACHER_USER_REFERENCE, Usuario::class));
        
        // Mensaje hijo
        $mensajeHijo = new MensajeForo();
        $mensajeHijo->setContenido('Muy bien, gracias por preguntar')
            ->setFechaPublicacion(new \DateTime())
            ->setIdMensajePadre($mensajePadre)
            ->setIdForo($this->getReference(ForoFixtures::FORO_REFERENCE, Foro::class))
            ->setIdUsuario($this->getReference(UsuarioFixtures::STUDENT_USER_REFERENCE, Usuario::class));

        $manager->persist($mensajePadre);
        $manager->persist($mensajeHijo);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ForoFixtures::class,
            UsuarioFixtures::class
        ];
    }
}