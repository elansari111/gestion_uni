<?php
/**
 * admin/etudiants/supprimer.php — Confirmation + suppression d'un étudiant (transaction PDO)
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

$erreur = '';

// Traitement de la suppression confirmée
if (isset($_POST['confirmer'])) {
    try {
        $pdo->beginTransaction();

        // 1. Récupérer les documents de l'étudiant pour supprimer les fichiers physiques
        $docs = $pdo->prepare('SELECT chemin FROM documents WHERE etudiant_id = ?');
        $docs->execute([$code]);
        foreach ($docs->fetchAll() as $doc) {
            $path = '../../' . $doc['chemin'];
            if (file_exists($path)) { unlink($path); }
        }

        // 2. Supprimer les documents en BDD (CASCADE fait cela automatiquement, mais explicite)
        $delDocs = $pdo->prepare('DELETE FROM documents WHERE etudiant_id = ?');
        $delDocs->execute([$code]);

        // 3. Supprimer la photo physique
        if ($etudiant['Photo'] && file_exists('../../uploads/photos/' . $etudiant['Photo'])) {
            unlink('../../uploads/photos/' . $etudiant['Photo']);
        }

        // 4. Supprimer le compte utilisateur lié
        $delUser = $pdo->prepare('DELETE FROM utilisateurs WHERE etudiant_id = ?');
        $delUser->execute([$code]);

        // 5. Supprimer l'étudiant
        $delEtu = $pdo->prepare('DELETE FROM etudiants WHERE Code = ?');
        $delEtu->execute([$code]);

        $pdo->commit();
        header('Location: liste.php?msg=supprime');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $erreur = 'Erreur lors de la suppression : ' . htmlspecialchars($e->getMessage());
    }
}

$page_title = 'Supprimer ' . $etudiant['Prenom'] . ' ' . $etudiant['Nom'];
$css_depth  = '../../assets/';
$nav_links  = [
    ['href'=>'../dashboard.php',      'label'=>'📊 Tableau de bord'],
    ['href'=>'liste.php',             'label'=>'🎓 Étudiants'],
    ['href'=>'../filieres/liste.php', 'label'=>'📚 Filières'],
    ['href'=>'../../logout.php',      'label'=>'⏻ Déconnexion'],
];
require_once '../../includes/header.php';
?>

<div class="card" style="max-width:500px;margin:0 auto;">
    <h2>🗑️ Confirmer la suppression</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <div class="alert alert-warning">
        ⚠️ Cette action est <strong>irréversible</strong>. Toutes les données associées
        (photo, documents, compte utilisateur) seront définitivement supprimées.
    </div>

    <div style="background:#f8f9fa;padding:16px;border-radius:8px;margin-bottom:20px;">
        <p><strong>Code :</strong> <?= htmlspecialchars($etudiant['Code']) ?></p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']) ?></p>
        <p><strong>Filière :</strong> <?= htmlspecialchars($etudiant['Filiere'] ?? '—') ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($etudiant['email'] ?? '—') ?></p>
    </div>

    <form method="POST">
        <div style="display:flex;gap:10px;">
            <button type="submit" name="confirmer" class="btn btn-danger">
                🗑️ Oui, supprimer définitivement
            </button>
            <a href="liste.php" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
