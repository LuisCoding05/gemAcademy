<?php

namespace App\Entity;

use App\Repository\MaterialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterialRepository::class)]
class Material
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
    private ?\DateTimeInterface $fechaPublicacion = null;

    #[ORM\Column(nullable: true)]
    private ?int $orden = null;

    #[ORM\ManyToOne(inversedBy: 'materials')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $idCurso = null;

    /**
     * @var Collection<int, MaterialCompletado>
     */
    #[ORM\OneToMany(targetEntity: MaterialCompletado::class, mappedBy: 'material')]
    private Collection $materialCompletados;

    #[ORM\OneToOne(mappedBy: 'material', cascade: ['persist', 'remove'])]
    private ?Fichero $fichero = null;

    public function __construct()
    {
        $this->materialCompletados = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }


    public function getFechaPublicacion(): ?\DateTimeInterface
    {
        return $this->fechaPublicacion;
    }

    public function setFechaPublicacion(\DateTimeInterface $fechaPublicacion): static
    {
        $this->fechaPublicacion = $fechaPublicacion;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): static
    {
        $this->orden = $orden;

        return $this;
    }

    public function getIdCurso(): ?Curso
    {
        return $this->idCurso;
    }

    public function setIdCurso(?Curso $idCurso): static
    {
        $this->idCurso = $idCurso;

        return $this;
    }

    /**
     * @return Collection<int, MaterialCompletado>
     */
    public function getMaterialCompletados(): Collection
    {
        return $this->materialCompletados;
    }

    public function addMaterialCompletado(MaterialCompletado $materialCompletado): static
    {
        if (!$this->materialCompletados->contains($materialCompletado)) {
            $this->materialCompletados->add($materialCompletado);
            $materialCompletado->setMaterial($this);
        }

        return $this;
    }

    public function removeMaterialCompletado(MaterialCompletado $materialCompletado): static
    {
        if ($this->materialCompletados->removeElement($materialCompletado)) {
            // set the owning side to null (unless already changed)
            if ($materialCompletado->getMaterial() === $this) {
                $materialCompletado->setMaterial(null);
            }
        }

        return $this;
    }

    public function getFichero(): ?Fichero
    {
        return $this->fichero;
    }

    public function setFichero(?Fichero $fichero): static
    {
        // unset the owning side of the relation if necessary
        if ($fichero === null && $this->fichero !== null) {
            $this->fichero->setMaterial(null);
        }

        // set the owning side of the relation if necessary
        if ($fichero !== null && $fichero->getMaterial() !== $this) {
            $fichero->setMaterial($this);
        }

        $this->fichero = $fichero;

        return $this;
    }
}
