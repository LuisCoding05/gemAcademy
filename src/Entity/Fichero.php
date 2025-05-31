<?php

namespace App\Entity;

use App\Repository\FicheroRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FicheroRepository::class)]
class Fichero
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombreOriginal = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta = null;

    #[ORM\Column(length: 100)]
    private ?string $mimeType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaSubida = null;

    #[ORM\ManyToOne(inversedBy: 'ficheros')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\OneToOne(mappedBy: 'fichero', cascade: ['persist'])]
    private ?Material $material = null;

    #[ORM\OneToOne(mappedBy: 'fichero', cascade: ['persist'])]
    private ?EntregaTarea $entregaTarea = null;

    #[ORM\Column]
    private ?int $tamanio = null;

    #[ORM\OneToOne(mappedBy: 'fichero', cascade: ['persist'])]
    private ?Tarea $tarea = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreOriginal(): ?string
    {
        return $this->nombreOriginal;
    }

    public function setNombreOriginal(string $nombreOriginal): static
    {
        $this->nombreOriginal = $nombreOriginal;

        return $this;
    }

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(string $ruta): static
    {
        $this->ruta = $ruta;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFechaSubida(): ?\DateTimeInterface
    {
        return $this->fechaSubida;
    }

    public function setFechaSubida(\DateTimeInterface $fechaSubida): static
    {
        $this->fechaSubida = $fechaSubida;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

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

    public function getEntregaTarea(): ?EntregaTarea
    {
        return $this->entregaTarea;
    }

    public function setEntregaTarea(?EntregaTarea $entregaTarea): static
    {
        $this->entregaTarea = $entregaTarea;

        return $this;
    }

    public function getTamanio(): ?int
    {
        return $this->tamanio;
    }

    public function setTamanio(int $tamanio): static
    {
        $this->tamanio = $tamanio;

        return $this;
    }

    public function getTarea(): ?Tarea
    {
        return $this->tarea;
    }

    public function setTarea(?Tarea $tarea): static
    {
        $this->tarea = $tarea;

        return $this;
    }
}
