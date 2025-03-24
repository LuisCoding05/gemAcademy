<?php

namespace App\Entity;

use App\Repository\CursoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursoRepository::class)]
class Curso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\ManyToOne(inversedBy: 'cursos')]
    private ?Imagen $imagen = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaCreacion = null;

    /**
     * @var Collection<int, UsuarioCurso>
     */
    #[ORM\OneToMany(targetEntity: UsuarioCurso::class, mappedBy: 'idCurso')]
    private Collection $usuarioCursos;

    /**
     * @var Collection<int, Tarea>
     */
    #[ORM\OneToMany(targetEntity: Tarea::class, mappedBy: 'idCurso')]
    private Collection $tareas;

    #[ORM\ManyToOne(inversedBy: 'cursos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $profesor = null;

    public function __construct()
    {
        $this->usuarioCursos = new ArrayCollection();
        $this->tareas = new ArrayCollection();
        $this->fechaCreacion = new \DateTime();
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getImagen(): ?Imagen
    {
        return $this->imagen;
    }

    public function setImagen(?Imagen $imagen): static
    {
        $this->imagen = $imagen;

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
     * @return Collection<int, UsuarioCurso>
     */
    public function getUsuarioCursos(): Collection
    {
        return $this->usuarioCursos;
    }

    public function addUsuarioCurso(UsuarioCurso $usuarioCurso): static
    {
        if (!$this->usuarioCursos->contains($usuarioCurso)) {
            $this->usuarioCursos->add($usuarioCurso);
            $usuarioCurso->setIdCurso($this);
        }

        return $this;
    }

    public function removeUsuarioCurso(UsuarioCurso $usuarioCurso): static
    {
        if ($this->usuarioCursos->removeElement($usuarioCurso)) {
            // set the owning side to null (unless already changed)
            if ($usuarioCurso->getIdCurso() === $this) {
                $usuarioCurso->setIdCurso(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tarea>
     */
    public function getTareas(): Collection
    {
        return $this->tareas;
    }

    public function addTarea(Tarea $tarea): static
    {
        if (!$this->tareas->contains($tarea)) {
            $this->tareas->add($tarea);
            $tarea->setIdCurso($this);
        }

        return $this;
    }

    public function removeTarea(Tarea $tarea): static
    {
        if ($this->tareas->removeElement($tarea)) {
            // set the owning side to null (unless already changed)
            if ($tarea->getIdCurso() === $this) {
                $tarea->setIdCurso(null);
            }
        }

        return $this;
    }

    public function getProfesor(): ?Usuario
    {
        return $this->profesor;
    }

    public function setProfesor(?Usuario $profesor): static
    {
        $this->profesor = $profesor;

        return $this;
    }
}
