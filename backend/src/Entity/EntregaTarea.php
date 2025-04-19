<?php

namespace App\Entity;

use App\Repository\EntregaTareaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntregaTareaRepository::class)]
class EntregaTarea
{

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_ENTREGADO = 'entregado';
    public const ESTADO_CALIFICADO = 'calificado';
    public const ESTADO_REVISION_SOLICITADA = 'revision_solicitada';
    public const ESTADO_ATRASADO = 'atrasado';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
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

    #[ORM\Column(length: 100, options: ['default' => self::ESTADO_PENDIENTE])]
    private ?string $estado = self::ESTADO_PENDIENTE;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $comentarioEstudiante = null;

    #[ORM\OneToOne(inversedBy: 'entregaTarea', cascade: ['persist', 'remove'])]
    private ?Fichero $fichero = null;

    public function __construct()
    {
        $this->estado = self::ESTADO_PENDIENTE;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaEntrega(): ?\DateTimeInterface
    {
        return $this->fechaEntrega;
    }

    public function setFechaEntrega(?\DateTimeInterface $fechaEntrega): static
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

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        if (!in_array($estado, [
            self::ESTADO_PENDIENTE,
            self::ESTADO_ENTREGADO,
            self::ESTADO_CALIFICADO,
            self::ESTADO_REVISION_SOLICITADA,
            self::ESTADO_ATRASADO
        ])) {
            throw new \InvalidArgumentException('Estado no válido');
        }

        $this->estado = $estado;
        return $this;
    }

    public function getComentarioEstudiante(): ?string
    {
        return $this->comentarioEstudiante;
    }

    public function setComentarioEstudiante(?string $comentarioEstudiante): static
    {
        $this->comentarioEstudiante = $comentarioEstudiante;

        return $this;
    }

    public function isEntregado(): bool
    {
        return $this->estado !== self::ESTADO_PENDIENTE;
    }

    public function isCalificado(): bool
    {
        return $this->estado === self::ESTADO_CALIFICADO;
    }

    public function isPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function isRevisionSolicitada(): bool
    {
        return $this->estado === self::ESTADO_REVISION_SOLICITADA;
    }

    public function getFichero(): ?Fichero
    {
        return $this->fichero;
    }

    public function setFichero(?Fichero $fichero): static
    {
        // unset the owning side of the relation if necessary
        if ($fichero === null && $this->fichero !== null) {
            $this->fichero->setEntregaTarea(null);
        }

        // set the owning side of the relation if necessary
        if ($fichero !== null && $fichero->getEntregaTarea() !== $this) {
            $fichero->setEntregaTarea($this);
        }

        $this->fichero = $fichero;

        return $this;
    }

}
