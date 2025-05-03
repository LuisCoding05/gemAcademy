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

    /**
     * @var Collection<int, Foro>
     */
    #[ORM\OneToMany(targetEntity: Foro::class, mappedBy: 'curso')]
    private Collection $foros;

    /**
     * @var Collection<int, Material>
     */
    #[ORM\OneToMany(targetEntity: Material::class, mappedBy: 'idCurso')]
    private Collection $materials;

    /**
     * @var Collection<int, Quizz>
     */
    #[ORM\OneToMany(targetEntity: Quizz::class, mappedBy: 'idCurso')]
    private Collection $quizzs;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descripcion = null;

    public function __construct()
    {
        $this->usuarioCursos = new ArrayCollection();
        $this->tareas = new ArrayCollection();
        $this->fechaCreacion = new \DateTime();
        $this->foros = new ArrayCollection();
        $this->materials = new ArrayCollection();
        $this->quizzs = new ArrayCollection();
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

    /**
     * @return Collection<int, Foro>
     */
    public function getForos(): Collection
    {
        return $this->foros;
    }

    public function addForo(Foro $foro): static
    {
        if (!$this->foros->contains($foro)) {
            $this->foros->add($foro);
            $foro->setCurso($this);
        }

        return $this;
    }

    public function removeForo(Foro $foro): static
    {
        if ($this->foros->removeElement($foro)) {
            // set the owning side to null (unless already changed)
            if ($foro->getCurso() === $this) {
                $foro->setCurso(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Material>
     */
    public function getMaterials(): Collection
    {
        return $this->materials;
    }

    public function addMaterial(Material $material): static
    {
        if (!$this->materials->contains($material)) {
            $this->materials->add($material);
            $material->setIdCurso($this);
        }

        return $this;
    }

    public function removeMaterial(Material $material): static
    {
        if ($this->materials->removeElement($material)) {
            // set the owning side to null (unless already changed)
            if ($material->getIdCurso() === $this) {
                $material->setIdCurso(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Quizz>
     */
    public function getQuizzs(): Collection
    {
        return $this->quizzs;
    }

    public function addQuizz(Quizz $quizz): static
    {
        if (!$this->quizzs->contains($quizz)) {
            $this->quizzs->add($quizz);
            $quizz->setIdCurso($this);
        }

        return $this;
    }

    public function removeQuizz(Quizz $quizz): static
    {
        if ($this->quizzs->removeElement($quizz)) {
            // set the owning side to null (unless already changed)
            if ($quizz->getIdCurso() === $this) {
                $quizz->setIdCurso(null);
            }
        }

        return $this;
    }

    public function getTotalItems(): int
    {
        return $this->materials->count() + $this->tareas->count() + $this->quizzs->count();
    }

    public function getTotalMateriales(): int
    {
        return $this->materials->count();
    }

    public function getTotalTareas(): int
    {
        return $this->tareas->count();
    }

    public function getTotalQuizzes(): int
    {
        return $this->quizzs->count();
    }
}
