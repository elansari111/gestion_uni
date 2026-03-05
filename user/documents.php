<?php
/**
 * user/documents.php — Documents PDF disponibles pour l'étudiant
 */
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';

$pdo = getConnexion();

$stmt = $pdo->prepare('
    SELECT d.*, u.login AS admin_login FROM documents d
    JOIN utilisateurs u ON d.uploaded_by = u.id
    WHERE d.etudiant_id = ?
    ORDER BY d.uploaded_at DESC
');
$stmt->execute([$_SESSION['etudiant_id']]);
$documents = $stmt->fetchAll();

$type_labels = [
    'releve_notes' => 'Relevé de notes',
    'attestation'  => 'Attestation',
    'autre'        => 'Autre document',
];

$page_title = 'Mes Documents';
$css_depth  = '../assets/';
$nav_links  = [
    ['href'=>'profil.php',           'label'=>'👤 Mon Profil'],
    ['href'=>'notes.php',            'label'=>'📊 Mes Notes'],
    ['href'=>'documents.php',        'label'=>'📄 Mes Documents', 'active'=>true],
    ['href'=>'changer_password.php', 'label'=>'🔑 Mot de passe'],
    ['href'=>'../logout.php',        'label'=>'⏻ Déconnexion'],
];
require_once '../includes/header.php';
?>

<div class="card">
    <h2>📄 Mes Documents Disponibles</h2>

    <?php if (empty($documents)): ?>
        <div class="alert alert-info">
            ℹ️ Aucun document n'est disponible pour vous pour le moment.
            Contactez l'administration pour obtenir vos documents.
        </div>
    <?php else: ?>
        <p style="color:#666;font-size:.88rem;margin-bottom:16px;"><?= count($documents) ?> document(s) disponible(s)</p>
        <table>
            <thead>
                <tr><th>Nom du fichier</th><th>Type</th><th>Taille</th><th>Date de dépôt</th><th>Télécharger</th></tr>
            </thead>
            <tbody>
            <?php foreach ($documents as $doc): ?>
                <tr>
                    <td>
                        <span style="font-size:1.3rem;">📄</span>
                        <?= htmlspecialchars($doc['nom_fichier']) ?>
                    </td>
                    <td>
                        <span class="badge" style="background:#e8ecff;color:#294898;">
                            <?= htmlspecialchars($type_labels[$doc['type_doc']] ?? $doc['type_doc']) ?>
                        </span>
                    </td>
                    <td><?= round($doc['taille'] / 1024) ?> Ko</td>
                    <td><?= date('d/m/Y à H:i', strtotime($doc['uploaded_at'])) ?></td>
                    <td>
                        <?php $filepath = '../' . $doc['chemin']; ?>
                        <?php if (file_exists($filepath)): ?>
                            <a href="../<?= htmlspecialchars($doc['chemin']) ?>"
                               download="<?= htmlspecialchars($doc['nom_fichier']) ?>"
                               class="btn btn-primary btn-sm">
                                ⬇️ Télécharger
                            </a>
                        <?php else: ?>
                            <span style="color:#aaa;font-size:.82rem;">Fichier introuvable</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
