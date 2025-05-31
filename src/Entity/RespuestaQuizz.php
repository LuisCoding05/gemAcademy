<?php

namespace App\Entity;

use App\Repository\RespuestaQuizzRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespuestaQuizzRepository::class)]
class RespuestaQuizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'respuestaQuizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?IntentoQuizz $idIntento = null;

    #[ORM\ManyToOne(inversedBy: 'respuestaQuizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PreguntaQuizz $idPregunta = null;

    #[ORM\Column(length: 255)]
    private ?string $respuesta = null;

    #[ORM\Column]
    private ?bool $esCorrecta = null;

    #[ORM\Column(options: ['default' => 10])]
    private ?int $puntosObtenidos = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdIntento(): ?IntentoQuizz
    {
        return $this->idIntento;
    }

    public function setIdIntento(?IntentoQuizz $idIntento): static
    {
        $this->idIntento = $idIntento;

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

    public function getRespuesta(): ?string
    {
        return $this->respuesta;
    }

    public function setRespuesta(string $respuesta): static
    {
        $this->respuesta = $respuesta;

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

    public function getPuntosObtenidos(): ?int
    {
        return $this->puntosObtenidos;
    }

    public function setPuntosObtenidos(int $puntosObtenidos): static
    {
        $this->puntosObtenidos = $puntosObtenidos;

        return $this;
    }
}
