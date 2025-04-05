<?php

// src/DataFixtures/QuizzFixtures.php (actualizado)
namespace App\DataFixtures;

use App\Entity\Quizz;
use App\Entity\Curso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuizzFixtures extends Fixture
{
    public const QUIZZES = [
        [
            'titulo' => 'Fundamentos de Programación',
            'descripcion' => 'Quiz sobre conceptos básicos de programación',
            'curso' => 'introducción-a-la-programación',
            'tiempoLimite' => 30,
            'puntosTotales' => 100,
            'diasLimite' => 7
        ],
        [
            'titulo' => 'React Básico',
            'descripcion' => 'Quiz sobre conceptos fundamentales de React',
            'curso' => 'desarrollo-web-con-react',
            'tiempoLimite' => 45,
            'puntosTotales' => 100,
            'diasLimite' => 7
        ],
        [
            'titulo' => 'Bases de Datos SQL',
            'descripcion' => 'Quiz sobre conceptos básicos de SQL',
            'curso' => 'bases-de-datos-sql',
            'tiempoLimite' => 40,
            'puntosTotales' => 100,
            'diasLimite' => 7
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::QUIZZES as $quizzData) {
            $quizz = new Quizz();
            $quizz->setTitulo($quizzData['titulo']);
            $quizz->setDescripcion($quizzData['descripcion']);
            $quizz->setFechaPublicacion(new \DateTime());
            $quizz->setFechaLimite(new \DateTime('+' . $quizzData['diasLimite'] . ' days'));
            $quizz->setTiempoLimite($quizzData['tiempoLimite']);
            $quizz->setPuntosTotales($quizzData['puntosTotales']);

            $manager->persist($quizz);
            $this->addReference('quizz-' . strtolower(str_replace(' ', '-', $quizzData['titulo'])), $quizz);
        }

        $manager->flush();
    }
}
