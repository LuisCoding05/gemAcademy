<?php

namespace App\DataFixtures;

use App\Entity\Imagen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImagenFixtures extends Fixture
{
    public const DEFAULT_IMAGE_REFERENCE = 'default-image';
    public const IMAGES = [
        'default' => 'default.webp',
        'light' => 'lightyagami.webp',
        'gojo' => 'gojo.jpg',
        'toji' => 'toji.jpg',
        'gohan' => 'gohanGafas.jpg',
        'ssj1' => 'ssj1.jpg',
        'ssj2' => 'ssj2.jpg',
        'ssj3' => 'ssj3.webp',
        'mui' => 'mui.jpg',
        'jake' => 'jake.jpg',
        'gato_sabio' => 'gatosabiduria.jpg',
        'gato_gafas' => 'gatogafas1.jpg',
        'sherlock' => 'shrelockholmes.jpg',
        'ironman' => 'ironman.jpg',
        'dexter' => 'dexter.png',
        'mclovin' => 'mclovin.jpg',
        'cortana' => 'cortana.jpg',
        'ui' => 'ui.jpg',
        'gojo_pfp' => 'gojopfp.jpg',
        'sunjin' => 'sungJinWoo.jpg',
        'estudios' => 'estudios.png',
        'tecnologia' => 'tecnologia.jpg',
        'rana' => 'ranapepe.webp',
        'reina' => 'reinaroja.jpeg',
        'shinobu' => 'shinobu.jpg',
        'L' => 'L.webp'
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::IMAGES as $reference => $filename) {
            $imagen = new Imagen();
            $imagen->setUrl('./images/pfpgemacademy/' . $filename);
            $manager->persist($imagen);
            $this->addReference('imagen-' . $reference, $imagen);
        }

        $manager->flush();
    }
}
