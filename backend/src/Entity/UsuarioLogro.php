<?php

namespace App\Entity;

use App\Repository\UsuarioLogroRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioLogroRepository::class)]
class UsuarioLogro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'usuarioLogros')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $idUsuario = null;

    #[ORM\ManyToOne(inversedBy: 'usuarioLogros')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Logro $idLogro = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaObtencion = null;

    public function __construct()
    {
        $this->fechaObtencion = new \DateTime();
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

    public function getIdLogro(): ?Logro
    {
        return $this->idLogro;
    }

    public function setIdLogro(?Logro $idLogro): static
    {
        $this->idLogro = $idLogro;

        return $this;
    }

    public function getFechaObtencion(): ?\DateTimeInterface
    {
        return $this->fechaObtencion;
    }

    public function setFechaObtencion(\DateTimeInterface $fechaObtencion): static
    {
        $this->fechaObtencion = $fechaObtencion;

        return $this;
    }
}
