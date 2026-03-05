<?php
/**
 * config/database.php
 * Connexion PDO à la base de données gestion_upf
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_upf');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ── Création automatique des dossiers uploads ──────────────────────────────
// __DIR__ = .../gestion_upf/config  →  dirname(__DIR__) = .../gestion_upf
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
// ──────────────────────────────────────────────────────────────────────────

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
