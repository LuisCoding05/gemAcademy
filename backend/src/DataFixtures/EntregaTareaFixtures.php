<?php

namespace App\DataFixtures;

use App\Entity\EntregaTarea;
use App\Entity\Tarea;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EntregaTareaFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $entrega = new EntregaTarea();
        $entrega->setArchivoUrl('/uploads/tarea1.pdf')
            ->setFechaEntrega(new \DateTime())
            ->setCalificacion('8.50')
            ->setPuntosObtenidos(85)
            ->setIdUsuario($this->getReference(UsuarioFixtures::STUDENT_USER_REFERENCE, Usuario::class));
        
        $entrega->setIdTarea($this->getReference(TareaFixtures::TAREA_REFERENCE, Tarea::class));
        
        $manager->persist($entrega);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            TareaFixtures::class
        ];
    }
}
