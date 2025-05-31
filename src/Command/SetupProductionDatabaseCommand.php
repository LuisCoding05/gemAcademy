<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:setup-production-database',
    description: 'Setup database schema and load initial data for production deployment',
)]
class SetupProductionDatabaseCommand extends Command
{
    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force database initialization even if tables exist')
            ->setHelp('This command sets up the database schema and loads initial data for production deployment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🚀 Setting up production database');

        try {
            // Test database connection
            $io->text('📡 Testing database connection...');
            $this->connection->connect();
            $io->success('✅ Database connection successful!');

            // Check if tables exist
            $force = $input->getOption('force');
            $tablesExist = $this->checkIfTablesExist();

            if ($tablesExist && !$force) {
                $io->note('ℹ️ Database tables already exist. Use --force to reinitialize.');
                
                // Run migrations if any are pending
                $io->text('🔄 Checking for pending migrations...');
                $this->runMigrations($input, $output, $io);
                
                return Command::SUCCESS;
            }

            if ($force && $tablesExist) {
                $io->warning('⚠️ Force mode: Dropping existing database schema...');
                $this->runCommand('doctrine:schema:drop', ['--force' => true], $input, $output, $io);
            }

            // Create database schema
            $io->text('🗄️ Creating database schema...');
            $this->runCommand('doctrine:schema:create', [], $input, $output, $io);

            // Load fixtures
            $io->text('📊 Loading initial data (fixtures)...');
            $this->runCommand('doctrine:fixtures:load', ['--no-interaction' => true], $input, $output, $io);

            // Clear and warm up cache
            $io->text('🧹 Clearing cache...');
            $this->runCommand('cache:clear', ['--env' => 'prod'], $input, $output, $io);

            $io->text('🔥 Warming up cache...');
            $this->runCommand('cache:warmup', ['--env' => 'prod'], $input, $output, $io);

            $io->success('🎉 Database setup completed successfully!');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $io->error('❌ Database setup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function checkIfTablesExist(): bool
    {
        try {
            $result = $this->connection->fetchOne(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'usuario'"
            );
            return (int) $result > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function runCommand(string $commandName, array $arguments, InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $command = $this->getApplication()->find($commandName);
        $commandInput = new ArrayInput(array_merge(['command' => $commandName], $arguments));
        $commandInput->setInteractive(false);
        
        $returnCode = $command->run($commandInput, $output);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException("Command $commandName failed with return code $returnCode");
        }
    }

    private function runMigrations(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        try {
            $this->runCommand('doctrine:migrations:migrate', ['--no-interaction' => true], $input, $output, $io);
            $io->text('✅ Migrations executed successfully');
        } catch (\Exception $e) {
            $io->text('ℹ️ No migrations to execute or migrations failed: ' . $e->getMessage());
        }
    }
}
