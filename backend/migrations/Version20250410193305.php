<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410193305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE material (id INT AUTO_INCREMENT NOT NULL, id_curso_id INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, fecha_publicacion DATETIME NOT NULL, orden INT DEFAULT NULL, INDEX IDX_7CBE7595D710A68A (id_curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595D710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE quizz ADD id_curso_id INT NOT NULL');
        $this->addSql('ALTER TABLE quizz ADD CONSTRAINT FK_7C77973DD710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('CREATE INDEX IDX_7C77973DD710A68A ON quizz (id_curso_id)');
        $this->addSql('ALTER TABLE usuario ADD token_verificacion VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595D710A68A');
        $this->addSql('DROP TABLE material');
        $this->addSql('ALTER TABLE quizz DROP FOREIGN KEY FK_7C77973DD710A68A');
        $this->addSql('DROP INDEX IDX_7C77973DD710A68A ON quizz');
        $this->addSql('ALTER TABLE quizz DROP id_curso_id');
        $this->addSql('ALTER TABLE usuario DROP token_verificacion');
        $this->addSql('ALTER TABLE usuario_curso CHANGE porcentaje_completado porcentaje_completado NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
