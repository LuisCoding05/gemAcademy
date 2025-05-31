<?php

namespace App\Entity;

use App\Repository\UsuarioNivelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioNivelRepository::class)]
class UsuarioNivel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'usuarioNivels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $idUsuario = null;

    #[ORM\ManyToOne(inversedBy: 'usuarioNivels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Nivel $idNivel = null;

    #[ORM\Column]
    private ?int $puntosSiguienteNivel = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $puntosActuales = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaUltimoNivel = null;

    public function __construct(){
        $this->fechaUltimoNivel = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUsuario(): ?Usuario
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(?Usuario $idUsuario): static
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    public function getIdNivel(): ?Nivel
    {
        return $this->idNivel;
    }

    public function setIdNivel(?Nivel $idNivel): static
    {
        $this->idNivel = $idNivel;

        return $this;
    }

    public function getPuntosSiguienteNivel(): ?int
    {
        return $this->puntosSiguienteNivel;
    }

    public function setPuntosSiguienteNivel(int $puntosSiguienteNivel): static
    {
        $this->puntosSiguienteNivel = $puntosSiguienteNivel;

        return $this;
    }

    public function getPuntosActuales(): ?int
    {
        return $this->puntosActuales;
    }

    public function setPuntosActuales(int $puntosActuales): static
    {
        $this->puntosActuales = $puntosActuales;

        return $this;
    }

    public function getFechaUltimoNivel(): ?\DateTimeInterface
    {
        return $this->fechaUltimoNivel;
    }

    public function setFechaUltimoNivel(\DateTimeInterface $fechaUltimoNivel): static
    {
        $this->fechaUltimoNivel = $fechaUltimoNivel;

        return $this;
    }
}
