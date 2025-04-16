<?php

namespace App\DataFixtures;

use App\Entity\Material;
use App\Entity\Curso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MaterialFixtures extends Fixture implements DependentFixtureInterface
{
    public const MATERIALES = [
        'introducción-a-la-programación' => [
            [
                'titulo' => 'Introducción a la Programación',
                'descripcion' => 'Conceptos básicos de programación y algoritmos',
                'url' => 'https://ejemplo.com/intro-programacion.pdf',
                'orden' => 1
            ],
            [
                'titulo' => 'Variables y Tipos de Datos',
                'descripcion' => 'Fundamentos de variables y tipos de datos en programación',
                'url' => 'https://ejemplo.com/variables.pdf',
                'orden' => 2
            ],
            [
                'titulo' => 'Estructuras de Control',
                'descripcion' => 'Uso de condicionales y bucles en programación',
                'url' => 'https://ejemplo.com/estructuras.pdf',
                'orden' => 3
            ]
        ],
        'desarrollo-web-con-react' => [
            [
                'titulo' => 'Fundamentos de React',
                'descripcion' => 'Introducción a React y sus conceptos básicos',
                'url' => 'https://ejemplo.com/react-basico.pdf',
                'orden' => 1
            ],
            [
                'titulo' => 'Componentes y Props',
                'descripcion' => 'Trabajo con componentes y propiedades en React',
                'url' => 'https://ejemplo.com/componentes.pdf',
                'orden' => 2
            ],
            [
                'titulo' => 'Hooks y Estado',
                'descripcion' => 'Gestión de estado con hooks en React',
                'url' => 'https://ejemplo.com/hooks.pdf',
                'orden' => 3
            ]
        ],
        'backend-con-symfony' => [
            [
                'titulo' => 'Introducción a Symfony',
                'descripcion' => 'Conceptos básicos del framework Symfony',
                'url' => 'https://ejemplo.com/symfony-intro.pdf',
                'orden' => 1
            ],
            [
                'titulo' => 'Entidades y Doctrine',
                'descripcion' => 'Trabajo con entidades y el ORM Doctrine',
                'url' => 'https://ejemplo.com/doctrine.pdf',
                'orden' => 2
            ]
        ],
        'bases-de-datos-sql' => [
            [
                'titulo' => 'Fundamentos de SQL',
                'descripcion' => 'Conceptos básicos de SQL y bases de datos relacionales',
                'url' => 'https://ejemplo.com/sql-basico.pdf',
                'orden' => 1
            ],
            [
                'titulo' => 'Consultas Avanzadas',
                'descripcion' => 'Técnicas avanzadas de consultas SQL',
                'url' => 'https://ejemplo.com/sql-avanzado.pdf',
                'orden' => 2
            ]
        ],
        'desarrollo-de-videojuegos-con-unity' => [
            [
                'titulo' => 'Introducción a Unity',
                'descripcion' => 'Conceptos básicos del motor Unity',
                'url' => 'https://ejemplo.com/unity-intro.pdf',
                'orden' => 1
            ],
            [
                'titulo' => 'Scripting en C#',
                'descripcion' => 'Programación de comportamientos con C# en Unity',
                'url' => 'https://ejemplo.com/unity-scripting.pdf',
                'orden' => 2
            ]
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::MATERIALES as $cursoSlug => $materialesList) {
            foreach ($materialesList as $index => $materialData) {
                $material = new Material();
                $material->setTitulo($materialData['titulo']);
                $material->setDescripcion($materialData['descripcion']);
                $material->setOrden($materialData['orden']);
                $material->setFechaPublicacion(new \DateTime());
                $material->setIdCurso($this->getReference('curso-' . $cursoSlug, Curso::class));
                
                $manager->persist($material);
                $this->addReference('material-' . $cursoSlug . '-' . ($index + 1), $material);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CursoFixtures::class];
    }
} 