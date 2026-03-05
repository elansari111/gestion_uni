<?php
/**
 * user/profil.php — Espace personnel de l'étudiant
 */
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';

$pdo = getConnexion();

$stmt = $pdo->prepare('
    SELECT e.*, f.IntituleF FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.Code = ?
');
$stmt->execute([$_SESSION['etudiant_id']]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    // Compte sans étudiant associé
    header('Location: ../logout.php');
    exit();
}

$ip         = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$page_title = 'Mon Profil';
$css_depth  = '../assets/';
$nav_links  = [
    ['href'=>'profil.php',              'label'=>'👤 Mon Profil', 'active'=>true],
    ['href'=>'notes.php',               'label'=>'📊 Mes Notes'],
    ['href'=>'documents.php',           'label'=>'📄 Mes Documents'],
    ['href'=>'changer_password.php',    'label'=>'🔑 Mot de passe'],
    ['href'=>'../logout.php',           'label'=>'⏻ Déconnexion'],
];
require_once '../includes/header.php';
?>

<div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start;">

<!-- Photo + infos rapides -->
<div class="card" style="text-align:center;">
    <?php if ($etudiant['Photo'] && file_exists('../uploads/photos/' . $etudiant['Photo'])): ?>
        <img src="../uploads/photos/<?= htmlspecialchars($etudiant['Photo']) ?>"
             class="photo-profil" style="width:130px;height:130px;margin-bottom:12px;" alt="Photo">
    <?php else: ?>
        <div style="width:130px;height:130px;border-radius:50%;background:#e8ecff;display:flex;align-items:center;justify-content:center;font-size:3.5rem;margin:0 auto 12px;">👤</div>
    <?php endif; ?>
    <h3 style="color:#294898;"><?= htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']) ?></h3>
    <p style="color:#C72C82;font-weight:600;"><?= htmlspecialchars($etudiant['IntituleF'] ?? '—') ?></p>
    <p style="color:#888;font-size:.85rem;"><?= htmlspecialchars($etudiant['Code']) ?></p>
</div>

<!-- Détails -->
<div>
    <div class="card">
        <h2>👤 Mes Informations Personnelles</h2>
        <table style="width:100%;font-size:.93rem;">
            <tr><td style="color:#888;padding:8px 0;width:35%;border-bottom:1px solid #f0f0f0;">Code étudiant</td>
                <td style="border-bottom:1px solid #f0f0f0;"><strong><?= htmlspecialchars($etudiant['Code']) ?></strong></td></tr>
            <tr><td style="color:#888;padding:8px 0;border-bottom:1px solid #f0f0f0;">Nom complet</td>
                <td style="border-bottom:1px solid #f0f0f0;"><?= htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']) ?></td></tr>
            <tr><td style="color:#888;padding:8px 0;border-bottom:1px solid #f0f0f0;">Filière</td>
                <td style="border-bottom:1px solid #f0f0f0;"><?= htmlspecialchars($etudiant['IntituleF'] ?? '—') ?></td></tr>
            <tr><td style="color:#888;padding:8px 0;border-bottom:1px solid #f0f0f0;">Date de naissance</td>
                <td style="border-bottom:1px solid #f0f0f0;"><?= $etudiant['date_naissance'] ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : '—' ?></td></tr>
            <tr><td style="color:#888;padding:8px 0;border-bottom:1px solid #f0f0f0;">Email</td>
                <td style="border-bottom:1px solid #f0f0f0;"><?= htmlspecialchars($etudiant['email'] ?? '—') ?></td></tr>
            <tr><td style="color:#888;padding:8px 0;">Téléphone</td>
                <td><?= htmlspecialchars($etudiant['telephone'] ?? '—') ?></td></tr>
        </table>
    </div>

    <div class="card">
        <h2>🖥️ Informations de Connexion</h2>
        <p><strong>Connecté depuis :</strong> <?= htmlspecialchars($_SESSION['heure_connexion']) ?></p>
        <p style="margin-top:8px;"><strong>Adresse IP :</strong> <code><?= htmlspecialchars($ip) ?></code></p>
        <p style="margin-top:8px;"><strong>Navigateur :</strong> <span style="font-size:.82rem;color:#666;"><?= htmlspecialchars(substr($user_agent, 0, 80)) ?>...</span></p>
        <div style="margin-top:14px;display:flex;gap:10px;">
            <a href="notes.php" class="btn btn-primary btn-sm">📊 Voir mes notes</a>
            <a href="changer_password.php" class="btn btn-outline btn-sm">🔑 Changer mot de passe</a>
        </div>
    </div>
</div>

</div>

<?php require_once '../includes/footer.php'; ?>
