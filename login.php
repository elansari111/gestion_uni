<?php
/**
 * login.php — Page de connexion
 */
session_start();

// Rediriger si déjà connecté
if (!empty($_SESSION['user_id'])) {
    $redirect = $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/profil.php';
    header('Location: ' . $redirect);
    exit();
}

$erreur = $_GET['erreur'] ?? '';
$msg    = $_GET['msg']    ?? '';

// Récupérer le dernier login via cookie
$last_login = isset($_COOKIE['last_login']) ? htmlspecialchars($_COOKIE['last_login']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Gestion UPF</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <div class="upf-logo">
            <span>U<em>P</em>F</span>
        </div>
        <h2>Connexion</h2>
        <p>Application de Gestion des Étudiants</p>

        <?php if ($erreur === '1'): ?>
            <div class="alert alert-danger">Login ou mot de passe incorrect.</div>
        <?php elseif ($erreur === 'acces'): ?>
            <div class="alert alert-danger">Accès non autorisé. Veuillez vous connecter.</div>
        <?php endif; ?>

        <?php if ($msg === 'deconnecte'): ?>
            <div class="alert alert-success">Vous avez été déconnecté avec succès.</div>
        <?php endif; ?>

        <form action="login_traitement.php" method="POST">
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" class="form-control"
                       value="<?= $last_login ?>" required autofocus
                       placeholder="Votre identifiant">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control"
                       required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:11px;font-size:1rem;">
                Se connecter
            </button>
        </form>

        <?php if ($last_login): ?>
            <p style="text-align:center;font-size:.8rem;color:#999;margin-top:12px;">
                Dernier login utilisé : <strong><?= $last_login ?></strong>
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
