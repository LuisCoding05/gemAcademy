<?php

namespace App\Entity;

use App\Repository\IntentoQuizzRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntentoQuizzRepository::class)]
class IntentoQuizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'intentoQuizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quizz $idQuizz = null;

    #[ORM\ManyToOne(inversedBy: 'intentoQuizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $idUsuario = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaInicio = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaFin = null;

    #[ORM\Column]
    private ?int $puntuacionTotal = null;

    #[ORM\Column]
    private ?bool $completado = null;

    /**
     * @var Collection<int, RespuestaQuizz>
     */
    #[ORM\OneToMany(targetEntity: RespuestaQuizz::class, mappedBy: 'idIntento')]
    private Collection $respuestaQuizzs;

    public function __construct()
    {
        $this->respuestaQuizzs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdQuizz(): ?Quizz
    {
        return $this->idQuizz;
    }

    public function setIdQuizz(?Quizz $idQuizz): static
    {
        $this->idQuizz = $idQuizz;

        return $this;
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

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $fechaInicio): static
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(\DateTimeInterface $fechaFin): static
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getPuntuacionTotal(): ?int
    {
        return $this->puntuacionTotal;
    }

    public function setPuntuacionTotal(int $puntuacionTotal): static
    {
        $this->puntuacionTotal = $puntuacionTotal;

        return $this;
    }

    public function isCompletado(): ?bool
    {
        return $this->completado;
    }

    public function setCompletado(bool $completado): static
    {
        $this->completado = $completado;

        return $this;
    }

    /**
     * @return Collection<int, RespuestaQuizz>
     */
    public function getRespuestaQuizzs(): Collection
    {
        return $this->respuestaQuizzs;
    }

    public function addRespuestaQuizz(RespuestaQuizz $respuestaQuizz): static
    {
        if (!$this->respuestaQuizzs->contains($respuestaQuizz)) {
            $this->respuestaQuizzs->add($respuestaQuizz);
            $respuestaQuizz->setIdIntento($this);
        }

        return $this;
    }

    public function removeRespuestaQuizz(RespuestaQuizz $respuestaQuizz): static
    {
        if ($this->respuestaQuizzs->removeElement($respuestaQuizz)) {
            // set the owning side to null (unless already changed)
            if ($respuestaQuizz->getIdIntento() === $this) {
                $respuestaQuizz->setIdIntento(null);
            }
        }

        return $this;
    }
}
