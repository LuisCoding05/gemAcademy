<?php

namespace App\Entity;

use App\Repository\TipoArchivoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TipoArchivoRepository::class)]
class TipoArchivo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $extension = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;
    #[ORM\Column(type: 'smallint')]
    private ?int $permitidoMaterial = 0;

    #[ORM\Column(type: 'smallint')]
    private ?int $permitidoTarea = 0;

    #[ORM\Column]
    private ?int $maxTamanoMb = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }    public function isPermitidoMaterial(): ?bool
    {
        return $this->permitidoMaterial === 1;
    }

    public function setPermitidoMaterial(bool $permitidoMaterial): static
    {
        $this->permitidoMaterial = $permitidoMaterial ? 1 : 0;

        return $this;
    }

    public function isPermitidoTarea(): ?bool
    {
        return $this->permitidoTarea === 1;
    }

    public function setPermitidoTarea(bool $permitidoTarea): static
    {
        $this->permitidoTarea = $permitidoTarea ? 1 : 0;

        return $this;
    }

    public function getMaxTamanoMb(): ?int
    {
        return $this->maxTamanoMb;
    }

    public function setMaxTamanoMb(int $maxTamanoMb): static
    {
        $this->maxTamanoMb = $maxTamanoMb;

        return $this;
    }
}
