<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324150458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intento_quizz (id INT AUTO_INCREMENT NOT NULL, id_quizz_id INT NOT NULL, id_usuario_id INT NOT NULL, fecha_inicio DATETIME NOT NULL, fecha_fin DATETIME NOT NULL, puntuacion_total INT NOT NULL, completado TINYINT(1) NOT NULL, INDEX IDX_B88CE435EA48A758 (id_quizz_id), INDEX IDX_B88CE4357EB2C349 (id_usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respuesta_quizz (id INT AUTO_INCREMENT NOT NULL, id_intento_id INT NOT NULL, id_pregunta_id INT NOT NULL, respuesta VARCHAR(255) NOT NULL, es_correcta TINYINT(1) NOT NULL, puntos_obtenidos INT NOT NULL, INDEX IDX_61C0D48CCA2EB257 (id_intento_id), INDEX IDX_61C0D48C29B74DE9 (id_pregunta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_nivel (id INT AUTO_INCREMENT NOT NULL, id_usuario_id INT NOT NULL, id_nivel_id INT NOT NULL, puntos_siguiente_nivel INT NOT NULL, puntos_actuales INT NOT NULL, fecha_ultimo_nivel DATETIME NOT NULL, INDEX IDX_B7224AD57EB2C349 (id_usuario_id), INDEX IDX_B7224AD58AEFCA3B (id_nivel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE intento_quizz ADD CONSTRAINT FK_B88CE435EA48A758 FOREIGN KEY (id_quizz_id) REFERENCES quizz (id)');
        $this->addSql('ALTER TABLE intento_quizz ADD CONSTRAINT FK_B88CE4357EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE respuesta_quizz ADD CONSTRAINT FK_61C0D48CCA2EB257 FOREIGN KEY (id_intento_id) REFERENCES intento_quizz (id)');
        $this->addSql('ALTER TABLE respuesta_quizz ADD CONSTRAINT FK_61C0D48C29B74DE9 FOREIGN KEY (id_pregunta_id) REFERENCES pregunta_quizz (id)');
        $this->addSql('ALTER TABLE usuario_nivel ADD CONSTRAINT FK_B7224AD57EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario_nivel ADD CONSTRAINT FK_B7224AD58AEFCA3B FOREIGN KEY (id_nivel_id) REFERENCES nivel (id)');
        $this->addSql('ALTER TABLE curso ADD profesor_id INT NOT NULL');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CA3B40ECE52BD977 FOREIGN KEY (profesor_id) REFERENCES usuario (id)');
        $this->addSql('CREATE INDEX IDX_CA3B40ECE52BD977 ON curso (profesor_id)');
        $this->addSql('ALTER TABLE entrega_tarea ADD id_tarea_id INT NOT NULL, ADD id_usuario_id INT NOT NULL');
        $this->addSql('ALTER TABLE entrega_tarea ADD CONSTRAINT FK_87CDD04C3D803374 FOREIGN KEY (id_tarea_id) REFERENCES tarea (id)');
        $this->addSql('ALTER TABLE entrega_tarea ADD CONSTRAINT FK_87CDD04C7EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('CREATE INDEX IDX_87CDD04C3D803374 ON entrega_tarea (id_tarea_id)');
        $this->addSql('CREATE INDEX IDX_87CDD04C7EB2C349 ON entrega_tarea (id_usuario_id)');
        $this->addSql('ALTER TABLE mensaje_foro ADD id_foro_id INT NOT NULL, ADD id_usuario_id INT NOT NULL, ADD contenido VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE mensaje_foro ADD CONSTRAINT FK_A6946C222B62FA86 FOREIGN KEY (id_foro_id) REFERENCES foro (id)');
        $this->addSql('ALTER TABLE mensaje_foro ADD CONSTRAINT FK_A6946C227EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('CREATE INDEX IDX_A6946C222B62FA86 ON mensaje_foro (id_foro_id)');
        $this->addSql('CREATE INDEX IDX_A6946C227EB2C349 ON mensaje_foro (id_usuario_id)');
        $this->addSql('ALTER TABLE opcion_pregunta ADD id_pregunta_id INT NOT NULL');
        $this->addSql('ALTER TABLE opcion_pregunta ADD CONSTRAINT FK_47E330D129B74DE9 FOREIGN KEY (id_pregunta_id) REFERENCES pregunta_quizz (id)');
        $this->addSql('CREATE INDEX IDX_47E330D129B74DE9 ON opcion_pregunta (id_pregunta_id)');
        $this->addSql('ALTER TABLE tarea ADD id_curso_id INT NOT NULL');
        $this->addSql('ALTER TABLE tarea ADD CONSTRAINT FK_3CA05366D710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('CREATE INDEX IDX_3CA05366D710A68A ON tarea (id_curso_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intento_quizz DROP FOREIGN KEY FK_B88CE435EA48A758');
        $this->addSql('ALTER TABLE intento_quizz DROP FOREIGN KEY FK_B88CE4357EB2C349');
        $this->addSql('ALTER TABLE respuesta_quizz DROP FOREIGN KEY FK_61C0D48CCA2EB257');
        $this->addSql('ALTER TABLE respuesta_quizz DROP FOREIGN KEY FK_61C0D48C29B74DE9');
        $this->addSql('ALTER TABLE usuario_nivel DROP FOREIGN KEY FK_B7224AD57EB2C349');
        $this->addSql('ALTER TABLE usuario_nivel DROP FOREIGN KEY FK_B7224AD58AEFCA3B');
        $this->addSql('DROP TABLE intento_quizz');
        $this->addSql('DROP TABLE respuesta_quizz');
        $this->addSql('DROP TABLE usuario_nivel');
        $this->addSql('ALTER TABLE curso DROP FOREIGN KEY FK_CA3B40ECE52BD977');
        $this->addSql('DROP INDEX IDX_CA3B40ECE52BD977 ON curso');
        $this->addSql('ALTER TABLE curso DROP profesor_id');
        $this->addSql('ALTER TABLE entrega_tarea DROP FOREIGN KEY FK_87CDD04C3D803374');
        $this->addSql('ALTER TABLE entrega_tarea DROP FOREIGN KEY FK_87CDD04C7EB2C349');
        $this->addSql('DROP INDEX IDX_87CDD04C3D803374 ON entrega_tarea');
        $this->addSql('DROP INDEX IDX_87CDD04C7EB2C349 ON entrega_tarea');
        $this->addSql('ALTER TABLE entrega_tarea DROP id_tarea_id, DROP id_usuario_id');
        $this->addSql('ALTER TABLE mensaje_foro DROP FOREIGN KEY FK_A6946C222B62FA86');
        $this->addSql('ALTER TABLE mensaje_foro DROP FOREIGN KEY FK_A6946C227EB2C349');
        $this->addSql('DROP INDEX IDX_A6946C222B62FA86 ON mensaje_foro');
        $this->addSql('DROP INDEX IDX_A6946C227EB2C349 ON mensaje_foro');
        $this->addSql('ALTER TABLE mensaje_foro DROP id_foro_id, DROP id_usuario_id, DROP contenido');
        $this->addSql('ALTER TABLE opcion_pregunta DROP FOREIGN KEY FK_47E330D129B74DE9');
        $this->addSql('DROP INDEX IDX_47E330D129B74DE9 ON opcion_pregunta');
        $this->addSql('ALTER TABLE opcion_pregunta DROP id_pregunta_id');
        $this->addSql('ALTER TABLE tarea DROP FOREIGN KEY FK_3CA05366D710A68A');
        $this->addSql('DROP INDEX IDX_3CA05366D710A68A ON tarea');
        $this->addSql('ALTER TABLE tarea DROP id_curso_id');
    }
}
