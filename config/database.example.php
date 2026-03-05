<?php
/**
 * config/database.example.php
 * ─────────────────────────────────────────────────────────────
 * Copiez ce fichier en database.php et remplissez vos valeurs.
 *   cp config/database.example.php config/database.php
 * ─────────────────────────────────────────────────────────────
 */

define('DB_HOST',    'localhost');   // Hôte MySQL (XAMPP = localhost)
define('DB_NAME',    'gestion_upf');// Nom de la base de données
define('DB_USER',    'root');        // Utilisateur MySQL
define('DB_PASS',    '');            // Mot de passe MySQL (vide par défaut sous XAMPP)
define('DB_CHARSET', 'utf8mb4');

// ── Création automatique des dossiers uploads ──────────────────
$_upf_root = dirname(__DIR__);
$_upf_dirs = [
    $_upf_root . '/uploads',
    $_upf_root . '/uploads/photos',
    $_upf_root . '/uploads/documents',
];
foreach ($_upf_dirs as $_dir) {
    if (!is_dir($_dir)) {
        mkdir($_dir, 0755, true);
    }
}
unset($_upf_root, $_upf_dirs, $_dir);

function getConnexion(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="padding:20px;background:#fee;border:1px solid #c00;font-family:monospace;">
                <strong>Erreur de connexion à la base de données :</strong><br>' .
                htmlspecialchars($e->getMessage()) . '</div>');
        }
    }
    return $pdo;
}
