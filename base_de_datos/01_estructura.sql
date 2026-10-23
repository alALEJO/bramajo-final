-- Bramajo - Sistema de Gestión Deportiva Modular
-- BoomeRam Solutions - MySQL 8.0

SET NAMES utf8mb4;

CREATE TABLE usuario (
    id_usuario        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre            VARCHAR(60)  NOT NULL,
    apellido          VARCHAR(60)  NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    password_hash     VARCHAR(255) NOT NULL,
    telefono          VARCHAR(255) NULL,
    rol               ENUM('ADMIN_GENERAL', 'ORGANIZADOR', 'PARTICIPANTE') NOT NULL DEFAULT 'PARTICIPANTE',
    estado            ENUM('ACTIVO', 'SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO',
    intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta   DATETIME NULL,
    fecha_registro    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE modulo_competencia (
    id_modulo   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo      VARCHAR(20)  NOT NULL UNIQUE,
    nombre      VARCHAR(60)  NOT NULL,
    descripcion VARCHAR(200) NOT NULL,
    activo      BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE torneo (
    id_torneo         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_creador        INT UNSIGNED NOT NULL,
    id_modulo         TINYINT UNSIGNED NOT NULL,
    nombre            VARCHAR(120) NOT NULL,
    disciplina        VARCHAR(60)  NOT NULL,
    descripcion       VARCHAR(500) NULL,
    reglas            VARCHAR(500) NULL,
    ubicacion         VARCHAR(120) NULL,
    sede              VARCHAR(120) NULL,
    modalidad         ENUM('INDIVIDUAL', 'EQUIPO') NOT NULL,
    max_participantes SMALLINT UNSIGNED NOT NULL DEFAULT 16,
    cantidad_rondas   TINYINT UNSIGNED NULL,
    puntos_victoria   TINYINT UNSIGNED NOT NULL DEFAULT 3,
    puntos_empate     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    puntos_derrota    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    fecha_inicio      DATE NOT NULL,
    fecha_fin         DATE NULL,
    estado            ENUM('BORRADOR', 'INSCRIPCION', 'EN_CURSO', 'FINALIZADO') NOT NULL DEFAULT 'BORRADOR',
    FOREIGN KEY (id_creador) REFERENCES usuario (id_usuario),
    FOREIGN KEY (id_modulo) REFERENCES modulo_competencia (id_modulo),
    CHECK (max_participantes >= 2),
    CHECK (puntos_victoria > puntos_empate),
    CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio)
);

CREATE TABLE organizador_torneo (
    id_torneo      INT UNSIGNED NOT NULL,
    id_usuario     INT UNSIGNED NOT NULL,
    puede_corregir BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id_torneo, id_usuario),
    FOREIGN KEY (id_torneo) REFERENCES torneo (id_torneo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
);

CREATE TABLE equipo (
    id_equipo  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(80) NOT NULL UNIQUE,
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE integrante_equipo (
    id_equipo  INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_equipo, id_usuario),
    FOREIGN KEY (id_equipo) REFERENCES equipo (id_equipo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
);

-- Un participante es un competidor dentro de un torneo: una persona o un equipo, nunca los dos.
CREATE TABLE participante (
    id_participante   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_torneo         INT UNSIGNED NOT NULL,
    id_usuario        INT UNSIGNED NULL,
    id_equipo         INT UNSIGNED NULL,
    estado            ENUM('PENDIENTE', 'INSCRIPTO', 'RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
    fecha_inscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_torneo, id_usuario),
    UNIQUE (id_torneo, id_equipo),
    FOREIGN KEY (id_torneo) REFERENCES torneo (id_torneo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario),
    FOREIGN KEY (id_equipo) REFERENCES equipo (id_equipo),
    CHECK ((id_usuario IS NULL) <> (id_equipo IS NULL))
);

CREATE TABLE ronda (
    id_ronda  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_torneo INT UNSIGNED NOT NULL,
    numero    TINYINT UNSIGNED NOT NULL,
    nombre    VARCHAR(40) NOT NULL,
    estado    ENUM('ABIERTA', 'CERRADA') NOT NULL DEFAULT 'ABIERTA',
    UNIQUE (id_torneo, numero),
    FOREIGN KEY (id_torneo) REFERENCES torneo (id_torneo) ON DELETE CASCADE
);

-- Si id_visitante es NULL, el local queda libre en esa ronda.
CREATE TABLE enfrentamiento (
    id_enfrentamiento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_ronda          INT UNSIGNED NOT NULL,
    id_local          INT UNSIGNED NOT NULL,
    id_visitante      INT UNSIGNED NULL,
    puntos_local      SMALLINT UNSIGNED NULL,
    puntos_visitante  SMALLINT UNSIGNED NULL,
    fecha_hora        DATETIME NULL,
    FOREIGN KEY (id_ronda) REFERENCES ronda (id_ronda) ON DELETE CASCADE,
    FOREIGN KEY (id_local) REFERENCES participante (id_participante),
    FOREIGN KEY (id_visitante) REFERENCES participante (id_participante)
);

CREATE TABLE auditoria (
    id_auditoria   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT UNSIGNED NULL,
    accion         ENUM('LOGIN', 'LOGIN_FALLIDO', 'ALTA', 'MODIFICACION', 'BAJA') NOT NULL,
    tabla_afectada VARCHAR(40) NOT NULL,
    id_registro    INT UNSIGNED NULL,
    valor_anterior JSON NULL,
    valor_nuevo    JSON NULL,
    ip             VARCHAR(45) NULL,
    fecha_hora     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
);

CREATE INDEX idx_torneo_estado ON torneo (estado);
CREATE INDEX idx_auditoria_fecha ON auditoria (fecha_hora);

INSERT INTO modulo_competencia (codigo, nombre, descripcion) VALUES
    ('LIGA', 'Liga', 'Todos contra todos. Gana quien suma más puntos.'),
    ('ELIMINACION', 'Eliminación directa', 'Quien pierde queda afuera. Si los inscriptos no son potencia de 2, algunos pasan libres.'),
    ('SUIZO', 'Sistema suizo', 'En cada ronda se enfrentan participantes con puntaje parecido, sin repetir rival.');
