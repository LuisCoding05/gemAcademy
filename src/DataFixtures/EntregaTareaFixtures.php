<?php

namespace App\DataFixtures;

use App\Entity\EntregaTarea;
use App\Entity\Tarea;
use App\Entity\UsuarioCurso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EntregaTareaFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $entregaTarea = new EntregaTarea();
        $entregaTarea->setIdTarea($this->getReference('tarea-introducción-a-la-programación-1', Tarea::class))
            ->setUsuarioCurso($this->getReference('usuario-curso-estudiante1', UsuarioCurso::class))
            ->setFechaEntrega(new \DateTime())
            ->setComentarioProfesor('Tarea completada con éxito')
            ->setEstado(EntregaTarea::ESTADO_ENTREGADO);
        
        $manager->persist($entregaTarea);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TareaFixtures::class,
            UsuarioCursoFixtures::class
        ];
    }
}
