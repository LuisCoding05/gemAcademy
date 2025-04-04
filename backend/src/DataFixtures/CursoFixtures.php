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
        $cursos = [
            [
                'nombre' => 'Desarrollo Web con PHP',
                'descripcion' => 'Curso completo sobre desarrollo backend con PHP, frameworks modernos y mejores prácticas.',
                'profesor' => 'teacher-user-1',
                'referencia' => self::CURSO_REFERENCE
            ],
            [
                'nombre' => 'JavaScript Avanzado',
                'descripcion' => 'Domina JavaScript moderno, ES6+, promesas, async/await y patrones de diseño.',
                'profesor' => 'teacher-user-2',
                'referencia' => 'curso-javascript'
            ],
            [
                'nombre' => 'Introducción a Python',
                'descripcion' => 'Aprende los fundamentos de Python, desde variables hasta programación orientada a objetos.',
                'profesor' => 'teacher-user-3',
                'referencia' => 'curso-python'
            ],
            [
                'nombre' => 'React desde Cero',
                'descripcion' => 'Desarrollo de aplicaciones web modernas con React, hooks y gestión de estado.',
                'profesor' => 'teacher-user-4',
                'referencia' => 'curso-react'
            ],
            [
                'nombre' => 'Bases de Datos SQL',
                'descripcion' => 'Diseño y optimización de bases de datos relacionales con MySQL y PostgreSQL.',
                'profesor' => 'teacher-user-5',
                'referencia' => 'curso-sql'
            ],
            [
                'nombre' => 'DevOps Fundamentals',
                'descripcion' => 'Introducción a DevOps, CI/CD, Docker y despliegue en la nube.',
                'profesor' => 'teacher-user-1',
                'referencia' => 'curso-devops'
            ],
            [
                'nombre' => 'Desarrollo de APIs RESTful',
                'descripcion' => 'Diseño y desarrollo de APIs REST siguiendo las mejores prácticas y estándares.',
                'profesor' => 'teacher-user-2',
                'referencia' => 'curso-api'
            ],
            [
                'nombre' => 'Testing y QA',
                'descripcion' => 'Metodologías y herramientas para testing de software y control de calidad.',
                'profesor' => 'teacher-user-3',
                'referencia' => 'curso-testing'
            ],
            [
                'nombre' => 'Seguridad Web',
                'descripcion' => 'Aprende a proteger aplicaciones web contra vulnerabilidades comunes.',
                'profesor' => 'teacher-user-4',
                'referencia' => 'curso-security'
            ],
            [
                'nombre' => 'Machine Learning Básico',
                'descripcion' => 'Introducción al aprendizaje automático con Python y scikit-learn.',
                'profesor' => 'teacher-user-5',
                'referencia' => 'curso-ml'
            ]
        ];

        foreach ($cursos as $cursoData) {
            $curso = new Curso();
            $curso->setImagen($this->getReference(ImagenFixtures::DEFAULT_IMAGE_REFERENCE, Imagen::class));
            $curso->setProfesor($this->getReference($cursoData['profesor'], Usuario::class));
            $curso->setNombre($cursoData['nombre']);
            $curso->setDescripcion($cursoData['descripcion']);
            $curso->setFechaCreacion(new DateTime());
            
            // Algunos cursos tendrán fechas diferentes para simular cursos más antiguos y más nuevos
            if (rand(0, 1)) {
                $randomDays = rand(-100, -1);
                $curso->setFechaCreacion((new DateTime())->modify("$randomDays days"));
            }

            $manager->persist($curso);
            $this->addReference($cursoData['referencia'], $curso);
        }

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