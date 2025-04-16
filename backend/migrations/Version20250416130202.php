<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416130202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entrega_tarea ADD fichero_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE entrega_tarea ADD CONSTRAINT FK_87CDD04C8E05855 FOREIGN KEY (fichero_id) REFERENCES fichero (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_87CDD04C8E05855 ON entrega_tarea (fichero_id)');
        $this->addSql('ALTER TABLE fichero DROP FOREIGN KEY FK_F4E944347B2CCA01');
        $this->addSql('DROP INDEX UNIQ_F4E944347B2CCA01 ON fichero');
        $this->addSql('ALTER TABLE fichero DROP entrega_tarea_id');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entrega_tarea DROP FOREIGN KEY FK_87CDD04C8E05855');
        $this->addSql('DROP INDEX UNIQ_87CDD04C8E05855 ON entrega_tarea');
        $this->addSql('ALTER TABLE entrega_tarea DROP fichero_id');
        $this->addSql('ALTER TABLE fichero ADD entrega_tarea_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fichero ADD CONSTRAINT FK_F4E944347B2CCA01 FOREIGN KEY (entrega_tarea_id) REFERENCES entrega_tarea (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F4E944347B2CCA01 ON fichero (entrega_tarea_id)');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
