<?php

// src/DataFixtures/UsuarioNivelFixtures.php
namespace App\DataFixtures;

use App\Entity\Nivel;
use App\Entity\Usuario;
use App\Entity\UsuarioNivel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UsuarioNivelFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $usuarioNivel = new UsuarioNivel();
        $usuarioNivel->setIdUsuario($this->getReference('usuario-estudiante1', Usuario::class))
            ->setIdNivel($this->getReference('nivel-inicial', Nivel::class))
            ->setPuntosSiguienteNivel(1000)
            ->setPuntosActuales(250);
        
        $manager->persist($usuarioNivel);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            NivelFixtures::class
        ];
    }
}
