<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416102344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fichero (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, material_id INT DEFAULT NULL, entrega_tarea_id INT DEFAULT NULL, nombre_original VARCHAR(255) NOT NULL, ruta VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, fecha_subida DATETIME NOT NULL, INDEX IDX_F4E94434DB38439E (usuario_id), UNIQUE INDEX UNIQ_F4E94434E308AC6F (material_id), UNIQUE INDEX UNIQ_F4E944347B2CCA01 (entrega_tarea_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fichero ADD CONSTRAINT FK_F4E94434DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE fichero ADD CONSTRAINT FK_F4E94434E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE fichero ADD CONSTRAINT FK_F4E944347B2CCA01 FOREIGN KEY (entrega_tarea_id) REFERENCES entrega_tarea (id)');
        $this->addSql('ALTER TABLE entrega_tarea DROP archivo_url');
        $this->addSql('ALTER TABLE material DROP url');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichero DROP FOREIGN KEY FK_F4E94434DB38439E');
        $this->addSql('ALTER TABLE fichero DROP FOREIGN KEY FK_F4E94434E308AC6F');
        $this->addSql('ALTER TABLE fichero DROP FOREIGN KEY FK_F4E944347B2CCA01');
        $this->addSql('DROP TABLE fichero');
        $this->addSql('ALTER TABLE entrega_tarea ADD archivo_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE material ADD url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
