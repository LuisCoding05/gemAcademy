<?php

namespace App\Entity;

use App\Repository\MensajeForoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MensajeForoRepository::class)]
class MensajeForo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'mensajeHijo')]
    private ?self $idMensajePadre = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'idMensajePadre')]
    private Collection $mensajeHijo;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaPublicacion = null;

    #[ORM\ManyToOne(inversedBy: 'mensajeForos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Foro $idForo = null;

    #[ORM\ManyToOne(inversedBy: 'mensajeForos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $idUsuario = null;

    #[ORM\Column(length: 255)]
    private ?string $contenido = null;

    public function __construct()
    {
        $this->mensajeHijo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdMensajePadre(): ?self
    {
        return $this->idMensajePadre;
    }

    public function setIdMensajePadre(?self $idMensajePadre): static
    {
        $this->idMensajePadre = $idMensajePadre;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getMensajeHijo(): Collection
    {
        return $this->mensajeHijo;
    }

    public function addMensajeHijo(self $mensajeHijo): static
    {
        if (!$this->mensajeHijo->contains($mensajeHijo)) {
            $this->mensajeHijo->add($mensajeHijo);
            $mensajeHijo->setIdMensajePadre($this);
        }

        return $this;
    }

    public function removeMensajeHijo(self $mensajeHijo): static
    {
        if ($this->mensajeHijo->removeElement($mensajeHijo)) {
            // set the owning side to null (unless already changed)
            if ($mensajeHijo->getIdMensajePadre() === $this) {
                $mensajeHijo->setIdMensajePadre(null);
            }
        }

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

    public function getIdForo(): ?Foro
    {
        return $this->idForo;
    }

    public function setIdForo(?Foro $idForo): static
    {
        $this->idForo = $idForo;

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

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): static
    {
        $this->contenido = $contenido;

        return $this;
    }
}
