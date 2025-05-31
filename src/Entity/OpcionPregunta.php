<?php

namespace App\Entity;

use App\Repository\OpcionPreguntaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpcionPreguntaRepository::class)]
class OpcionPregunta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $texto = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $esCorrecta = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $retroalimentacion = null;

    #[ORM\ManyToOne(inversedBy: 'opcionPreguntas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PreguntaQuizz $idPregunta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexto(): ?string
    {
        return $this->texto;
    }

    public function setTexto(string $texto): static
    {
        $this->texto = $texto;

        return $this;
    }

    public function isEsCorrecta(): ?bool
    {
        return $this->esCorrecta;
    }

    public function setEsCorrecta(bool $esCorrecta): static
    {
        $this->esCorrecta = $esCorrecta;

        return $this;
    }

    public function getRetroalimentacion(): ?string
    {
        return $this->retroalimentacion;
    }

    public function setRetroalimentacion(?string $retroalimentacion): static
    {
        $this->retroalimentacion = $retroalimentacion;

        return $this;
    }

    public function getIdPregunta(): ?PreguntaQuizz
    {
        return $this->idPregunta;
    }

    public function setIdPregunta(?PreguntaQuizz $idPregunta): static
    {
        $this->idPregunta = $idPregunta;

        return $this;
    }
}
