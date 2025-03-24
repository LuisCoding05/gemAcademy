<?php

namespace App\Entity;

use App\Repository\LogroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogroRepository::class)]
class Logro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logros')]
    private ?Imagen $imagen = null;

    #[ORM\Column(length: 100)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255)]
    private ?string $motivo = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $puntosOtorgados = null;

    /**
     * @var Collection<int, UsuarioLogro>
     */
    #[ORM\OneToMany(targetEntity: UsuarioLogro::class, mappedBy: 'idLogro')]
    private Collection $usuarioLogros;

    public function __construct()
    {
        $this->usuarioLogros = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getMotivo(): ?string
    {
        return $this->motivo;
    }

    public function setMotivo(string $motivo): static
    {
        $this->motivo = $motivo;

        return $this;
    }

    public function getPuntosOtorgados(): ?int
    {
        return $this->puntosOtorgados;
    }

    public function setPuntosOtorgados(?int $puntosOtorgados): static
    {
        $this->puntosOtorgados = $puntosOtorgados;

        return $this;
    }

    /**
     * @return Collection<int, UsuarioLogro>
     */
    public function getUsuarioLogros(): Collection
    {
        return $this->usuarioLogros;
    }

    public function addUsuarioLogro(UsuarioLogro $usuarioLogro): static
    {
        if (!$this->usuarioLogros->contains($usuarioLogro)) {
            $this->usuarioLogros->add($usuarioLogro);
            $usuarioLogro->setIdLogro($this);
        }

        return $this;
    }

    public function removeUsuarioLogro(UsuarioLogro $usuarioLogro): static
    {
        if ($this->usuarioLogros->removeElement($usuarioLogro)) {
            // set the owning side to null (unless already changed)
            if ($usuarioLogro->getIdLogro() === $this) {
                $usuarioLogro->setIdLogro(null);
            }
        }

        return $this;
    }
}
