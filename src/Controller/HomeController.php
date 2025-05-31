<?php

namespace App\Controller;

use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api')]
class HomeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/home', name: 'api_home', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        return $this->json([
            'message' => 'Bienvenido a la página de administración',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

    #[Route('/home/users', name: 'api_home_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getUsers(Request $request): JsonResponse
    {
        // Parámetros de paginación
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $search = $request->query->get('search', '');

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
           ->from(Usuario::class, 'u');

        // Aplicar filtro de búsqueda si existe
        if ($search) {
            $qb->where('u.username LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Obtener total de registros para la paginación
        $totalQb = clone $qb;
        $total = count($totalQb->getQuery()->getResult());

        // Aplicar paginación
        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit)
           ->orderBy('u.id', 'ASC');

        $usuarios = $qb->getQuery()->getResult();

        $usuariosData = array_map(function($usuario) {
            return [
                'id' => $usuario->getId(),
                'username' => $usuario->getUsername(),
                'email' => $usuario->getEmail(),
                'nombre' => $usuario->getNombre(),
                'apellido' => $usuario->getApellido(),
                'roles' => $usuario->getRoles(),
                'banned' => $usuario->isBanned(),
                'fechaRegistro' => $usuario->getFechaRegistro()->format('Y-m-d H:i:s'),
                'ultimaConexion' => $usuario->getUltimaConexion() ? $usuario->getUltimaConexion()->format('Y-m-d H:i:s') : null,
            ];
        }, $usuarios);

        return $this->json([
            'usuarios' => $usuariosData,
            'total' => $total,
            'pagina' => $page,
            'limite' => $limit,
            'totalPaginas' => ceil($total / $limit)
        ]);
    }

    #[Route('/home/users/{id}/update', name: 'api_home_update_user', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(Usuario::class)->find($id);
        if (!$user) {
            return $this->json(['message' => 'Usuario no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);        // No permitir que un admin se modifique a sí mismo
        if ($user->getId() === $this->getUser()->getId()) {
            return $this->json(['message' => 'No puedes modificar tu propio usuario'], 403);
        }

        /** @var Usuario $currentUser */
        $currentUser = $this->getUser();
        
        // No permitir que un admin normal modifique a un super administrador
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            return $this->json(['message' => 'No puedes modificar a un Super Administrador'], 403);
        }

        // Actualizar estado de ban si se proporciona
        if (isset($data['banned'])) {
            $user->setBan($data['banned']);
        }        // Actualizar roles si se proporcionan
        if (isset($data['roles'])) {
            /** @var Usuario $currentUser */
            $currentUser = $this->getUser();
            $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles());
            
            // Solo un super admin puede otorgar o quitar el rol de super admin
            if (!$isSuperAdmin) {
                // Si el usuario a modificar tiene ROLE_SUPER_ADMIN, conservarlo
                if (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $data['roles'])) {
                    $data['roles'][] = 'ROLE_SUPER_ADMIN';
                }
                
                // No permitir que un admin normal añada el rol ROLE_SUPER_ADMIN
                if (in_array('ROLE_SUPER_ADMIN', $data['roles']) && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                    return $this->json(['message' => 'No tienes permisos para otorgar el rol de Super Administrador'], 403);
                }
            }
            
            // Asegurarse de que ROLE_USER siempre esté presente
            if (!in_array('ROLE_USER', $data['roles'])) {
                $data['roles'][] = 'ROLE_USER';
            }
            
            $user->setRoles($data['roles']);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'banned' => $user->isBanned()
            ]
        ]);
    }
}