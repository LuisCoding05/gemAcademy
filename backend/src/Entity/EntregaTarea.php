<?php

namespace App\Entity;

use App\Repository\EntregaTareaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntregaTareaRepository::class)]
class EntregaTarea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $archivoUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaEntrega = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    private ?string $calificacion = null;

    #[ORM\Column(nullable: true)]
    private ?int $puntosObtenidos = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comentarioProfesor = null;

    #[ORM\ManyToOne(inversedBy: 'entregaTareas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tarea $idTarea = null;

    #[ORM\ManyToOne(inversedBy: 'entregaTareas')]
    private ?UsuarioCurso $usuarioCurso = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFechaEntrega(): ?\DateTimeInterface
    {
        return $this->fechaEntrega;
    }

    public function setFechaEntrega(\DateTimeInterface $fechaEntrega): static
    {
        $this->fechaEntrega = $fechaEntrega;

        return $this;
    }

    public function getCalificacion(): ?string
    {
        return $this->calificacion;
    }

    public function setCalificacion(?string $calificacion): static
    {
        $this->calificacion = $calificacion;

        return $this;
    }

    public function getPuntosObtenidos(): ?int
    {
        return $this->puntosObtenidos;
    }

    public function setPuntosObtenidos(?int $puntosObtenidos): static
    {
        $this->puntosObtenidos = $puntosObtenidos;

        return $this;
    }

    public function getComentarioProfesor(): ?string
    {
        return $this->comentarioProfesor;
    }

    public function setComentarioProfesor(?string $comentarioProfesor): static
    {
        $this->comentarioProfesor = $comentarioProfesor;

        return $this;
    }

    public function getIdTarea(): ?Tarea
    {
        return $this->idTarea;
    }

    public function setIdTarea(?Tarea $idTarea): static
    {
        $this->idTarea = $idTarea;

        return $this;
    }

    public function getUsuarioCurso(): ?UsuarioCurso
    {
        return $this->usuarioCurso;
    }

    public function setUsuarioCurso(?UsuarioCurso $usuarioCurso): static
    {
        $this->usuarioCurso = $usuarioCurso;

        return $this;
    }

}
