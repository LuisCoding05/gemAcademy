<?php

namespace App\DataFixtures;

use App\Entity\Imagen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImagenFixtures extends Fixture
{
    public const DEFAULT_IMAGE_REFERENCE = 'default-image';

    public function load(ObjectManager $manager): void
    {
        $imagen = new Imagen();
        $imagen->setUrl('https://example.com/default-course.jpg');
        $manager->persist($imagen);
        $this->addReference(self::DEFAULT_IMAGE_REFERENCE, $imagen);

        $manager->flush();
    }
}
