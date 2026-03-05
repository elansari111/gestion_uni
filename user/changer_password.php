<?php
/**
 * user/changer_password.php — Formulaire de changement de mot de passe
 */
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';

$erreurs = $_SESSION['form_erreurs'] ?? [];
$success = $_SESSION['form_success'] ?? '';
unset($_SESSION['form_erreurs'], $_SESSION['form_success']);

$page_title = 'Changer mon mot de passe';
$css_depth  = '../assets/';
$nav_links  = [
    ['href'=>'profil.php',              'label'=>'👤 Mon Profil'],
    ['href'=>'notes.php',               'label'=>'📊 Mes Notes'],
    ['href'=>'documents.php',           'label'=>'📄 Mes Documents'],
    ['href'=>'changer_password.php',    'label'=>'🔑 Mot de passe', 'active'=>true],
    ['href'=>'../logout.php',           'label'=>'⏻ Déconnexion'],
];
require_once '../includes/header.php';
?>

<div class="card" style="max-width:480px;margin:0 auto;">
    <h2>🔑 Changer mon Mot de Passe</h2>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $e): ?>
                <div>• <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="changer_password_traitement.php" method="POST">
        <div class="form-group">
            <label>Mot de passe actuel <span style="color:#C72C82">*</span></label>
            <input type="password" name="mdp_actuel" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe <span style="color:#C72C82">*</span></label>
            <input type="password" name="mdp_nouveau" class="form-control" required minlength="8">
            <small style="color:#888;">Minimum 8 caractères.</small>
        </div>
        <div class="form-group">
            <label>Confirmer le nouveau mot de passe <span style="color:#C72C82">*</span></label>
            <input type="password" name="mdp_confirmation" class="form-control" required>
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
            <button type="submit" class="btn btn-primary">💾 Changer le mot de passe</button>
            <a href="profil.php" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
