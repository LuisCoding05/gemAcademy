<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324181945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logro CHANGE puntos_otorgados puntos_otorgados INT DEFAULT 0');
        $this->addSql('ALTER TABLE nivel CHANGE puntos_requeridos puntos_requeridos INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE opcion_pregunta CHANGE es_correcta es_correcta TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE pregunta_quizz CHANGE puntos puntos INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE quizz CHANGE tiempo_limite tiempo_limite INT DEFAULT 30 NOT NULL');
        $this->addSql('ALTER TABLE respuesta_quizz CHANGE puntos_obtenidos puntos_obtenidos INT DEFAULT 10 NOT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE puntos_maximos puntos_maximos INT DEFAULT 100 NOT NULL, CHANGE es_obligatoria es_obligatoria TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE usuario CHANGE ban ban TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE usuario_curso CHANGE tareas_completadas tareas_completadas INT DEFAULT 0 NOT NULL, CHANGE tareas_totales tareas_totales INT DEFAULT 0 NOT NULL, CHANGE quizzes_completados quizzes_completados INT DEFAULT 0 NOT NULL, CHANGE quizzes_totales quizzes_totales INT DEFAULT 0 NOT NULL, CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE usuario_nivel CHANGE puntos_actuales puntos_actuales INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logro CHANGE puntos_otorgados puntos_otorgados INT DEFAULT NULL');
        $this->addSql('ALTER TABLE nivel CHANGE puntos_requeridos puntos_requeridos INT NOT NULL');
        $this->addSql('ALTER TABLE opcion_pregunta CHANGE es_correcta es_correcta TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE pregunta_quizz CHANGE puntos puntos INT NOT NULL');
        $this->addSql('ALTER TABLE quizz CHANGE tiempo_limite tiempo_limite INT NOT NULL');
        $this->addSql('ALTER TABLE respuesta_quizz CHANGE puntos_obtenidos puntos_obtenidos INT NOT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE puntos_maximos puntos_maximos INT NOT NULL, CHANGE es_obligatoria es_obligatoria TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE usuario CHANGE ban ban TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE usuario_curso CHANGE tareas_completadas tareas_completadas INT NOT NULL, CHANGE tareas_totales tareas_totales INT NOT NULL, CHANGE quizzes_completados quizzes_completados INT NOT NULL, CHANGE quizzes_totales quizzes_totales INT NOT NULL, CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) NOT NULL');
        $this->addSql('ALTER TABLE usuario_nivel CHANGE puntos_actuales puntos_actuales INT NOT NULL');
    }
}
