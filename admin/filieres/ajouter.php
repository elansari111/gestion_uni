<?php
/**
 * admin/filieres/ajouter.php — Formulaire d'ajout d'une filière
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$pdo     = getConnexion();
$erreurs = $_SESSION['form_erreurs'] ?? [];
$values  = $_SESSION['form_values']  ?? [];
unset($_SESSION['form_erreurs'], $_SESSION['form_values']);

$page_title = 'Ajouter une filière';
$css_depth  = '../../assets/';
$nav_links  = [
    ['href'=>'../dashboard.php',     'label'=>'📊 Tableau de bord'],
    ['href'=>'../etudiants/liste.php','label'=>'🎓 Étudiants'],
    ['href'=>'liste.php',             'label'=>'📚 Filières'],
    ['href'=>'../../logout.php',      'label'=>'⏻ Déconnexion'],
];
require_once '../../includes/header.php';
?>

<div class="card" style="max-width:600px;">
    <h2>➕ Ajouter une Filière</h2>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $e): ?>
                <div>• <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="ajouter_traitement.php" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Code filière <span style="color:#C72C82">*</span></label>
                <input type="text" name="CodeF" class="form-control" required maxlength="10"
                       placeholder="ex: GINFO"
                       value="<?= htmlspecialchars($values['CodeF'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Nb de places</label>
                <input type="number" name="nbPlaces" class="form-control" min="1"
                       value="<?= htmlspecialchars($values['nbPlaces'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Intitulé complet <span style="color:#C72C82">*</span></label>
            <input type="text" name="IntituleF" class="form-control" required maxlength="100"
                   value="<?= htmlspecialchars($values['IntituleF'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Responsable pédagogique</label>
            <input type="text" name="responsable" class="form-control" maxlength="100"
                   value="<?= htmlspecialchars($values['responsable'] ?? '') ?>">
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            <a href="liste.php" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
