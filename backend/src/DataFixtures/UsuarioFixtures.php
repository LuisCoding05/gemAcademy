<?php

namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    // src/DataFixtures/UsuarioFixtures.php
public function load(ObjectManager $manager): void
{
    $usuarios = [
        [
            'username' => 'admin',
            'email' => 'admin@academy.com',
            'password' => 'admin123',
            'ban' => false,
            'roles' => ['ROLE_ADMIN']
        ],
        [
            'username' => 'profesor1',
            'email' => 'profesor1@academy.com',
            'password' => 'prof123',
            'ban' => false,
            'roles' => ['ROLE_USER']
        ],
        [
            'username' => 'estudiante1',
            'email' => 'estudiante1@academy.com',
            'password' => 'est123',
            'ban' => false,
            'roles' => ['ROLE_USER']
        ]
    ];

    foreach ($usuarios as $userData) {
        $usuario = new Usuario();
        $usuario->setUsername($userData['username']);
        $usuario->setEmail($userData['email']);
        $usuario->setRoles($userData['roles']);
        $usuario->setBan($userData['ban']);
        $usuario->setFechaRegistro(new \DateTime());
        
        // Hashear la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword(
            $usuario,
            $userData['password']
        );
        $usuario->setPassword($hashedPassword);

        $manager->persist($usuario);
    }

    $manager->flush();
}
}