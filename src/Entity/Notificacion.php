<?php

namespace App\Entity;

use App\Repository\NotificacionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificacionRepository::class)]
class Notificacion
{
    public const TIPO_MENSAJE = 'mensaje_curso';
    public const TIPO_CORRECCION = 'correccion_entrega';
    public const TIPO_LOGRO = 'logro_desbloqueado';
    public const TIPO_TAREA = 'nueva_tarea';
    public const TIPO_RECORDATORIO = 'recordatorio';
    public const NUEVO_NIVEL = 'nuevo_nivel';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notificaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 50)]
    private ?string $tipo = null;

    #[ORM\Column(length: 255)]
    private ?string $titulo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenido = null;
    
    #[ORM\Column(type: 'smallint')]
    private ?int $leida = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaCreacion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fechaLectura = null;    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
        $this->leida = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;
        return $this;
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

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): static
    {
        $this->contenido = $contenido;
        return $this;
    }    public function isLeida(): ?bool
    {
        return $this->leida === 1;
    }

    public function setLeida(bool $leida): static
    {
        $this->leida = $leida ? 1 : 0;
        if ($leida && $this->fechaLectura === null) {
            $this->fechaLectura = new \DateTime();
        }
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
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

    public function getFechaLectura(): ?\DateTimeInterface
    {
        return $this->fechaLectura;
    }

    public function setFechaLectura(?\DateTimeInterface $fechaLectura): static
    {
        $this->fechaLectura = $fechaLectura;
        return $this;
    }
}