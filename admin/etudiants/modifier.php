<?php
/**
 * admin/etudiants/modifier.php — Formulaire de modification d'un étudiant
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$pdo  = getConnexion();
$code = trim($_GET['code'] ?? '');

if ($code === '') { header('Location: liste.php'); exit(); }

$stmt = $pdo->prepare('SELECT * FROM etudiants WHERE Code = ?');
$stmt->execute([$code]);
$etudiant = $stmt->fetch();
if (!$etudiant) { header('Location: liste.php'); exit(); }

$filieres = $pdo->query('SELECT CodeF, IntituleF FROM filieres ORDER BY IntituleF')->fetchAll();

$erreurs = $_SESSION['form_erreurs'] ?? [];
$values  = $_SESSION['form_values']  ?? $etudiant;
unset($_SESSION['form_erreurs'], $_SESSION['form_values']);

$page_title = 'Modifier ' . $etudiant['Prenom'] . ' ' . $etudiant['Nom'];
$css_depth  = '../../assets/';
$nav_links  = [
    ['href'=>'../dashboard.php',      'label'=>'📊 Tableau de bord'],
    ['href'=>'liste.php',             'label'=>'🎓 Étudiants'],
    ['href'=>'../filieres/liste.php', 'label'=>'📚 Filières'],
    ['href'=>'../../logout.php',      'label'=>'⏻ Déconnexion'],
];
require_once '../../includes/header.php';
?>

<div class="card">
    <h2>✏️ Modifier l'étudiant : <?= htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']) ?></h2>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $err): ?>
                <div>• <?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="modifier_traitement.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="Code" value="<?= htmlspecialchars($etudiant['Code']) ?>">

        <div class="form-row">
            <div class="form-group">
                <label>Code étudiant</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($etudiant['Code']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Filière</label>
                <select name="Filiere" class="form-control">
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($filieres as $f): ?>
                    <option value="<?= htmlspecialchars($f['CodeF']) ?>"
                        <?= ($values['Filiere'] ?? '') === $f['CodeF'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['IntituleF']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Nom <span style="color:#C72C82">*</span></label>
                <input type="text" name="Nom" class="form-control" required maxlength="50"
                       value="<?= htmlspecialchars($values['Nom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Prénom <span style="color:#C72C82">*</span></label>
                <input type="text" name="Prenom" class="form-control" required maxlength="50"
                       value="<?= htmlspecialchars($values['Prenom'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Note /20</label>
                <input type="number" name="Note" class="form-control" min="0" max="20" step="0.01"
                       placeholder="Laisser vide si non évalué"
                       value="<?= htmlspecialchars($values['Note'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <input type="date" name="date_naissance" class="form-control"
                       value="<?= htmlspecialchars($values['date_naissance'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" maxlength="100"
                       value="<?= htmlspecialchars($values['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone" class="form-control" maxlength="20"
                       value="<?= htmlspecialchars($values['telephone'] ?? '') ?>">
            </div>
        </div>

        <!-- Photo actuelle -->
        <div class="form-group">
            <label>Photo actuelle</label><br>
            <?php if ($etudiant['Photo'] && file_exists('../../uploads/photos/' . $etudiant['Photo'])): ?>
                <img src="../../uploads/photos/<?= htmlspecialchars($etudiant['Photo']) ?>"
                     class="photo-profil" style="margin-bottom:8px;" alt="Photo actuelle">
                <br>
            <?php else: ?>
                <span style="color:#aaa;">Aucune photo</span><br>
            <?php endif; ?>
            <label style="margin-top:8px;">Nouvelle photo (JPG, JPEG, PNG — max 2 Mo)</label>
            <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
            <small style="color:#888;">Si une nouvelle photo est sélectionnée, l'ancienne sera remplacée.</small>
        </div>

        <div style="display:flex;gap:10px;margin-top:10px;">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            <a href="liste.php" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
