<?php

namespace App\Entity;

use App\Repository\QuizzRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizzRepository::class)]
class Quizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaPublicacion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaLimite = null;

    #[ORM\Column(options: ['default' => 30])]
    private ?int $tiempoLimite = null;

    #[ORM\Column]
    private ?int $puntosTotales = null;

    #[ORM\Column(options: ['default' => 1], nullable:true)]
    private ?int $intentosPermitidos = 1;

    /**
     * @var Collection<int, PreguntaQuizz>
     */
    #[ORM\OneToMany(targetEntity: PreguntaQuizz::class, mappedBy: 'idQuizz')]
    private Collection $preguntaQuizzs;

    /**
     * @var Collection<int, IntentoQuizz>
     */
    #[ORM\OneToMany(targetEntity: IntentoQuizz::class, mappedBy: 'idQuizz')]
    private Collection $intentoQuizzs;

    #[ORM\ManyToOne(inversedBy: 'quizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $idCurso = null;

    public function __construct()
    {
        $this->preguntaQuizzs = new ArrayCollection();
        $this->intentoQuizzs = new ArrayCollection();
        $this->fechaPublicacion  = new DateTime();
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

    public function getTiempoLimite(): ?int
    {
        return $this->tiempoLimite;
    }

    public function setTiempoLimite(int $tiempoLimite): static
    {
        $this->tiempoLimite = $tiempoLimite;

        return $this;
    }

    public function getPuntosTotales(): ?int
    {
        return $this->puntosTotales;
    }

    public function setPuntosTotales(int $puntosTotales): static
    {
        $this->puntosTotales = $puntosTotales;

        return $this;
    }

    public function getIntentosPermitidos(): ?int
    {
        return $this->intentosPermitidos;
    }

    public function setIntentosPermitidos(int $intentosPermitidos): static
    {
        $this->intentosPermitidos = $intentosPermitidos;

        return $this;
    }

    /**
     * @return Collection<int, PreguntaQuizz>
     */
    public function getPreguntaQuizzs(): Collection
    {
        return $this->preguntaQuizzs;
    }

    public function addPreguntaQuizz(PreguntaQuizz $preguntaQuizz): static
    {
        if (!$this->preguntaQuizzs->contains($preguntaQuizz)) {
            $this->preguntaQuizzs->add($preguntaQuizz);
            $preguntaQuizz->setIdQuizz($this);
        }

        return $this;
    }

    public function removePreguntaQuizz(PreguntaQuizz $preguntaQuizz): static
    {
        if ($this->preguntaQuizzs->removeElement($preguntaQuizz)) {
            // set the owning side to null (unless already changed)
            if ($preguntaQuizz->getIdQuizz() === $this) {
                $preguntaQuizz->setIdQuizz(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IntentoQuizz>
     */
    public function getIntentoQuizzs(): Collection
    {
        return $this->intentoQuizzs;
    }

    public function addIntentoQuizz(IntentoQuizz $intentoQuizz): static
    {
        if (!$this->intentoQuizzs->contains($intentoQuizz)) {
            $this->intentoQuizzs->add($intentoQuizz);
            $intentoQuizz->setIdQuizz($this);
        }

        return $this;
    }

    public function removeIntentoQuizz(IntentoQuizz $intentoQuizz): static
    {
        if ($this->intentoQuizzs->removeElement($intentoQuizz)) {
            // set the owning side to null (unless already changed)
            if ($intentoQuizz->getIdQuizz() === $this) {
                $intentoQuizz->setIdQuizz(null);
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
