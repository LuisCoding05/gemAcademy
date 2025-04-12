<?php

namespace App\DataFixtures;

use App\Entity\Imagen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImagenFixtures extends Fixture
{
    public const DEFAULT_IMAGE_REFERENCE = 'default-image';
    public const IMAGES = [
        'default' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp',
        'light' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/lightyagami_offe2o.webp',
        'gojo' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/gojo_n1kbpl.jpg',
        'toji' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483547/toji_au7ia1.jpg',
        'gohan' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/gohanGafas_unrsfz.jpg',
        'ssj1' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483546/ssj1_gcxiev.jpg',
        'ssj2' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483546/ssj2_h3pgz0.jpg',
        'ssj3' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483546/ssj3_p8qd4l.webp',
        'mui' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/mui_gzw7gj.jpg',
        'jake' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/jake_f4pvss.jpg',
        'gato_sabio' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/gatosabiduria_wdfmxn.jpg',
        'gato_gafas' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/gatogafas1_oknork.jpg',
        'sherlock' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483546/shrelockholmes_zudpar.jpg',
        'ironman' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/ironman_wz7fch.jpg',
        'dexter' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/dexter_padxbb.png',
        'mclovin' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/mclovin_h2rldf.jpg',
        'cortana' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/cortana_sscjh0.jpg',
        'ui' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/ui_tnnjtr.jpg',
        'gojo_pfp' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/gojopfp_sxdxgi.jpg',
        'sunjin' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483547/sungJinWoo_lhdpyo.jpg',
        'estudios' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/estudios_bpouyw.png',
        'tecnologia' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483547/tecnologia_zuuows.jpg',
        'rana' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/ranapepe_dxk89v.webp',
        'reina' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/reinaroja_n2ql4x.jpg',
        'shinobu' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483546/shinobu_vfsp6x.jpg',
        'L' => 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483545/L_pxgnzj.webp'
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::IMAGES as $reference => $url) {
            $imagen = new Imagen();
            $imagen->setUrl($url);
            $manager->persist($imagen);
            $this->addReference('imagen-' . $reference, $imagen);
        }

        $manager->flush();
    }
}
