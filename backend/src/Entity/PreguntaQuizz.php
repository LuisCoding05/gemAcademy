<?php

namespace App\Entity;

use App\Repository\PreguntaQuizzRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreguntaQuizzRepository::class)]
class PreguntaQuizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pregunta = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $puntos = null;

    #[ORM\Column(nullable: true)]
    private ?int $orden = null;

    #[ORM\ManyToOne(inversedBy: 'preguntaQuizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quizz $idQuizz = null;

    /**
     * @var Collection<int, OpcionPregunta>
     */
    #[ORM\OneToMany(targetEntity: OpcionPregunta::class, mappedBy: 'idPregunta')]
    private Collection $opcionPreguntas;

    /**
     * @var Collection<int, RespuestaQuizz>
     */
    #[ORM\OneToMany(targetEntity: RespuestaQuizz::class, mappedBy: 'idPregunta')]
    private Collection $respuestaQuizzs;

    public function __construct()
    {
        $this->opcionPreguntas = new ArrayCollection();
        $this->respuestaQuizzs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPregunta(): ?string
    {
        return $this->pregunta;
    }

    public function setPregunta(string $pregunta): static
    {
        $this->pregunta = $pregunta;

        return $this;
    }

    public function getPuntos(): ?int
    {
        return $this->puntos;
    }

    public function setPuntos(int $puntos): static
    {
        $this->puntos = $puntos;

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

    public function getIdQuizz(): ?Quizz
    {
        return $this->idQuizz;
    }

    public function setIdQuizz(?Quizz $idQuizz): static
    {
        $this->idQuizz = $idQuizz;

        return $this;
    }

    /**
     * @return Collection<int, OpcionPregunta>
     */
    public function getOpcionPreguntas(): Collection
    {
        return $this->opcionPreguntas;
    }

    public function addOpcionPregunta(OpcionPregunta $opcionPregunta): static
    {
        if (!$this->opcionPreguntas->contains($opcionPregunta)) {
            $this->opcionPreguntas->add($opcionPregunta);
            $opcionPregunta->setIdPregunta($this);
        }

        return $this;
    }

    public function removeOpcionPregunta(OpcionPregunta $opcionPregunta): static
    {
        if ($this->opcionPreguntas->removeElement($opcionPregunta)) {
            // set the owning side to null (unless already changed)
            if ($opcionPregunta->getIdPregunta() === $this) {
                $opcionPregunta->setIdPregunta(null);
            }
        }

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
            $respuestaQuizz->setIdPregunta($this);
        }

        return $this;
    }

    public function removeRespuestaQuizz(RespuestaQuizz $respuestaQuizz): static
    {
        if ($this->respuestaQuizzs->removeElement($respuestaQuizz)) {
            // set the owning side to null (unless already changed)
            if ($respuestaQuizz->getIdPregunta() === $this) {
                $respuestaQuizz->setIdPregunta(null);
            }
        }

        return $this;
    }
}
