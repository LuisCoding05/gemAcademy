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
    public function load(ObjectManager $manager): void
    {
        $logro = new Logro();
        $logro->setTitulo('Primer logro')
            ->setMotivo('Completar el primer curso')
            ->setPuntosOtorgados(100)
            ->setImagen($this->getReference(ImagenFixtures::DEFAULT_IMAGE_REFERENCE, Imagen::class));
        
        $manager->persist($logro);
        $this->addReference(self::LOGRO_REFERENCE, $logro);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ImagenFixtures::class
        ];
    }
}