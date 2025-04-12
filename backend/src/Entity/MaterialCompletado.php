<?php

namespace App\Entity;

use App\Repository\MaterialCompletadoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterialCompletadoRepository::class)]
class MaterialCompletado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'materialCompletados')]
    private ?UsuarioCurso $usuarioCurso = null;

    #[ORM\ManyToOne(inversedBy: 'materialCompletados')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Material $material = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaCompletado = null;
    
    public function __construct()
    {
        $this->fechaCompletado = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuarioCurso(): ?UsuarioCurso
    {
        return $this->usuarioCurso;
    }

    public function setUsuarioCurso(?UsuarioCurso $usuarioCurso): static
    {
        $this->usuarioCurso = $usuarioCurso;

        return $this;
    }

    public function getMaterial(): ?Material
    {
        return $this->material;
    }

    public function setMaterial(?Material $material): static
    {
        $this->material = $material;

        return $this;
    }

    public function getFechaCompletado(): ?\DateTimeInterface
    {
        return $this->fechaCompletado;
    }

    public function setFechaCompletado(\DateTimeInterface $fechaCompletado): static
    {
        $this->fechaCompletado = $fechaCompletado;

        return $this;
    }
}
