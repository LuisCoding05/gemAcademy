<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250322220736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE curso (id INT AUTO_INCREMENT NOT NULL, imagen_id INT DEFAULT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, fecha_creacion DATETIME NOT NULL, INDEX IDX_CA3B40EC763C8AA7 (imagen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entrega_tarea (id INT AUTO_INCREMENT NOT NULL, archivo_url VARCHAR(255) DEFAULT NULL, fecha_entrega DATETIME NOT NULL, calificacion NUMERIC(4, 2) DEFAULT NULL, puntos_obtenidos INT DEFAULT NULL, comentario_profesor VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE foro (id INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, fecha_creacion DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imagen (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logro (id INT AUTO_INCREMENT NOT NULL, imagen_id INT DEFAULT NULL, titulo VARCHAR(100) NOT NULL, motivo VARCHAR(255) NOT NULL, puntos_otorgados INT DEFAULT NULL, INDEX IDX_7439301C763C8AA7 (imagen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mensaje_foro (id INT AUTO_INCREMENT NOT NULL, id_mensaje_padre_id INT DEFAULT NULL, fecha_publicacion DATETIME NOT NULL, INDEX IDX_A6946C22914C7CA8 (id_mensaje_padre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nivel (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, puntos_requeridos INT NOT NULL, descripcion VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE opcion_pregunta (id INT AUTO_INCREMENT NOT NULL, texto VARCHAR(255) NOT NULL, es_correcta TINYINT(1) NOT NULL, retroalimentacion VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pregunta_quizz (id INT AUTO_INCREMENT NOT NULL, id_quizz_id INT NOT NULL, pregunta VARCHAR(255) NOT NULL, puntos INT NOT NULL, orden INT DEFAULT NULL, INDEX IDX_EEF58D3DEA48A758 (id_quizz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quizz (id INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, fecha_publicacion DATETIME NOT NULL, fecha_limite DATETIME NOT NULL, tiempo_limite INT NOT NULL, puntos_totales INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarea (id INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, archivo_url VARCHAR(255) DEFAULT NULL, fecha_publicacion DATETIME NOT NULL, fecha_limite DATETIME NOT NULL, puntos_maximos INT NOT NULL, es_obligatoria TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipo_archivo (id INT AUTO_INCREMENT NOT NULL, extension VARCHAR(25) NOT NULL, descripcion VARCHAR(255) NOT NULL, permitido_material TINYINT(1) NOT NULL, permitido_tarea TINYINT(1) NOT NULL, max_tamano_mb INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, imagen_id INT DEFAULT NULL, username VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, ban TINYINT(1) NOT NULL, fecha_registro DATETIME NOT NULL, ultima_conexion DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_2265B05DF85E0677 (username), UNIQUE INDEX UNIQ_2265B05DE7927C74 (email), INDEX IDX_2265B05D763C8AA7 (imagen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_curso (id INT AUTO_INCREMENT NOT NULL, id_usuario_id INT NOT NULL, id_curso_id INT NOT NULL, fecha_inscripcion DATETIME NOT NULL, materiales_completados INT NOT NULL, materiales_totales INT NOT NULL, tareas_completadas INT NOT NULL, tareas_totales INT NOT NULL, quizzes_completados INT NOT NULL, quizzes_totales INT NOT NULL, porcentaje_completado NUMERIC(5, 2) NOT NULL, ultima_actualizacion DATETIME NOT NULL, INDEX IDX_D7E52AF27EB2C349 (id_usuario_id), INDEX IDX_D7E52AF2D710A68A (id_curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_logro (id INT AUTO_INCREMENT NOT NULL, id_usuario_id INT NOT NULL, id_logro_id INT NOT NULL, fecha_obtencion DATETIME NOT NULL, INDEX IDX_69E75A027EB2C349 (id_usuario_id), INDEX IDX_69E75A02A3E5CD3E (id_logro_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CA3B40EC763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id)');
        $this->addSql('ALTER TABLE logro ADD CONSTRAINT FK_7439301C763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id)');
        $this->addSql('ALTER TABLE mensaje_foro ADD CONSTRAINT FK_A6946C22914C7CA8 FOREIGN KEY (id_mensaje_padre_id) REFERENCES mensaje_foro (id)');
        $this->addSql('ALTER TABLE pregunta_quizz ADD CONSTRAINT FK_EEF58D3DEA48A758 FOREIGN KEY (id_quizz_id) REFERENCES quizz (id)');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05D763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id)');
        $this->addSql('ALTER TABLE usuario_curso ADD CONSTRAINT FK_D7E52AF27EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario_curso ADD CONSTRAINT FK_D7E52AF2D710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE usuario_logro ADD CONSTRAINT FK_69E75A027EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario_logro ADD CONSTRAINT FK_69E75A02A3E5CD3E FOREIGN KEY (id_logro_id) REFERENCES logro (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE curso DROP FOREIGN KEY FK_CA3B40EC763C8AA7');
        $this->addSql('ALTER TABLE logro DROP FOREIGN KEY FK_7439301C763C8AA7');
        $this->addSql('ALTER TABLE mensaje_foro DROP FOREIGN KEY FK_A6946C22914C7CA8');
        $this->addSql('ALTER TABLE pregunta_quizz DROP FOREIGN KEY FK_EEF58D3DEA48A758');
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05D763C8AA7');
        $this->addSql('ALTER TABLE usuario_curso DROP FOREIGN KEY FK_D7E52AF27EB2C349');
        $this->addSql('ALTER TABLE usuario_curso DROP FOREIGN KEY FK_D7E52AF2D710A68A');
        $this->addSql('ALTER TABLE usuario_logro DROP FOREIGN KEY FK_69E75A027EB2C349');
        $this->addSql('ALTER TABLE usuario_logro DROP FOREIGN KEY FK_69E75A02A3E5CD3E');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE entrega_tarea');
        $this->addSql('DROP TABLE foro');
        $this->addSql('DROP TABLE imagen');
        $this->addSql('DROP TABLE logro');
        $this->addSql('DROP TABLE mensaje_foro');
        $this->addSql('DROP TABLE nivel');
        $this->addSql('DROP TABLE opcion_pregunta');
        $this->addSql('DROP TABLE pregunta_quizz');
        $this->addSql('DROP TABLE quizz');
        $this->addSql('DROP TABLE tarea');
        $this->addSql('DROP TABLE tipo_archivo');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE usuario_curso');
        $this->addSql('DROP TABLE usuario_logro');
    }
}
