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
        // Admin principal
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

        // Admins adicionales
        $adminNames = [
            ['username' => 'admin2', 'email' => 'admin2@example.com', 'nombre' => 'Laura', 'apellido' => 'Martínez'],
            ['username' => 'admin3', 'email' => 'admin3@example.com', 'nombre' => 'Carlos', 'apellido' => 'Rodríguez']
        ];

        foreach ($adminNames as $index => $adminData) {
            $adminUser = new Usuario();
            $adminUser->setUsername($adminData['username'])
                ->setEmail($adminData['email'])
                ->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'))
                ->setNombre($adminData['nombre'])
                ->setApellido($adminData['apellido'])
                ->setRoles(['ROLE_ADMIN'])
                ->setVerificado(true)
                ->setBan(false);
            $manager->persist($adminUser);
            $this->addReference('admin-user-' . ($index + 2), $adminUser);
        }

        // Profesores
        $teacherNames = [
            ['username' => 'profesor1', 'email' => 'profesor1@example.com', 'nombre' => 'Juan', 'apellido' => 'Pérez'],
            ['username' => 'profesor2', 'email' => 'profesor2@example.com', 'nombre' => 'Ana', 'apellido' => 'López'],
            ['username' => 'profesor3', 'email' => 'profesor3@example.com', 'nombre' => 'Miguel', 'apellido' => 'Sánchez'],
            ['username' => 'profesor4', 'email' => 'profesor4@example.com', 'nombre' => 'Isabel', 'apellido' => 'Torres'],
            ['username' => 'profesor5', 'email' => 'profesor5@example.com', 'nombre' => 'Roberto', 'apellido' => 'García']
        ];

        foreach ($teacherNames as $index => $teacherData) {
            $teacher = new Usuario();
            $teacher->setUsername($teacherData['username'])
                ->setEmail($teacherData['email'])
                ->setPassword($this->passwordHasher->hashPassword($teacher, 'profesor123'))
                ->setNombre($teacherData['nombre'])
                ->setApellido($teacherData['apellido'])
                ->setRoles(['ROLE_TEACHER'])
                ->setVerificado(true)
                ->setBan(false);
            $manager->persist($teacher);
            $this->addReference('teacher-user-' . ($index + 1), $teacher);
        }

        // Estudiantes
        $studentNames = [
            ['username' => 'estudiante1', 'email' => 'estudiante1@example.com', 'nombre' => 'María', 'apellido' => 'García'],
            ['username' => 'estudiante2', 'email' => 'estudiante2@example.com', 'nombre' => 'Pedro', 'apellido' => 'Fernández'],
            ['username' => 'estudiante3', 'email' => 'estudiante3@example.com', 'nombre' => 'Lucía', 'apellido' => 'Martín'],
            ['username' => 'estudiante4', 'email' => 'estudiante4@example.com', 'nombre' => 'David', 'apellido' => 'Jiménez'],
            ['username' => 'estudiante5', 'email' => 'estudiante5@example.com', 'nombre' => 'Sara', 'apellido' => 'Ruiz'],
            ['username' => 'estudiante6', 'email' => 'estudiante6@example.com', 'nombre' => 'Pablo', 'apellido' => 'Díaz'],
            ['username' => 'estudiante7', 'email' => 'estudiante7@example.com', 'nombre' => 'Elena', 'apellido' => 'Moreno'],
            ['username' => 'estudiante8', 'email' => 'estudiante8@example.com', 'nombre' => 'Jorge', 'apellido' => 'Álvarez'],
            ['username' => 'estudiante9', 'email' => 'estudiante9@example.com', 'nombre' => 'Carmen', 'apellido' => 'Muñoz'],
            ['username' => 'estudiante10', 'email' => 'estudiante10@example.com', 'nombre' => 'Daniel', 'apellido' => 'Romero']
        ];

        foreach ($studentNames as $index => $studentData) {
            $student = new Usuario();
            $student->setUsername($studentData['username'])
                ->setEmail($studentData['email'])
                ->setPassword($this->passwordHasher->hashPassword($student, 'estudiante123'))
                ->setNombre($studentData['nombre'])
                ->setApellido($studentData['apellido'])
                ->setRoles(['ROLE_USER'])
                ->setVerificado($index < 8) // Los últimos dos no están verificados
                ->setBan($index === 9); // El último está baneado
            $manager->persist($student);
            $this->addReference('student-user-' . ($index + 1), $student);
        }

        // Usuarios no verificados adicionales
        $unverifiedNames = [
            ['username' => 'pending1', 'email' => 'pending1@example.com', 'nombre' => 'Usuario', 'apellido' => 'Pendiente1'],
            ['username' => 'pending2', 'email' => 'pending2@example.com', 'nombre' => 'Usuario', 'apellido' => 'Pendiente2']
        ];

        foreach ($unverifiedNames as $index => $userData) {
            $unverifiedUser = new Usuario();
            $unverifiedUser->setUsername($userData['username'])
                ->setEmail($userData['email'])
                ->setPassword($this->passwordHasher->hashPassword($unverifiedUser, 'pending123'))
                ->setNombre($userData['nombre'])
                ->setApellido($userData['apellido'])
                ->setRoles(['ROLE_USER'])
                ->setVerificado(false)
                ->setBan(false);
            $manager->persist($unverifiedUser);
            $this->addReference('unverified-user-' . ($index + 1), $unverifiedUser);
        }

        $manager->flush();
    }
}