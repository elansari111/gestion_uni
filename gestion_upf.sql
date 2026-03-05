-- ============================================================
-- Base de données : gestion_upf
-- Application de Gestion UPF — TP Final PHP Procédural
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_upf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_upf;

-- Table : filieres
CREATE TABLE IF NOT EXISTS filieres (
    CodeF       VARCHAR(10)  NOT NULL,
    IntituleF   VARCHAR(100) NOT NULL,
    responsable VARCHAR(100) DEFAULT NULL,
    nbPlaces    INT          DEFAULT NULL,
    created_at  DATETIME     NOT NULL,
    PRIMARY KEY (CodeF)
) ENGINE=InnoDB;

-- Table : etudiants
CREATE TABLE IF NOT EXISTS etudiants (
    Code            VARCHAR(10)  NOT NULL,
    Nom             VARCHAR(50)  NOT NULL,
    Prenom          VARCHAR(50)  NOT NULL,
    Filiere         VARCHAR(10)  DEFAULT NULL,
    Note            DECIMAL(4,2) DEFAULT NULL,
    Photo           VARCHAR(255) DEFAULT NULL,
    date_naissance  DATE         DEFAULT NULL,
    email           VARCHAR(100) DEFAULT NULL,
    telephone       VARCHAR(20)  DEFAULT NULL,
    created_at      DATETIME     NOT NULL,
    PRIMARY KEY (Code),
    UNIQUE KEY uq_email (email),
    CONSTRAINT fk_etudiant_filiere FOREIGN KEY (Filiere) REFERENCES filieres(CodeF) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table : utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id                  INT          NOT NULL AUTO_INCREMENT,
    login               VARCHAR(50)  NOT NULL,
    password            VARCHAR(255) NOT NULL,
    role                ENUM('admin','user') NOT NULL,
    etudiant_id         VARCHAR(10)  DEFAULT NULL,
    derniere_connexion  DATETIME     DEFAULT NULL,
    created_at          DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_login (login),
    CONSTRAINT fk_utilisateur_etudiant FOREIGN KEY (etudiant_id) REFERENCES etudiants(Code) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table : documents
CREATE TABLE IF NOT EXISTS documents (
    id          INT          NOT NULL AUTO_INCREMENT,
    etudiant_id VARCHAR(10)  NOT NULL,
    type_doc    ENUM('releve_notes','attestation','autre') NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin      VARCHAR(255) NOT NULL,
    taille      INT          NOT NULL,
    mime_type   VARCHAR(100) NOT NULL,
    uploaded_by INT          NOT NULL,
    uploaded_at DATETIME     NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_doc_etudiant FOREIGN KEY (etudiant_id) REFERENCES etudiants(Code) ON DELETE CASCADE,
    CONSTRAINT fk_doc_admin    FOREIGN KEY (uploaded_by) REFERENCES utilisateurs(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- Données de test
-- ============================================================

INSERT INTO filieres (CodeF, IntituleF, responsable, nbPlaces, created_at) VALUES
('GINFO',  'Génie Informatique',        'Pr. Kzadri',    60, NOW()),
('GINDU',  'Génie Industriel',          'Pr. Benali',    40, NOW()),
('GMATH',  'Génie Mathématiques',       'Pr. Chraibi',   30, NOW()),
('GELE',   'Génie Électronique',        'Pr. Mansouri',  35, NOW());

INSERT INTO etudiants (Code, Nom, Prenom, Filiere, Note, date_naissance, email, telephone, created_at) VALUES
('E001', 'Alami',    'Youssef',  'GINFO', 14.50, '2002-05-15', 'y.alami@upf.ma',    '0612345678', NOW()),
('E002', 'Bennani',  'Sara',     'GINFO', 17.00, '2001-11-20', 's.bennani@upf.ma',  '0623456789', NOW()),
('E003', 'Cherkaoui','Omar',     'GINFO',  8.75, '2002-03-10', 'o.cherkaoui@upf.ma','0634567890', NOW()),
('E004', 'Idrissi',  'Fatima',   'GINDU', 12.00, '2001-07-25', 'f.idrissi@upf.ma',  '0645678901', NOW()),
('E005', 'Zouiten',  'Hamza',    'GINDU', NULL,  '2002-09-05', 'h.zouiten@upf.ma',  '0656789012', NOW()),
('E006', 'Lahlou',   'Nadia',    'GMATH', 15.50, '2001-04-18', 'n.lahlou@upf.ma',   '0667890123', NOW()),
('E007', 'Tahiri',   'Amine',    'GELE',  10.25, '2002-01-30', 'a.tahiri@upf.ma',   '0678901234', NOW()),
('E008', 'Fassi',    'Kenza',    'GINFO', 19.00, '2001-12-12', 'k.fassi@upf.ma',    '0689012345', NOW());

-- Mots de passe : admin123 / user123 — générés avec password_hash()
-- IMPORTANT : Exécuter ce script PHP une fois pour obtenir les hachés :
--   echo password_hash('admin123', PASSWORD_DEFAULT);
--   echo password_hash('user123', PASSWORD_DEFAULT);
-- Puis remplacer les valeurs ci-dessous.

INSERT INTO utilisateurs (login, password, role, etudiant_id, created_at) VALUES
('admin',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL,   NOW()),
('alami25',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',  'E001', NOW()),
('bennani25','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',  'E002', NOW()),
('cherkaoui25','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user', 'E003', NOW());
-- Note : Le hash ci-dessus correspond au mot de passe "password" (Laravel default).
-- Remplacer par vos propres hachés avant utilisation en production.
