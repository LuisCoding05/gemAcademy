<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entrega_tarea ADD estado VARCHAR(100) DEFAULT \'pendiente\' NOT NULL, ADD comentario_estudiante VARCHAR(255) DEFAULT NULL, CHANGE fecha_entrega fecha_entrega DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE intento_quizz CHANGE fecha_inicio fecha_inicio DATETIME DEFAULT NULL, CHANGE fecha_fin fecha_fin DATETIME DEFAULT NULL, CHANGE puntuacion_total puntuacion_total INT DEFAULT NULL, CHANGE completado completado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entrega_tarea DROP estado, DROP comentario_estudiante, CHANGE fecha_entrega fecha_entrega DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intento_quizz CHANGE fecha_inicio fecha_inicio DATETIME NOT NULL, CHANGE fecha_fin fecha_fin DATETIME NOT NULL, CHANGE puntuacion_total puntuacion_total INT NOT NULL, CHANGE completado completado TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
