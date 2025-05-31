<?php

namespace App\DataFixtures;

use App\Entity\Imagen;
use App\Entity\Logro;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LogroFixtures extends Fixture implements DependentFixtureInterface
{
    public const LOGRO_REFERENCE = "curso-completado";
    
    private function createLogro(
        ObjectManager $manager,
        string $titulo,
        string $motivo,
        int $puntos,
        string $imagenReference,
        ?string $reference = null
    ): void {
        $logro = new Logro();
        $logro->setTitulo($titulo)
            ->setMotivo($motivo)
            ->setPuntosOtorgados($puntos)
            ->setImagen($this->getReference($imagenReference, Imagen::class));
        
        $manager->persist($logro);
        if ($reference) {
            $this->addReference($reference, $logro);
        }
    }

    public function load(ObjectManager $manager): void
    {
        // Logros de Quizzes
        $this->createLogro(
            $manager,
            'Novato Aprendiz',
            'Completar un quiz sin importar el número de aciertos (primer quiz completado).',
            50,
            'imagen-estudios'
        );

        $this->createLogro(
            $manager,
            'Lanza Proyectiles',
            'Acertar al menos el 50 % de las preguntas de un quiz.',
            100,
            'imagen-gohan'
        );

        $this->createLogro(
            $manager,
            'Ultra Instinto Sin Dominar',
            'Fallar exactamente 1 pregunta en un quiz.',
            150,
            'imagen-ui'
        );

        $this->createLogro(
            $manager,
            'Ultra Instinto Dominado',
            'Acertar todas las preguntas de un quiz.',
            200,
            'imagen-mui'
        );

        $this->createLogro(
            $manager,
            'Six eyes unlocked',
            'Obtener 3 quizzes perfectos seguidos.',
            300,
            'imagen-gojo'
        );

        // Logros de Tareas
        $this->createLogro(
            $manager,
            'Entrenamiento Básico',
            'Entregar tu primera tarea.',
            50,
            'imagen-gato_gafas'
        );

        $this->createLogro(
            $manager,
            'Maestro Constructor',
            'Completar 5 tareas.',
            150,
            'imagen-ironman'
        );

        $this->createLogro(
            $manager,
            'Torre de Babel',
            'Completar una tarea con más de 500 palabras/muchísimo contenido.',
            200,
            'imagen-sherlock'
        );

        $this->createLogro(
            $manager,
            'Crono-Maestro',
            'Entregar una tarea antes de la fecha límite.',
            100,
            'imagen-cortana'
        );

        // Logros de Niveles
        $this->createLogro(
            $manager,
            'Nivel 1 – Aventurero',
            'Alcanzar el nivel 2 de experiencia.',
            100,
            'imagen-link'
        );

        $this->createLogro(
            $manager,
            'Nivel 5 – Guerrero',
            'Alcanzar el nivel 6 de experiencia.',
            300,
            'imagen-ssj2'
        );

        $this->createLogro(
            $manager,
            'Nivel 10 – Sabio Místico',
            'Alcanzar el nivel 7 de experiencia.',
            500,
            'imagen-ssj3'
        );

        $this->createLogro(
            $manager,
            'Ascensión Divina',
            'Llegar al nivel máximo (nivel 8).',
            1000,
            'imagen-gato_sabio'
        );

        // Original logro de prueba
        $this->createLogro(
            $manager,
            'Primer Logro',
            'Este es el primer logro de prueba',
            100,
            'imagen-default',
            self::LOGRO_REFERENCE
        );

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ImagenFixtures::class
        ];
    }
}