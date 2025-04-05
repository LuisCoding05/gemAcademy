<?php

// src/DataFixtures/UsuarioLogroFixtures.php
namespace App\DataFixtures;

use App\Entity\Logro;
use App\Entity\Usuario;
use App\Entity\UsuarioLogro;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UsuarioLogroFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $usuarioLogro = new UsuarioLogro();
        $usuarioLogro->setIdUsuario($this->getReference('usuario-estudiante1', Usuario::class))
            ->setIdLogro($this->getReference('curso-completado', Logro::class));
        
        $manager->persist($usuarioLogro);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            LogroFixtures::class
        ];
    }
}
