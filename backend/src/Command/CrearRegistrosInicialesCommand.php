<?php

namespace App\Command;

use App\Entity\UsuarioCurso;
use App\Service\CursoInscripcionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:crear-registros-iniciales',
    description: 'Crea registros iniciales para usuarios inscritos en cursos',
)]
class CrearRegistrosInicialesCommand extends Command
{
    private $entityManager;
    private $inscripcionService;

    public function __construct(EntityManagerInterface $entityManager, CursoInscripcionService $inscripcionService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->inscripcionService = $inscripcionService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creando registros iniciales para usuarios inscritos en cursos');

        // Obtener todos los usuarios inscritos en cursos
        $usuarioCursos = $this->entityManager->getRepository(UsuarioCurso::class)->findAll();
        $total = count($usuarioCursos);
        
        $io->progressStart($total);
        
        $actualizados = 0;
        
        foreach ($usuarioCursos as $usuarioCurso) {
            // Verificar si ya tiene registros iniciales
            $tieneMateriales = $usuarioCurso->getMaterialCompletados()->count() > 0;
            $tieneTareas = $usuarioCurso->getEntregaTareas()->count() > 0;
            
            // Si no tiene registros, crearlos
            if (!$tieneMateriales || !$tieneTareas) {
                $this->inscripcionService->crearRegistrosIniciales($usuarioCurso, $usuarioCurso->getIdCurso());
                $actualizados++;
            }
            
            $io->progressAdvance();
        }
        
        $io->progressFinish();
        
        $io->success("Proceso completado. Se actualizaron $actualizados de $total usuarios inscritos.");
        
        return Command::SUCCESS;
    }
} 