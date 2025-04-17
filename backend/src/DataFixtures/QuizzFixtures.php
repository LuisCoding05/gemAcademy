<?php

// src/DataFixtures/QuizzFixtures.php (actualizado)
namespace App\DataFixtures;

use App\Entity\Quizz;
use App\Entity\Curso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class QuizzFixtures extends Fixture implements DependentFixtureInterface
{
    public const QUIZZES = [
        'introducción-a-la-programación' => [
            [
                'titulo' => 'Quiz de Introducción',
                'descripcion' => 'Evaluación de conceptos básicos de programación',
                'tiempoLimite' => 30,
                'puntosTotales' => 100,
                'diasLimite' => 7,
                "intentos" => 5
            ],
            [
                'titulo' => 'Quiz de Variables',
                'descripcion' => 'Evaluación sobre variables y tipos de datos',
                'tiempoLimite' => 20,
                'puntosTotales' => 50,
                'diasLimite' => 5,
                "intentos" => 5
            ]
        ],
        'desarrollo-web-con-react' => [
            [
                'titulo' => 'Quiz de React Básico',
                'descripcion' => 'Evaluación de conceptos fundamentales de React',
                'tiempoLimite' => 45,
                'puntosTotales' => 100,
                'diasLimite' => 7,
                "intentos" => 5
            ],
            [
                'titulo' => 'Quiz de Componentes',
                'descripcion' => 'Evaluación sobre componentes y props',
                'tiempoLimite' => 30,
                'puntosTotales' => 75,
                'diasLimite' => 5,
                "intentos" => 5
            ]
        ],
        'backend-con-symfony' => [
            [
                'titulo' => 'Quiz de Symfony',
                'descripcion' => 'Evaluación de conceptos básicos de Symfony',
                'tiempoLimite' => 40,
                'puntosTotales' => 100,
                'diasLimite' => 7,
                "intentos" => 5
            ],
            [
                'titulo' => 'Quiz de Doctrine',
                'descripcion' => 'Evaluación sobre entidades y consultas',
                'tiempoLimite' => 35,
                'puntosTotales' => 80,
                'diasLimite' => 5,
                "intentos" => 5
            ]
        ],
        'bases-de-datos-sql' => [
            [
                'titulo' => 'Quiz de SQL Básico',
                'descripcion' => 'Evaluación de conceptos básicos de SQL',
                'tiempoLimite' => 30,
                'puntosTotales' => 100,
                'diasLimite' => 7,
                "intentos" => 5
            ],
            [
                'titulo' => 'Quiz de Consultas Avanzadas',
                'descripcion' => 'Evaluación de técnicas avanzadas de SQL',
                'tiempoLimite' => 45,
                'puntosTotales' => 120,
                'diasLimite' => 7,
                "intentos" => 5
            ]
        ],
        'desarrollo-de-videojuegos-con-unity' => [
            [
                'titulo' => 'Quiz de Unity',
                'descripcion' => 'Evaluación de conceptos básicos de Unity',
                'tiempoLimite' => 30,
                'puntosTotales' => 100,
                'diasLimite' => 7,
                "intentos" => 5
            ],
            [
                'titulo' => 'Quiz de C# en Unity',
                'descripcion' => 'Evaluación de scripting en C# para Unity',
                'tiempoLimite' => 40,
                'puntosTotales' => 100,
                'diasLimite' => 7,
                "intentos" => 5
            ]
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::QUIZZES as $cursoSlug => $quizzesList) {
            foreach ($quizzesList as $index => $quizzData) {
                $quizz = new Quizz();
                $quizz->setTitulo($quizzData['titulo']);
                $quizz->setDescripcion($quizzData['descripcion']);
                $quizz->setFechaPublicacion(new \DateTime());
                $quizz->setFechaLimite(new \DateTime('+' . $quizzData['diasLimite'] . ' days'));
                $quizz->setTiempoLimite($quizzData['tiempoLimite']);
                $quizz->setPuntosTotales($quizzData['puntosTotales']);
                $quizz->setIntentosPermitidos($quizzData["intentos"]);
                $quizz->setIdCurso($this->getReference('curso-' . $cursoSlug, Curso::class));
                
                $manager->persist($quizz);
                $this->addReference('quizz-' . $cursoSlug . '-' . ($index + 1), $quizz);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CursoFixtures::class];
    }
}
