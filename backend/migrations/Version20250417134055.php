<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417134055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichero DROP FOREIGN KEY FK_F4E94434E308AC6F');
        $this->addSql('DROP INDEX UNIQ_F4E94434E308AC6F ON fichero');
        $this->addSql('ALTER TABLE fichero DROP material_id');
        $this->addSql('ALTER TABLE quizz ADD intentos_permitidos INT DEFAULT 1');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichero ADD material_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fichero ADD CONSTRAINT FK_F4E94434E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F4E94434E308AC6F ON fichero (material_id)');
        $this->addSql('ALTER TABLE quizz DROP intentos_permitidos');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
