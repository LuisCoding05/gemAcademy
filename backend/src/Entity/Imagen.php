<?php

namespace App\Entity;

use App\Repository\ImagenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagenRepository::class)]
class Imagen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    /**
     * @var Collection<int, Usuario>
     */
    #[ORM\OneToMany(targetEntity: Usuario::class, mappedBy: 'imagen')]
    private Collection $usuarios;

    /**
     * @var Collection<int, Logro>
     */
    #[ORM\OneToMany(targetEntity: Logro::class, mappedBy: 'imagen')]
    private Collection $logros;

    /**
     * @var Collection<int, Curso>
     */
    #[ORM\OneToMany(targetEntity: Curso::class, mappedBy: 'imagen')]
    private Collection $cursos;

    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
        $this->logros = new ArrayCollection();
        $this->cursos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<int, Usuario>
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(Usuario $usuario): static
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios->add($usuario);
            $usuario->setImagen($this);
        }

        return $this;
    }

    public function removeUsuario(Usuario $usuario): static
    {
        if ($this->usuarios->removeElement($usuario)) {
            // set the owning side to null (unless already changed)
            if ($usuario->getImagen() === $this) {
                $usuario->setImagen(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Logro>
     */
    public function getLogros(): Collection
    {
        return $this->logros;
    }

    public function addLogro(Logro $logro): static
    {
        if (!$this->logros->contains($logro)) {
            $this->logros->add($logro);
            $logro->setImagen($this);
        }

        return $this;
    }

    public function removeLogro(Logro $logro): static
    {
        if ($this->logros->removeElement($logro)) {
            // set the owning side to null (unless already changed)
            if ($logro->getImagen() === $this) {
                $logro->setImagen(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Curso>
     */
    public function getCursos(): Collection
    {
        return $this->cursos;
    }

    public function addCurso(Curso $curso): static
    {
        if (!$this->cursos->contains($curso)) {
            $this->cursos->add($curso);
            $curso->setImagen($this);
        }

        return $this;
    }

    public function removeCurso(Curso $curso): static
    {
        if ($this->cursos->removeElement($curso)) {
            // set the owning side to null (unless already changed)
            if ($curso->getImagen() === $this) {
                $curso->setImagen(null);
            }
        }

        return $this;
    }
}
