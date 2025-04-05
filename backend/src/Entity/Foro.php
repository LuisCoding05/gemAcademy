<?php

namespace App\Entity;

use App\Repository\ForoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForoRepository::class)]
class Foro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaCreacion = null;

    /**
     * @var Collection<int, MensajeForo>
     */
    #[ORM\OneToMany(targetEntity: MensajeForo::class, mappedBy: 'idForo')]
    private Collection $mensajeForos;

    #[ORM\ManyToOne(inversedBy: 'foros')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $curso = null;

    public function __construct()
    {
        $this->mensajeForos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

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

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * @return Collection<int, MensajeForo>
     */
    public function getMensajeForos(): Collection
    {
        return $this->mensajeForos;
    }

    public function addMensajeForo(MensajeForo $mensajeForo): static
    {
        if (!$this->mensajeForos->contains($mensajeForo)) {
            $this->mensajeForos->add($mensajeForo);
            $mensajeForo->setIdForo($this);
        }

        return $this;
    }

    public function removeMensajeForo(MensajeForo $mensajeForo): static
    {
        if ($this->mensajeForos->removeElement($mensajeForo)) {
            // set the owning side to null (unless already changed)
            if ($mensajeForo->getIdForo() === $this) {
                $mensajeForo->setIdForo(null);
            }
        }

        return $this;
    }

    public function getCurso(): ?Curso
    {
        return $this->curso;
    }

    public function setCurso(?Curso $curso): static
    {
        $this->curso = $curso;

        return $this;
    }
}
