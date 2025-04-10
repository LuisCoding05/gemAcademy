<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410193514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE curso (id INT AUTO_INCREMENT NOT NULL, imagen_id INT DEFAULT NULL, profesor_id INT NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, fecha_creacion DATETIME NOT NULL, INDEX IDX_CA3B40EC763C8AA7 (imagen_id), INDEX IDX_CA3B40ECE52BD977 (profesor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entrega_tarea (id INT AUTO_INCREMENT NOT NULL, id_tarea_id INT NOT NULL, id_usuario_id INT NOT NULL, archivo_url VARCHAR(255) DEFAULT NULL, fecha_entrega DATETIME NOT NULL, calificacion NUMERIC(4, 2) DEFAULT NULL, puntos_obtenidos INT DEFAULT NULL, comentario_profesor VARCHAR(255) DEFAULT NULL, INDEX IDX_87CDD04C3D803374 (id_tarea_id), INDEX IDX_87CDD04C7EB2C349 (id_usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE foro (id INT AUTO_INCREMENT NOT NULL, curso_id INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, fecha_creacion DATETIME NOT NULL, INDEX IDX_BC869C6387CB4A1F (curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imagen (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intento_quizz (id INT AUTO_INCREMENT NOT NULL, id_quizz_id INT NOT NULL, id_usuario_id INT NOT NULL, fecha_inicio DATETIME NOT NULL, fecha_fin DATETIME NOT NULL, puntuacion_total INT NOT NULL, completado TINYINT(1) NOT NULL, INDEX IDX_B88CE435EA48A758 (id_quizz_id), INDEX IDX_B88CE4357EB2C349 (id_usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, fecha DATETIME NOT NULL, INDEX IDX_8F3F68C5DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logro (id INT AUTO_INCREMENT NOT NULL, imagen_id INT DEFAULT NULL, titulo VARCHAR(100) NOT NULL, motivo VARCHAR(255) NOT NULL, puntos_otorgados INT DEFAULT 0, INDEX IDX_7439301C763C8AA7 (imagen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material (id INT AUTO_INCREMENT NOT NULL, id_curso_id INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, fecha_publicacion DATETIME NOT NULL, orden INT DEFAULT NULL, INDEX IDX_7CBE7595D710A68A (id_curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mensaje_foro (id INT AUTO_INCREMENT NOT NULL, id_mensaje_padre_id INT DEFAULT NULL, id_foro_id INT NOT NULL, id_usuario_id INT NOT NULL, fecha_publicacion DATETIME NOT NULL, contenido VARCHAR(255) NOT NULL, INDEX IDX_A6946C22914C7CA8 (id_mensaje_padre_id), INDEX IDX_A6946C222B62FA86 (id_foro_id), INDEX IDX_A6946C227EB2C349 (id_usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nivel (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, puntos_requeridos INT DEFAULT 0 NOT NULL, descripcion VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE opcion_pregunta (id INT AUTO_INCREMENT NOT NULL, id_pregunta_id INT NOT NULL, texto VARCHAR(255) NOT NULL, es_correcta TINYINT(1) DEFAULT 0 NOT NULL, retroalimentacion VARCHAR(255) DEFAULT NULL, INDEX IDX_47E330D129B74DE9 (id_pregunta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pregunta_quizz (id INT AUTO_INCREMENT NOT NULL, id_quizz_id INT NOT NULL, pregunta VARCHAR(255) NOT NULL, puntos INT DEFAULT 0 NOT NULL, orden INT DEFAULT NULL, INDEX IDX_EEF58D3DEA48A758 (id_quizz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quizz (id INT AUTO_INCREMENT NOT NULL, id_curso_id INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, fecha_publicacion DATETIME NOT NULL, fecha_limite DATETIME NOT NULL, tiempo_limite INT DEFAULT 30 NOT NULL, puntos_totales INT NOT NULL, INDEX IDX_7C77973DD710A68A (id_curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respuesta_quizz (id INT AUTO_INCREMENT NOT NULL, id_intento_id INT NOT NULL, id_pregunta_id INT NOT NULL, respuesta VARCHAR(255) NOT NULL, es_correcta TINYINT(1) NOT NULL, puntos_obtenidos INT DEFAULT 10 NOT NULL, INDEX IDX_61C0D48CCA2EB257 (id_intento_id), INDEX IDX_61C0D48C29B74DE9 (id_pregunta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarea (id INT AUTO_INCREMENT NOT NULL, id_curso_id INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, archivo_url VARCHAR(255) DEFAULT NULL, fecha_publicacion DATETIME NOT NULL, fecha_limite DATETIME NOT NULL, puntos_maximos INT DEFAULT 100 NOT NULL, es_obligatoria TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_3CA05366D710A68A (id_curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipo_archivo (id INT AUTO_INCREMENT NOT NULL, extension VARCHAR(25) NOT NULL, descripcion VARCHAR(255) NOT NULL, permitido_material TINYINT(1) NOT NULL, permitido_tarea TINYINT(1) NOT NULL, max_tamano_mb INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, imagen_id INT DEFAULT NULL, username VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, ban TINYINT(1) DEFAULT 0 NOT NULL, fecha_registro DATETIME NOT NULL, ultima_conexion DATETIME DEFAULT NULL, verificado TINYINT(1) NOT NULL, nombre VARCHAR(150) NOT NULL, apellido VARCHAR(150) NOT NULL, apellido2 VARCHAR(150) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', token_verificacion VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_2265B05DF85E0677 (username), UNIQUE INDEX UNIQ_2265B05DE7927C74 (email), INDEX IDX_2265B05D763C8AA7 (imagen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_curso (id INT AUTO_INCREMENT NOT NULL, id_usuario_id INT NOT NULL, id_curso_id INT NOT NULL, fecha_inscripcion DATETIME NOT NULL, materiales_completados INT NOT NULL, materiales_totales INT NOT NULL, tareas_completadas INT DEFAULT 0 NOT NULL, tareas_totales INT DEFAULT 0 NOT NULL, quizzes_completados INT DEFAULT 0 NOT NULL, quizzes_totales INT DEFAULT 0 NOT NULL, porcentaje_completado NUMERIC(5, 2) DEFAULT \'0\' NOT NULL, ultima_actualizacion DATETIME NOT NULL, INDEX IDX_D7E52AF27EB2C349 (id_usuario_id), INDEX IDX_D7E52AF2D710A68A (id_curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_logro (id INT AUTO_INCREMENT NOT NULL, id_usuario_id INT NOT NULL, id_logro_id INT NOT NULL, fecha_obtencion DATETIME NOT NULL, INDEX IDX_69E75A027EB2C349 (id_usuario_id), INDEX IDX_69E75A02A3E5CD3E (id_logro_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_nivel (id INT AUTO_INCREMENT NOT NULL, id_usuario_id INT NOT NULL, id_nivel_id INT NOT NULL, puntos_siguiente_nivel INT NOT NULL, puntos_actuales INT DEFAULT 0 NOT NULL, fecha_ultimo_nivel DATETIME NOT NULL, INDEX IDX_B7224AD57EB2C349 (id_usuario_id), INDEX IDX_B7224AD58AEFCA3B (id_nivel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CA3B40EC763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id)');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CA3B40ECE52BD977 FOREIGN KEY (profesor_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE entrega_tarea ADD CONSTRAINT FK_87CDD04C3D803374 FOREIGN KEY (id_tarea_id) REFERENCES tarea (id)');
        $this->addSql('ALTER TABLE entrega_tarea ADD CONSTRAINT FK_87CDD04C7EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE foro ADD CONSTRAINT FK_BC869C6387CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE intento_quizz ADD CONSTRAINT FK_B88CE435EA48A758 FOREIGN KEY (id_quizz_id) REFERENCES quizz (id)');
        $this->addSql('ALTER TABLE intento_quizz ADD CONSTRAINT FK_B88CE4357EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE logro ADD CONSTRAINT FK_7439301C763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595D710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE mensaje_foro ADD CONSTRAINT FK_A6946C22914C7CA8 FOREIGN KEY (id_mensaje_padre_id) REFERENCES mensaje_foro (id)');
        $this->addSql('ALTER TABLE mensaje_foro ADD CONSTRAINT FK_A6946C222B62FA86 FOREIGN KEY (id_foro_id) REFERENCES foro (id)');
        $this->addSql('ALTER TABLE mensaje_foro ADD CONSTRAINT FK_A6946C227EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE opcion_pregunta ADD CONSTRAINT FK_47E330D129B74DE9 FOREIGN KEY (id_pregunta_id) REFERENCES pregunta_quizz (id)');
        $this->addSql('ALTER TABLE pregunta_quizz ADD CONSTRAINT FK_EEF58D3DEA48A758 FOREIGN KEY (id_quizz_id) REFERENCES quizz (id)');
        $this->addSql('ALTER TABLE quizz ADD CONSTRAINT FK_7C77973DD710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE respuesta_quizz ADD CONSTRAINT FK_61C0D48CCA2EB257 FOREIGN KEY (id_intento_id) REFERENCES intento_quizz (id)');
        $this->addSql('ALTER TABLE respuesta_quizz ADD CONSTRAINT FK_61C0D48C29B74DE9 FOREIGN KEY (id_pregunta_id) REFERENCES pregunta_quizz (id)');
        $this->addSql('ALTER TABLE tarea ADD CONSTRAINT FK_3CA05366D710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05D763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id)');
        $this->addSql('ALTER TABLE usuario_curso ADD CONSTRAINT FK_D7E52AF27EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario_curso ADD CONSTRAINT FK_D7E52AF2D710A68A FOREIGN KEY (id_curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE usuario_logro ADD CONSTRAINT FK_69E75A027EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario_logro ADD CONSTRAINT FK_69E75A02A3E5CD3E FOREIGN KEY (id_logro_id) REFERENCES logro (id)');
        $this->addSql('ALTER TABLE usuario_nivel ADD CONSTRAINT FK_B7224AD57EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario_nivel ADD CONSTRAINT FK_B7224AD58AEFCA3B FOREIGN KEY (id_nivel_id) REFERENCES nivel (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE curso DROP FOREIGN KEY FK_CA3B40EC763C8AA7');
        $this->addSql('ALTER TABLE curso DROP FOREIGN KEY FK_CA3B40ECE52BD977');
        $this->addSql('ALTER TABLE entrega_tarea DROP FOREIGN KEY FK_87CDD04C3D803374');
        $this->addSql('ALTER TABLE entrega_tarea DROP FOREIGN KEY FK_87CDD04C7EB2C349');
        $this->addSql('ALTER TABLE foro DROP FOREIGN KEY FK_BC869C6387CB4A1F');
        $this->addSql('ALTER TABLE intento_quizz DROP FOREIGN KEY FK_B88CE435EA48A758');
        $this->addSql('ALTER TABLE intento_quizz DROP FOREIGN KEY FK_B88CE4357EB2C349');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5DB38439E');
        $this->addSql('ALTER TABLE logro DROP FOREIGN KEY FK_7439301C763C8AA7');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595D710A68A');
        $this->addSql('ALTER TABLE mensaje_foro DROP FOREIGN KEY FK_A6946C22914C7CA8');
        $this->addSql('ALTER TABLE mensaje_foro DROP FOREIGN KEY FK_A6946C222B62FA86');
        $this->addSql('ALTER TABLE mensaje_foro DROP FOREIGN KEY FK_A6946C227EB2C349');
        $this->addSql('ALTER TABLE opcion_pregunta DROP FOREIGN KEY FK_47E330D129B74DE9');
        $this->addSql('ALTER TABLE pregunta_quizz DROP FOREIGN KEY FK_EEF58D3DEA48A758');
        $this->addSql('ALTER TABLE quizz DROP FOREIGN KEY FK_7C77973DD710A68A');
        $this->addSql('ALTER TABLE respuesta_quizz DROP FOREIGN KEY FK_61C0D48CCA2EB257');
        $this->addSql('ALTER TABLE respuesta_quizz DROP FOREIGN KEY FK_61C0D48C29B74DE9');
        $this->addSql('ALTER TABLE tarea DROP FOREIGN KEY FK_3CA05366D710A68A');
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05D763C8AA7');
        $this->addSql('ALTER TABLE usuario_curso DROP FOREIGN KEY FK_D7E52AF27EB2C349');
        $this->addSql('ALTER TABLE usuario_curso DROP FOREIGN KEY FK_D7E52AF2D710A68A');
        $this->addSql('ALTER TABLE usuario_logro DROP FOREIGN KEY FK_69E75A027EB2C349');
        $this->addSql('ALTER TABLE usuario_logro DROP FOREIGN KEY FK_69E75A02A3E5CD3E');
        $this->addSql('ALTER TABLE usuario_nivel DROP FOREIGN KEY FK_B7224AD57EB2C349');
        $this->addSql('ALTER TABLE usuario_nivel DROP FOREIGN KEY FK_B7224AD58AEFCA3B');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE entrega_tarea');
        $this->addSql('DROP TABLE foro');
        $this->addSql('DROP TABLE imagen');
        $this->addSql('DROP TABLE intento_quizz');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE logro');
        $this->addSql('DROP TABLE material');
        $this->addSql('DROP TABLE mensaje_foro');
        $this->addSql('DROP TABLE nivel');
        $this->addSql('DROP TABLE opcion_pregunta');
        $this->addSql('DROP TABLE pregunta_quizz');
        $this->addSql('DROP TABLE quizz');
        $this->addSql('DROP TABLE respuesta_quizz');
        $this->addSql('DROP TABLE tarea');
        $this->addSql('DROP TABLE tipo_archivo');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE usuario_curso');
        $this->addSql('DROP TABLE usuario_logro');
        $this->addSql('DROP TABLE usuario_nivel');
    }
}
