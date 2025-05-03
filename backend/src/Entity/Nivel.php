<?php

namespace App\Entity;

use App\Repository\NivelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NivelRepository::class)]
class Nivel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $puntosRequeridos = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    /**
     * @var Collection<int, UsuarioNivel>
     */
    #[ORM\OneToMany(targetEntity: UsuarioNivel::class, mappedBy: 'idNivel')]
    private Collection $usuarioNivels;

    #[ORM\Column]
    private ?int $numNivel = null;

    public function __construct()
    {
        $this->usuarioNivels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getPuntosRequeridos(): ?int
    {
        return $this->puntosRequeridos;
    }

    public function setPuntosRequeridos(int $puntosRequeridos): static
    {
        $this->puntosRequeridos = $puntosRequeridos;

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
    }

    /**
     * @return Collection<int, UsuarioNivel>
     */
    public function getUsuarioNivels(): Collection
    {
        return $this->usuarioNivels;
    }

    public function addUsuarioNivel(UsuarioNivel $usuarioNivel): static
    {
        if (!$this->usuarioNivels->contains($usuarioNivel)) {
            $this->usuarioNivels->add($usuarioNivel);
            $usuarioNivel->setIdNivel($this);
        }

        return $this;
    }

    public function removeUsuarioNivel(UsuarioNivel $usuarioNivel): static
    {
        if ($this->usuarioNivels->removeElement($usuarioNivel)) {
            // set the owning side to null (unless already changed)
            if ($usuarioNivel->getIdNivel() === $this) {
                $usuarioNivel->setIdNivel(null);
            }
        }

        return $this;
    }

    public function getNumNivel(): ?int
    {
        return $this->numNivel;
    }

    public function setNumNivel(int $numNivel): static
    {
        $this->numNivel = $numNivel;

        return $this;
    }
}
