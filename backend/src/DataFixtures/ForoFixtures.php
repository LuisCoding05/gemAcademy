<?php

namespace App\DataFixtures;

use App\Entity\Foro;
use App\Entity\Curso;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ForoFixtures extends Fixture implements DependentFixtureInterface
{
    public const FORO_REFERENCE = 'foro-general';

    public function load(ObjectManager $manager): void
    {
        $cursos = [
            'introducción-a-la-programación',
            'desarrollo-web-con-react',
            'backend-con-symfony',
            'bases-de-datos-sql',
            'desarrollo-de-videojuegos-con-unity',
            'inteligencia-artificial-básica',
            'seguridad-informática',
            'diseño-de-interfaces-de-usuario',
            'programación-móvil-con-flutter',
            'devops-y-ci/cd'
        ];

        foreach ($cursos as $cursoSlug) {
            $foro = new Foro();
            $foro->setTitulo('Foro del curso ' . ucwords(str_replace('-', ' ', $cursoSlug)))
                ->setDescripcion('Espacio de discusión para el curso')
                ->setFechaCreacion(new \DateTime())
                ->setCurso($this->getReference('curso-' . $cursoSlug, Curso::class));

            $manager->persist($foro);

            // Si es el foro del primer curso, lo guardamos como referencia para los mensajes
            if ($cursoSlug === 'introducción-a-la-programación') {
                $this->addReference(self::FORO_REFERENCE, $foro);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CursoFixtures::class
        ];
    }
}