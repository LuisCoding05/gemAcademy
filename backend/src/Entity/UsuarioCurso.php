<?php

namespace App\Entity;

use App\Repository\UsuarioCursoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioCursoRepository::class)]
class UsuarioCurso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'usuarioCursos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $idUsuario = null;

    #[ORM\ManyToOne(inversedBy: 'usuarioCursos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $idCurso = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaInscripcion = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $materialesCompletados = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $tareasCompletadas = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $quizzesCompletados = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => 0])]
    private ?string $porcentajeCompletado = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $ultimaActualizacion = null;

    /**
     * @var Collection<int, MaterialCompletado>
     */
    #[ORM\OneToMany(targetEntity: MaterialCompletado::class, mappedBy: 'usuarioCurso')]
    private Collection $materialCompletados;

    /**
     * @var Collection<int, EntregaTarea>
     */
    #[ORM\OneToMany(targetEntity: EntregaTarea::class, mappedBy: 'usuarioCurso')]
    private Collection $entregaTareas;

    public function __construct()
    {
        $this->fechaInscripcion = new \DateTime();
        $this->materialCompletados = new ArrayCollection();
        $this->entregaTareas = new ArrayCollection();
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

    public function getIdCurso(): ?Curso
    {
        return $this->idCurso;
    }

    public function setIdCurso(?Curso $idCurso): static
    {
        $this->idCurso = $idCurso;

        return $this;
    }

    public function getFechaInscripcion(): ?\DateTimeInterface
    {
        return $this->fechaInscripcion;
    }

    public function setFechaInscripcion(\DateTimeInterface $fechaInscripcion): static
    {
        $this->fechaInscripcion = $fechaInscripcion;

        return $this;
    }

    public function getMaterialesCompletados(): ?int
    {
        return $this->materialesCompletados;
    }

    public function setMaterialesCompletados(int $materialesCompletados): static
    {
        $this->materialesCompletados = $materialesCompletados;

        return $this;
    }

    public function getTareasCompletadas(): ?int
    {
        return $this->tareasCompletadas;
    }

    public function setTareasCompletadas(int $tareasCompletadas): static
    {
        $this->tareasCompletadas = $tareasCompletadas;

        return $this;
    }


    public function getQuizzesCompletados(): ?int
    {
        return $this->quizzesCompletados;
    }

    public function setQuizzesCompletados(int $quizzesCompletados): static
    {
        $this->quizzesCompletados = $quizzesCompletados;

        return $this;
    }

    public function getPorcentajeCompletado(): ?string
    {
        return $this->porcentajeCompletado;
    }

    public function setPorcentajeCompletado(string $porcentajeCompletado): static
    {
        $this->porcentajeCompletado = $porcentajeCompletado;

        return $this;
    }

    public function getUltimaActualizacion(): ?\DateTimeInterface
    {
        return $this->ultimaActualizacion;
    }

    public function setUltimaActualizacion(\DateTimeInterface $ultimaActualizacion): static
    {
        $this->ultimaActualizacion = $ultimaActualizacion;

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
            $materialCompletado->setUsuarioCurso($this);
        }

        return $this;
    }

    public function removeMaterialCompletado(MaterialCompletado $materialCompletado): static
    {
        if ($this->materialCompletados->removeElement($materialCompletado)) {
            // set the owning side to null (unless already changed)
            if ($materialCompletado->getUsuarioCurso() === $this) {
                $materialCompletado->setUsuarioCurso(null);
            }
        }

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
            $entregaTarea->setUsuarioCurso($this);
        }

        return $this;
    }

    public function removeEntregaTarea(EntregaTarea $entregaTarea): static
    {
        if ($this->entregaTareas->removeElement($entregaTarea)) {
            // set the owning side to null (unless already changed)
            if ($entregaTarea->getUsuarioCurso() === $this) {
                $entregaTarea->setUsuarioCurso(null);
            }
        }

        return $this;
    }
}
