<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250412155303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quizz_completado DROP FOREIGN KEY FK_6EC9C0ABBA934BCD');
        $this->addSql('ALTER TABLE quizz_completado DROP FOREIGN KEY FK_6EC9C0AB6F0AB886');
        $this->addSql('DROP TABLE quizz_completado');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quizz_completado (id INT AUTO_INCREMENT NOT NULL, usuario_curso_id INT NOT NULL, quizz_id INT NOT NULL, fecha_completado DATETIME NOT NULL, INDEX IDX_6EC9C0AB6F0AB886 (usuario_curso_id), INDEX IDX_6EC9C0ABBA934BCD (quizz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE quizz_completado ADD CONSTRAINT FK_6EC9C0ABBA934BCD FOREIGN KEY (quizz_id) REFERENCES quizz (id)');
        $this->addSql('ALTER TABLE quizz_completado ADD CONSTRAINT FK_6EC9C0AB6F0AB886 FOREIGN KEY (usuario_curso_id) REFERENCES usuario_curso (id)');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
