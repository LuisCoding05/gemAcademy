<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 150, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'usuarios')]
    private ?Imagen $imagen = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $ban = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaRegistro = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ultimaConexion = null;

    /**
     * @var Collection<int, UsuarioCurso>
     */
    #[ORM\OneToMany(targetEntity: UsuarioCurso::class, mappedBy: 'idUsuario')]
    private Collection $usuarioCursos;

    /**
     * @var Collection<int, UsuarioLogro>
     */
    #[ORM\OneToMany(targetEntity: UsuarioLogro::class, mappedBy: 'idUsuario')]
    private Collection $usuarioLogros;

    /**
     * @var Collection<int, MensajeForo>
     */
    #[ORM\OneToMany(targetEntity: MensajeForo::class, mappedBy: 'idUsuario')]
    private Collection $mensajeForos;

    /**
     * @var Collection<int, EntregaTarea>
     */
    #[ORM\OneToMany(targetEntity: EntregaTarea::class, mappedBy: 'idUsuario')]
    private Collection $entregaTareas;

    /**
     * @var Collection<int, IntentoQuizz>
     */
    #[ORM\OneToMany(targetEntity: IntentoQuizz::class, mappedBy: 'idUsuario')]
    private Collection $intentoQuizzs;

    /**
     * @var Collection<int, UsuarioNivel>
     */
    #[ORM\OneToMany(targetEntity: UsuarioNivel::class, mappedBy: 'idUsuario')]
    private Collection $usuarioNivels;

    /**
     * @var Collection<int, Curso>
     */
    #[ORM\OneToMany(targetEntity: Curso::class, mappedBy: 'profesor')]
    private Collection $cursos;

    #[ORM\Column]
    private ?bool $verificado = null;

    #[ORM\Column(length: 150)]
    private ?string $nombre = null;

    #[ORM\Column(length: 150)]
    private ?string $apellido = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $apellido2 = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, Log>
     */
    #[ORM\OneToMany(targetEntity: Log::class, mappedBy: 'usuario')]
    private Collection $log;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $tokenVerificacion = null;

    public function __construct()
    {
        $this->usuarioCursos = new ArrayCollection();
        $this->usuarioLogros = new ArrayCollection();
        $this->mensajeForos = new ArrayCollection();
        $this->entregaTareas = new ArrayCollection();
        $this->intentoQuizzs = new ArrayCollection();
        $this->usuarioNivels = new ArrayCollection();
        $this->cursos = new ArrayCollection();
        $this->fechaRegistro = new \DateTime();
        $this->log = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
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

    public function isBan(): ?bool
    {
        return $this->ban;
    }

    public function setBan(bool $ban): static
    {
        $this->ban = $ban;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): static
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getUltimaConexion(): ?\DateTimeInterface
    {
        return $this->ultimaConexion;
    }

    public function setUltimaConexion(?\DateTimeInterface $ultimaConexion): static
    {
        $this->ultimaConexion = $ultimaConexion;

        return $this;
    }

    /**
     * @return Collection<int, UsuarioCurso>
     */
    public function getUsuarioCursos(): Collection
    {
        return $this->usuarioCursos;
    }

    public function addUsuarioCurso(UsuarioCurso $usuarioCurso): static
    {
        if (!$this->usuarioCursos->contains($usuarioCurso)) {
            $this->usuarioCursos->add($usuarioCurso);
            $usuarioCurso->setIdUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioCurso(UsuarioCurso $usuarioCurso): static
    {
        if ($this->usuarioCursos->removeElement($usuarioCurso)) {
            // set the owning side to null (unless already changed)
            if ($usuarioCurso->getIdUsuario() === $this) {
                $usuarioCurso->setIdUsuario(null);
            }
        }

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
            $usuarioLogro->setIdUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioLogro(UsuarioLogro $usuarioLogro): static
    {
        if ($this->usuarioLogros->removeElement($usuarioLogro)) {
            // set the owning side to null (unless already changed)
            if ($usuarioLogro->getIdUsuario() === $this) {
                $usuarioLogro->setIdUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MensajeForo>
     */
    public function getMensajeForos(): Collection
    {
        return $this->mensajeForos;
    }

    public function addMensajeForo(MensajeForo $mensajeForo): static
    {
        if (!$this->mensajeForos->contains($mensajeForo)) {
            $this->mensajeForos->add($mensajeForo);
            $mensajeForo->setIdUsuario($this);
        }

        return $this;
    }

    public function removeMensajeForo(MensajeForo $mensajeForo): static
    {
        if ($this->mensajeForos->removeElement($mensajeForo)) {
            // set the owning side to null (unless already changed)
            if ($mensajeForo->getIdUsuario() === $this) {
                $mensajeForo->setIdUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EntregaTarea>
     */
    public function getEntregaTareas(): Collection
    {
        return $this->entregaTareas;
    }

    /**
     * @return Collection<int, IntentoQuizz>
     */
    public function getIntentoQuizzs(): Collection
    {
        return $this->intentoQuizzs;
    }

    public function addIntentoQuizz(IntentoQuizz $intentoQuizz): static
    {
        if (!$this->intentoQuizzs->contains($intentoQuizz)) {
            $this->intentoQuizzs->add($intentoQuizz);
            $intentoQuizz->setIdUsuario($this);
        }

        return $this;
    }

    public function removeIntentoQuizz(IntentoQuizz $intentoQuizz): static
    {
        if ($this->intentoQuizzs->removeElement($intentoQuizz)) {
            // set the owning side to null (unless already changed)
            if ($intentoQuizz->getIdUsuario() === $this) {
                $intentoQuizz->setIdUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UsuarioNivel>
     */
    public function getUsuarioNivels(): Collection
    {
        return $this->usuarioNivels;
    }

    public function addUsuarioNivel(UsuarioNivel $usuarioNivel): static
    {
        if (!$this->usuarioNivels->contains($usuarioNivel)) {
            $this->usuarioNivels->add($usuarioNivel);
            $usuarioNivel->setIdUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioNivel(UsuarioNivel $usuarioNivel): static
    {
        if ($this->usuarioNivels->removeElement($usuarioNivel)) {
            // set the owning side to null (unless already changed)
            if ($usuarioNivel->getIdUsuario() === $this) {
                $usuarioNivel->setIdUsuario(null);
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
            $curso->setProfesor($this);
        }

        return $this;
    }

    public function removeCurso(Curso $curso): static
    {
        if ($this->cursos->removeElement($curso)) {
            // set the owning side to null (unless already changed)
            if ($curso->getProfesor() === $this) {
                $curso->setProfesor(null);
            }
        }

        return $this;
    }

    public function isVerificado(): ?bool
    {
        return $this->verificado;
    }

    public function isBanned(): ?bool
    {
        return $this->ban;
    }

    public function setVerificado(bool $verificado): static
    {
        $this->verificado = $verificado;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function setApellido2(?string $apellido2): static
    {
        $this->apellido2 = $apellido2;

        return $this;
    }

    public function getApellido2(): ?string
    {
        return $this->apellido2;
    }

    public function eraseCredentials(): void
    {
        // Si almacenas datos temporales sensibles, límpialos aquí
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantiza que todos los usuarios tengan al menos ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Log>
     */
    public function getLog(): Collection
    {
        return $this->log;
    }

    public function addLog(Log $log): static
    {
        if (!$this->log->contains($log)) {
            $this->log->add($log);
            $log->setUsuario($this);
        }

        return $this;
    }

    public function removeLog(Log $log): static
    {
        if ($this->log->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getUsuario() === $this) {
                $log->setUsuario(null);
            }
        }

        return $this;
    }

    public function getTokenVerificacion(): ?string
    {
        return $this->tokenVerificacion;
    }

    public function setTokenVerificacion(?string $tokenVerificacion): static
    {
        $this->tokenVerificacion = $tokenVerificacion;

        return $this;
    }
}
