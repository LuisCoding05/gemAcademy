<?php

// src/DataFixtures/QuizzFixtures.php (actualizado)
namespace App\DataFixtures;

use App\Entity\Quizz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuizzFixtures extends Fixture
{
    public const QUIZZ_REFERENCE = 'main-quizz';

    public function load(ObjectManager $manager): void
    {
        $quizzes = [
            [
                'titulo' => 'Fundamentos de PHP',
                'descripcion' => 'Evaluación de conceptos básicos de PHP y programación orientada a objetos',
                'tiempoLimite' => 45,
                'puntosTotales' => 100,
                'diasLimite' => 7,
                'referencia' => self::QUIZZ_REFERENCE
            ],
            [
                'titulo' => 'JavaScript Básico',
                'descripcion' => 'Test de conocimientos fundamentales de JavaScript',
                'tiempoLimite' => 30,
                'puntosTotales' => 80,
                'diasLimite' => 5,
                'referencia' => 'quizz-js-basico'
            ],
            [
                'titulo' => 'Estructuras de Datos',
                'descripcion' => 'Evaluación sobre arrays, listas enlazadas, árboles y grafos',
                'tiempoLimite' => 60,
                'puntosTotales' => 120,
                'diasLimite' => 10,
                'referencia' => 'quizz-estructuras'
            ],
            [
                'titulo' => 'SQL Avanzado',
                'descripcion' => 'Examen de consultas complejas, joins y optimización',
                'tiempoLimite' => 50,
                'puntosTotales' => 100,
                'diasLimite' => 3,
                'referencia' => 'quizz-sql'
            ],
            [
                'titulo' => 'Patrones de Diseño',
                'descripcion' => 'Test sobre patrones creacionales, estructurales y de comportamiento',
                'tiempoLimite' => 40,
                'puntosTotales' => 90,
                'diasLimite' => 6,
                'referencia' => 'quizz-patrones'
            ],
            [
                'titulo' => 'React Hooks',
                'descripcion' => 'Evaluación práctica sobre el uso de hooks en React',
                'tiempoLimite' => 35,
                'puntosTotales' => 85,
                'diasLimite' => 4,
                'referencia' => 'quizz-react'
            ],
            [
                'titulo' => 'Testing con Jest',
                'descripcion' => 'Examen sobre pruebas unitarias y de integración',
                'tiempoLimite' => 45,
                'puntosTotales' => 95,
                'diasLimite' => 5,
                'referencia' => 'quizz-testing'
            ],
            [
                'titulo' => 'Git Avanzado',
                'descripcion' => 'Evaluación de comandos git y flujos de trabajo',
                'tiempoLimite' => 30,
                'puntosTotales' => 75,
                'diasLimite' => 3,
                'referencia' => 'quizz-git'
            ],
            [
                'titulo' => 'Seguridad Web',
                'descripcion' => 'Test sobre vulnerabilidades comunes y mejores prácticas',
                'tiempoLimite' => 55,
                'puntosTotales' => 110,
                'diasLimite' => 7,
                'referencia' => 'quizz-security'
            ],
            [
                'titulo' => 'Docker y Kubernetes',
                'descripcion' => 'Evaluación de conceptos de contenedores y orquestación',
                'tiempoLimite' => 50,
                'puntosTotales' => 100,
                'diasLimite' => 6,
                'referencia' => 'quizz-docker'
            ]
        ];

        foreach ($quizzes as $quizzData) {
            $quizz = new Quizz();
            $quizz->setTitulo($quizzData['titulo'])
                ->setDescripcion($quizzData['descripcion'])
                ->setFechaLimite(new \DateTime('+' . $quizzData['diasLimite'] . ' days'))
                ->setTiempoLimite($quizzData['tiempoLimite'])
                ->setPuntosTotales($quizzData['puntosTotales']);
            
            $manager->persist($quizz);
            $this->addReference($quizzData['referencia'], $quizz);
        }

        $manager->flush();
    }
}
