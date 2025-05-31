<?php

namespace App\DataFixtures;

use App\Entity\Nivel;
use App\Entity\Usuario;
use App\Entity\UsuarioNivel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UsuarioNivelFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $usuarios = [
            'estudiante1', 'estudiante2', 'estudiante3', 'estudiante4', 'estudiante5',
            'estudiante6', 'estudiante7', 'estudiante8', 'estudiante9', 'estudiante10',
            'profesor1', 'profesor2', 'admin'
        ];

        foreach ($usuarios as $username) {
            $usuarioNivel = new UsuarioNivel();
            $usuarioNivel->setIdUsuario($this->getReference('usuario-' . $username, Usuario::class))
                ->setIdNivel($this->getReference('nivel-inicial', Nivel::class))
                ->setPuntosSiguienteNivel(100) // Puntos necesarios para el nivel 2 (Explorador)
                ->setPuntosActuales(0);
            
            $manager->persist($usuarioNivel);
        }
        
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            NivelFixtures::class
        ];
    }
}
