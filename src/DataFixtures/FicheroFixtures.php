<?php

namespace App\DataFixtures;

use App\Entity\Fichero;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FicheroFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $profesor = $manager->getRepository(Usuario::class)->findOneBy(['email' => 'profesor1@gemacademy.com']);
        $estudiante = $manager->getRepository(Usuario::class)->findOneBy(['email' => 'estudiante1@gemacademy.com']);

        // Crear ficheros de ejemplo para material del curso
        $ficheroMaterial1 = new Fichero();
        $ficheroMaterial1->setNombreOriginal('guia_introduccion.pdf');
        $ficheroMaterial1->setRuta('/uploads/cursos/guia_introduccion.pdf');
        $ficheroMaterial1->setMimeType('application/pdf');
        $ficheroMaterial1->setFechaSubida(new \DateTime());
        $ficheroMaterial1->setTamanio(1024 * 1024); // 1MB
        $ficheroMaterial1->setUsuario($profesor);
        $manager->persist($ficheroMaterial1);
        $this->addReference('fichero-material-1', $ficheroMaterial1);

        $ficheroMaterial2 = new Fichero();
        $ficheroMaterial2->setNombreOriginal('presentacion_tema1.pptx');
        $ficheroMaterial2->setRuta('/uploads/cursos/presentacion_tema1.pptx');
        $ficheroMaterial2->setMimeType('application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $ficheroMaterial2->setFechaSubida(new \DateTime());
        $ficheroMaterial2->setTamanio(2 * 1024 * 1024); // 2MB
        $ficheroMaterial2->setUsuario($profesor);
        $manager->persist($ficheroMaterial2);
        $this->addReference('fichero-material-2', $ficheroMaterial2);

        // Crear ficheros de ejemplo para tareas
        $ficheroTarea1 = new Fichero();
        $ficheroTarea1->setNombreOriginal('instrucciones_tarea1.pdf');
        $ficheroTarea1->setRuta('/uploads/cursos/instrucciones_tarea1.pdf');
        $ficheroTarea1->setMimeType('application/pdf');
        $ficheroTarea1->setFechaSubida(new \DateTime());
        $ficheroTarea1->setTamanio(512 * 1024); // 512KB
        $ficheroTarea1->setUsuario($profesor);
        $manager->persist($ficheroTarea1);
        $this->addReference('fichero-tarea-1', $ficheroTarea1);

        // Crear ficheros de ejemplo para entregas de tareas
        $ficheroEntrega1 = new Fichero();
        $ficheroEntrega1->setNombreOriginal('entrega_tarea1_estudiante1.pdf');
        $ficheroEntrega1->setRuta('/uploads/cursos/entrega_tarea1_estudiante1.pdf');
        $ficheroEntrega1->setMimeType('application/pdf');
        $ficheroEntrega1->setFechaSubida(new \DateTime());
        $ficheroEntrega1->setTamanio(1.5 * 1024 * 1024); // 1.5MB
        $ficheroEntrega1->setUsuario($estudiante);
        $manager->persist($ficheroEntrega1);
        $this->addReference('fichero-entrega-1', $ficheroEntrega1);

        $ficheroEntrega2 = new Fichero();
        $ficheroEntrega2->setNombreOriginal('entrega_tarea2_estudiante1.docx');
        $ficheroEntrega2->setRuta('/uploads/cursos/entrega_tarea2_estudiante1.docx');
        $ficheroEntrega2->setMimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $ficheroEntrega2->setFechaSubida(new \DateTime());
        $ficheroEntrega2->setTamanio(750 * 1024); // 750KB
        $ficheroEntrega2->setUsuario($estudiante);
        $manager->persist($ficheroEntrega2);
        $this->addReference('fichero-entrega-2', $ficheroEntrega2);

        $manager->flush();
    }

    public function getDependencies():array
    {
        return [
            UsuarioFixtures::class,
        ];
    }
}