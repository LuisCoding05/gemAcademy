<?php
// src/DataFixtures/UsuarioFixtures.php
namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const TEACHER_USER_REFERENCE = 'teacher-user';
    public const STUDENT_USER_REFERENCE = 'student-user';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new Usuario();
        $admin->setUsername('admin')
            ->setEmail('admin@example.com')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'))
            ->setNombre('Admin')
            ->setApellido('Istrador')
            ->setRoles(['ROLE_ADMIN'])
            ->setVerificado(true)
            ->setBan(false);
        $manager->persist($admin);
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        // Profesor
        $teacher = new Usuario();
        $teacher->setUsername('profesor1')
            ->setEmail('profesor@example.com')
            ->setPassword($this->passwordHasher->hashPassword($teacher, 'profesor123'))
            ->setNombre('Juan')
            ->setApellido('Pérez')
            ->setRoles(['ROLE_USER'])
            ->setVerificado(true)
            ->setBan(false);
        $manager->persist($teacher);
        $this->addReference(self::TEACHER_USER_REFERENCE, $teacher);

        // Estudiante
        $student = new Usuario();
        $student->setUsername('estudiante1')
            ->setEmail('estudiante@example.com')
            ->setPassword($this->passwordHasher->hashPassword($student, 'estudiante123'))
            ->setNombre('María')
            ->setApellido('García')
            ->setRoles(['ROLE_USER'])
            ->setVerificado(true)
            ->setBan(false);
        $manager->persist($student);
        $this->addReference(self::STUDENT_USER_REFERENCE, $student);

        $manager->flush();
    }
}