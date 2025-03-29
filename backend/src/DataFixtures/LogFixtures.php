<?php

// src/DataFixtures/LogFixtures.php
namespace App\DataFixtures;

use App\Entity\Log;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $usuarios = [
            $this->getReference(UsuarioFixtures::ADMIN_USER_REFERENCE, Usuario::class),
            $this->getReference(UsuarioFixtures::TEACHER_USER_REFERENCE, Usuario::class),
            $this->getReference(UsuarioFixtures::STUDENT_USER_REFERENCE, Usuario::class)
        ];

        $acciones = [
            'Inicio de sesión exitoso',
            'Actualización de perfil',
            'Entrega de tarea',
            'Completado de quizz'
        ];

        foreach ($usuarios as $usuario) {
            for ($i = 0; $i < 5; $i++) {
                $log = new Log();
                $log->setUsuario($usuario)
                    ->setFecha(new \DateTime("-{$i} hours"));
                
                $manager->persist($log);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class
        ];
    }
}
