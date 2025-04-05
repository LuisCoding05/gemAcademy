<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405133911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE foro ADD curso_id INT NOT NULL');
        $this->addSql('ALTER TABLE foro ADD CONSTRAINT FK_BC869C6387CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id)');
        $this->addSql('CREATE INDEX IDX_BC869C6387CB4A1F ON foro (curso_id)');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE foro DROP FOREIGN KEY FK_BC869C6387CB4A1F');
        $this->addSql('DROP INDEX IDX_BC869C6387CB4A1F ON foro');
        $this->addSql('ALTER TABLE foro DROP curso_id');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
