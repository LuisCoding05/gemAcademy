<?php
// src/DataFixtures/UsuarioFixtures.php
namespace App\DataFixtures;

use App\Entity\Usuario;
use App\Entity\Imagen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioFixtures extends Fixture
{
    public const USUARIOS = [
        [
            'username' => 'admin',
            'email' => 'admin@gemacademy.com',
            'password' => 'admin123',
            'roles' => ['ROLE_ADMIN'],
            'nombre' => 'Administrador',
            'apellido' => 'Sistema',
            'imagen' => 'default'
        ],
        [
            'username' => 'profesor1',
            'email' => 'profesor1@gemacademy.com',
            'password' => 'profesor123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Juan',
            'apellido' => 'García',
            'imagen' => 'gojo'
        ],
        [
            'username' => 'profesor2',
            'email' => 'profesor2@gemacademy.com',
            'password' => 'profesor123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'María',
            'apellido' => 'López',
            'imagen' => 'shinobu'
        ],
        [
            'username' => 'estudiante1',
            'email' => 'estudiante1@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Carlos',
            'apellido' => 'Martínez',
            'imagen' => 'light'
        ],
        [
            'username' => 'estudiante2',
            'email' => 'estudiante2@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Ana',
            'apellido' => 'Rodríguez',
            'imagen' => 'reina'
        ],
        [
            'username' => 'estudiante3',
            'email' => 'estudiante3@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Pedro',
            'apellido' => 'Sánchez',
            'imagen' => 'toji'
        ],
        [
            'username' => 'estudiante4',
            'email' => 'estudiante4@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Laura',
            'apellido' => 'Fernández',
            'imagen' => 'cortana'
        ],
        [
            'username' => 'estudiante5',
            'email' => 'estudiante5@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Miguel',
            'apellido' => 'Gómez',
            'imagen' => 'ironman'
        ],
        [
            'username' => 'estudiante6',
            'email' => 'estudiante6@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Sofía',
            'apellido' => 'Pérez',
            'imagen' => 'ui'
        ],
        [
            'username' => 'estudiante7',
            'email' => 'estudiante7@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'David',
            'apellido' => 'Ruiz',
            'imagen' => 'sherlock'
        ],
        [
            'username' => 'estudiante8',
            'email' => 'estudiante8@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Elena',
            'apellido' => 'Díaz',
            'imagen' => 'rana'
        ],
        [
            'username' => 'estudiante9',
            'email' => 'estudiante9@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Javier',
            'apellido' => 'Hernández',
            'imagen' => 'L'
        ],
        [
            'username' => 'estudiante10',
            'email' => 'estudiante10@gemacademy.com',
            'password' => 'estudiante123',
            'roles' => ['ROLE_USER'],
            'nombre' => 'Isabel',
            'apellido' => 'Jiménez',
            'imagen' => 'gato_sabio'
        ]
    ];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        foreach (self::USUARIOS as $usuarioData) {
            $usuario = new Usuario();
            $usuario->setUsername($usuarioData['username']);
            $usuario->setEmail($usuarioData['email']);
            $usuario->setPassword($this->passwordHasher->hashPassword($usuario, $usuarioData['password']));
            $usuario->setRoles($usuarioData['roles']);
            $usuario->setNombre($usuarioData['nombre']);
            $usuario->setApellido($usuarioData['apellido']);
            $usuario->setImagen($this->getReference('imagen-' . $usuarioData['imagen'], Imagen::class));
            $usuario->setFechaRegistro(new \DateTime());
            $usuario->setVerificado(true);
            $usuario->setBan(false);

            $manager->persist($usuario);
            $this->addReference('usuario-' . $usuarioData['username'], $usuario);
        }

        $manager->flush();
    }
}