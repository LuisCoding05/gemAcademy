<?php

namespace App\DataFixtures;

use App\Entity\Curso;
use App\Entity\Tarea;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TareaFixtures extends Fixture implements DependentFixtureInterface
{
    public const TAREAS = [
        'introducción-a-la-programación' => [
            [
                'titulo' => 'Ejercicios básicos de algoritmos',
                'descripcion' => 'Resolver problemas básicos usando pseudocódigo y diagramas de flujo',
                'dias_plazo' => 7,
                'puntos_maximos' => 100,
                'es_obligatoria' => true
            ],
            [
                'titulo' => 'Proyecto final: Calculadora simple',
                'descripcion' => 'Desarrollar una calculadora que realice operaciones básicas',
                'dias_plazo' => 14,
                'puntos_maximos' => 150,
                'es_obligatoria' => true
            ]
        ],
        'desarrollo-web-con-react' => [
            [
                'titulo' => 'Componentes básicos',
                'descripcion' => 'Crear componentes reutilizables con props y state',
                'dias_plazo' => 5,
                'puntos_maximos' => 80,
                'es_obligatoria' => true
            ],
            [
                'titulo' => 'Gestión de estado con hooks',
                'descripcion' => 'Implementar useState y useEffect en una aplicación',
                'dias_plazo' => 7,
                'puntos_maximos' => 100,
                'es_obligatoria' => true
            ],
            [
                'titulo' => 'Proyecto: Lista de tareas',
                'descripcion' => 'Desarrollar una aplicación de lista de tareas con React',
                'dias_plazo' => 10,
                'puntos_maximos' => 120,
                'es_obligatoria' => false
            ]
        ],
        'backend-con-symfony' => [
            [
                'titulo' => 'API REST básica',
                'descripcion' => 'Crear endpoints CRUD para una entidad simple',
                'dias_plazo' => 10,
                'puntos_maximos' => 100,
                'es_obligatoria' => true
            ],
            [
                'titulo' => 'Autenticación JWT',
                'descripcion' => 'Implementar autenticación usando tokens JWT',
                'dias_plazo' => 7,
                'puntos_maximos' => 120,
                'es_obligatoria' => true
            ]
        ],
        'bases-de-datos-sql' => [
            [
                'titulo' => 'Diseño de esquema',
                'descripcion' => 'Crear un esquema de base de datos normalizado',
                'dias_plazo' => 5,
                'puntos_maximos' => 90,
                'es_obligatoria' => true
            ]
        ],
        'desarrollo-de-videojuegos-con-unity' => [
            [
                'titulo' => 'Movimiento de personaje',
                'descripcion' => 'Implementar controles básicos de movimiento',
                'dias_plazo' => 7,
                'puntos_maximos' => 100,
                'es_obligatoria' => true
            ],
            [
                'titulo' => 'Sistema de colisiones',
                'descripcion' => 'Crear un sistema de colisiones y física básica',
                'dias_plazo' => 10,
                'puntos_maximos' => 120,
                'es_obligatoria' => true
            ]
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TAREAS as $cursoSlug => $tareasList) {
            foreach ($tareasList as $index => $tareaData) {
                $tarea = new Tarea();
                $tarea->setTitulo($tareaData['titulo']);
                $tarea->setDescripcion($tareaData['descripcion']);
                $tarea->setFechaLimite((new \DateTime())->modify('+' . $tareaData['dias_plazo'] . ' days'));
                $tarea->setPuntosMaximos($tareaData['puntos_maximos']);
                $tarea->setEsObligatoria($tareaData['es_obligatoria']);
                $tarea->setIdCurso($this->getReference('curso-' . $cursoSlug, Curso::class));
                
                $manager->persist($tarea);
                $this->addReference('tarea-' . $cursoSlug . '-' . ($index + 1), $tarea);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CursoFixtures::class];
    }
}