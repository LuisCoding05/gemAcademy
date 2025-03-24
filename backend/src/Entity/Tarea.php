<?php

namespace App\Entity;

use App\Repository\TareaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TareaRepository::class)]
class Tarea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $archivoUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaPublicacion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaLimite = null;

    #[ORM\Column(options: ['default' => 100])]
    private ?int $puntosMaximos = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $esObligatoria = null;

    /**
     * @var Collection<int, EntregaTarea>
     */
    #[ORM\OneToMany(targetEntity: EntregaTarea::class, mappedBy: 'idTarea')]
    private Collection $entregaTareas;

    #[ORM\ManyToOne(inversedBy: 'tareas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $idCurso = null;

    public function __construct()
    {
        $this->entregaTareas = new ArrayCollection();
        $this->fechaPublicacion = new \DateTime();
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

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getArchivoUrl(): ?string
    {
        return $this->archivoUrl;
    }

    public function setArchivoUrl(?string $archivoUrl): static
    {
        $this->archivoUrl = $archivoUrl;

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

    public function getFechaLimite(): ?\DateTimeInterface
    {
        return $this->fechaLimite;
    }

    public function setFechaLimite(\DateTimeInterface $fechaLimite): static
    {
        $this->fechaLimite = $fechaLimite;

        return $this;
    }

    public function getPuntosMaximos(): ?int
    {
        return $this->puntosMaximos;
    }

    public function setPuntosMaximos(int $puntosMaximos): static
    {
        $this->puntosMaximos = $puntosMaximos;

        return $this;
    }

    public function isEsObligatoria(): ?bool
    {
        return $this->esObligatoria;
    }

    public function setEsObligatoria(bool $esObligatoria): static
    {
        $this->esObligatoria = $esObligatoria;

        return $this;
    }

    /**
     * @return Collection<int, EntregaTarea>
     */
    public function getEntregaTareas(): Collection
    {
        return $this->entregaTareas;
    }

    public function addEntregaTarea(EntregaTarea $entregaTarea): static
    {
        if (!$this->entregaTareas->contains($entregaTarea)) {
            $this->entregaTareas->add($entregaTarea);
            $entregaTarea->setIdTarea($this);
        }

        return $this;
    }

    public function removeEntregaTarea(EntregaTarea $entregaTarea): static
    {
        if ($this->entregaTareas->removeElement($entregaTarea)) {
            // set the owning side to null (unless already changed)
            if ($entregaTarea->getIdTarea() === $this) {
                $entregaTarea->setIdTarea(null);
            }
        }

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
}
