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
        $logro->setTitulo('Primer Logro')
            ->setMotivo('Este es el primer logro de prueba')
            ->setPuntosOtorgados(100)
            ->setImagen($this->getReference('imagen-default', Imagen::class));
        
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