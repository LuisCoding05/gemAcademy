<?php

// src/DataFixtures/CursoFixtures.php
namespace App\DataFixtures;

use App\Entity\Curso;
use App\Entity\Usuario;
use App\Entity\Imagen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CursoFixtures extends Fixture implements DependentFixtureInterface
{
    public const CURSOS = [
        [
            'titulo' => 'Introducción a la Programación',
            'descripcion' => 'Aprende los fundamentos de la programación desde cero',
            'imagen' => 'tecnologia',
            'profesor' => 'profesor1',
            'duracion' => 40,
            'nivel' => 'Principiante'
        ],
        [
            'titulo' => 'Desarrollo Web con React',
            'descripcion' => 'Aprende a crear aplicaciones web modernas con React',
            'imagen' => 'ui',
            'profesor' => 'profesor1',
            'duracion' => 60,
            'nivel' => 'Intermedio'
        ],
        [
            'titulo' => 'Backend con Symfony',
            'descripcion' => 'Desarrollo de APIs y servicios backend con Symfony',
            'imagen' => 'estudios',
            'profesor' => 'profesor2',
            'duracion' => 50,
            'nivel' => 'Intermedio'
        ],
        [
            'titulo' => 'Bases de Datos SQL',
            'descripcion' => 'Aprende a diseñar y gestionar bases de datos relacionales',
            'imagen' => 'tecnologia',
            'profesor' => 'profesor2',
            'duracion' => 45,
            'nivel' => 'Principiante'
        ],
        [
            'titulo' => 'Desarrollo de Videojuegos con Unity',
            'descripcion' => 'Crea tus propios videojuegos usando el motor Unity',
            'imagen' => 'tecnologia',
            'profesor' => 'profesor1',
            'duracion' => 80,
            'nivel' => 'Avanzado'
        ],
        [
            'titulo' => 'Inteligencia Artificial Básica',
            'descripcion' => 'Introducción a los conceptos fundamentales de IA',
            'imagen' => 'estudios',
            'profesor' => 'profesor2',
            'duracion' => 55,
            'nivel' => 'Intermedio'
        ],
        [
            'titulo' => 'Seguridad Informática',
            'descripcion' => 'Aprende a proteger tus sistemas y datos',
            'imagen' => 'tecnologia',
            'profesor' => 'profesor1',
            'duracion' => 65,
            'nivel' => 'Avanzado'
        ],
        [
            'titulo' => 'Diseño de Interfaces de Usuario',
            'descripcion' => 'Principios y técnicas de diseño UI/UX',
            'imagen' => 'ui',
            'profesor' => 'profesor2',
            'duracion' => 40,
            'nivel' => 'Intermedio'
        ],
        [
            'titulo' => 'Programación Móvil con Flutter',
            'descripcion' => 'Desarrollo de aplicaciones móviles multiplataforma',
            'imagen' => 'tecnologia',
            'profesor' => 'profesor1',
            'duracion' => 70,
            'nivel' => 'Intermedio'
        ],
        [
            'titulo' => 'DevOps y CI/CD',
            'descripcion' => 'Automatización de procesos de desarrollo y despliegue',
            'imagen' => 'estudios',
            'profesor' => 'profesor2',
            'duracion' => 60,
            'nivel' => 'Avanzado'
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CURSOS as $cursoData) {
            $curso = new Curso();
            $curso->setNombre($cursoData['titulo']);
            $curso->setDescripcion($cursoData['descripcion']);
            $curso->setImagen($this->getReference('imagen-' . $cursoData['imagen'], Imagen::class));
            $curso->setProfesor($this->getReference('usuario-' . $cursoData['profesor'], Usuario::class));
            
            $curso->setFechaCreacion(new \DateTime());
            

            $manager->persist($curso);
            $this->addReference('curso-' . strtolower(str_replace(' ', '-', $cursoData['titulo'])), $curso);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ImagenFixtures::class,
            UsuarioFixtures::class
        ];
    }
}