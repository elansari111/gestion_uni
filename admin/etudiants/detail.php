<?php
/**
 * admin/etudiants/detail.php — Fiche complète + upload de document PDF
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$pdo  = getConnexion();
$code = trim($_GET['code'] ?? '');
if ($code === '') { header('Location: liste.php'); exit(); }

$stmt = $pdo->prepare('
    SELECT e.*, f.IntituleF FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.Code = ?
');
$stmt->execute([$code]);
$etudiant = $stmt->fetch();
if (!$etudiant) { header('Location: liste.php'); exit(); }

// Documents de l'étudiant
$docs = $pdo->prepare('
    SELECT d.*, u.login AS admin_login FROM documents d
    JOIN utilisateurs u ON d.uploaded_by = u.id
    WHERE d.etudiant_id = ?
    ORDER BY d.uploaded_at DESC
');
$docs->execute([$code]);
$documents = $docs->fetchAll();

// Traitement upload document PDF
$msg_doc = '';
$err_doc = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $f        = $_FILES['document'];
    $type_doc = trim($_POST['type_doc'] ?? 'autre');

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $err_doc = 'Erreur lors de l\'upload (code ' . $f['error'] . ').';
    } elseif ($f['size'] > 5242880) {
        $err_doc = 'Le fichier dépasse 5 Mo.';
    } else {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $err_doc = 'Seuls les fichiers PDF sont acceptés.';
        } else {
            $mime = mime_content_type($f['tmp_name']);
            if ($mime !== 'application/pdf') {
                $err_doc = 'Le type MIME du fichier n\'est pas PDF.';
            } else {
                $filename = 'doc_' . $code . '_' . time() . '.pdf';
                $chemin   = 'uploads/documents/' . $filename;
                $dest     = '../../' . $chemin;
                if (!move_uploaded_file($f['tmp_name'], $dest)) {
                    $err_doc = 'Impossible de déplacer le fichier.';
                } else {
                    $ins = $pdo->prepare('
                        INSERT INTO documents (etudiant_id, type_doc, nom_fichier, chemin, taille, mime_type, uploaded_by, uploaded_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ');
                    $ins->execute([$code, $type_doc, $f['name'], $chemin, $f['size'], $mime, $_SESSION['user_id']]);
                    $msg_doc = 'Document uploadé avec succès.';
                    // Recharger documents
                    $docs->execute([$code]);
                    $documents = $docs->fetchAll();
                }
            }
        }
    }
}

function getMention($note) {
    if ($note === null) return ['En attente','badge-attente'];
    if ($note >= 16) return ['Très Bien','badge-tb'];
    if ($note >= 14) return ['Bien','badge-bien'];
    if ($note >= 12) return ['Assez Bien','badge-ab'];
    if ($note >= 10) return ['Passable','badge-pass'];
    return ['Insuffisant','badge-insuf'];
}
[$mention, $badge] = getMention($etudiant['Note']);

$page_title = 'Fiche — ' . $etudiant['Prenom'] . ' ' . $etudiant['Nom'];
$css_depth  = '../../assets/';
$nav_links  = [
    ['href'=>'../dashboard.php',      'label'=>'📊 Tableau de bord'],
    ['href'=>'liste.php',             'label'=>'🎓 Étudiants'],
    ['href'=>'../filieres/liste.php', 'label'=>'📚 Filières'],
    ['href'=>'../../logout.php',      'label'=>'⏻ Déconnexion'],
];
require_once '../../includes/header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

<!-- Informations étudiant -->
<div class="card">
    <h2>👤 <?= htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']) ?></h2>
    <div style="text-align:center;margin-bottom:20px;">
        <?php if ($etudiant['Photo'] && file_exists('../../uploads/photos/' . $etudiant['Photo'])): ?>
            <img src="../../uploads/photos/<?= htmlspecialchars($etudiant['Photo']) ?>"
                 class="photo-profil" style="width:120px;height:120px;" alt="Photo">
        <?php else: ?>
            <div style="width:120px;height:120px;border-radius:50%;background:#e8ecff;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto;">👤</div>
        <?php endif; ?>
    </div>
    <table style="width:100%;font-size:.92rem;">
        <tr><td style="color:#888;padding:5px 0;width:40%;">Code</td><td><strong><?= htmlspecialchars($etudiant['Code']) ?></strong></td></tr>
        <tr><td style="color:#888;padding:5px 0;">Filière</td><td><?= htmlspecialchars($etudiant['IntituleF'] ?? '—') ?></td></tr>
        <tr><td style="color:#888;padding:5px 0;">Date de naissance</td><td><?= $etudiant['date_naissance'] ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : '—' ?></td></tr>
        <tr><td style="color:#888;padding:5px 0;">Email</td><td><?= htmlspecialchars($etudiant['email'] ?? '—') ?></td></tr>
        <tr><td style="color:#888;padding:5px 0;">Téléphone</td><td><?= htmlspecialchars($etudiant['telephone'] ?? '—') ?></td></tr>
        <tr><td style="color:#888;padding:5px 0;">Note</td>
            <td>
                <?php if ($etudiant['Note'] !== null): ?>
                    <span class="<?= $etudiant['Note'] >= 12 ? 'note-vert' : ($etudiant['Note'] >= 10 ? 'note-orange' : 'note-rouge') ?>">
                        <?= $etudiant['Note'] ?>/20
                    </span>
                <?php else: ?><span style="color:#aaa;">Non évalué</span><?php endif; ?>
            </td>
        </tr>
        <tr><td style="color:#888;padding:5px 0;">Mention</td><td><span class="badge <?= $badge ?>"><?= $mention ?></span></td></tr>
        <tr><td style="color:#888;padding:5px 0;">Ajouté le</td><td><?= date('d/m/Y', strtotime($etudiant['created_at'])) ?></td></tr>
    </table>
    <div style="margin-top:16px;display:flex;gap:8px;">
        <a href="modifier.php?code=<?= urlencode($code) ?>" class="btn btn-warning btn-sm">✏️ Modifier</a>
        <a href="supprimer.php?code=<?= urlencode($code) ?>" class="btn btn-danger btn-sm">🗑️ Supprimer</a>
        <a href="liste.php" class="btn btn-outline btn-sm">← Retour</a>
    </div>
</div>

<!-- Documents -->
<div>
    <!-- Upload document -->
    <div class="card">
        <h2>📤 Uploader un Document PDF</h2>
        <?php if ($msg_doc): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($msg_doc) ?></div>
        <?php endif; ?>
        <?php if ($err_doc): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($err_doc) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Type de document</label>
                <select name="type_doc" class="form-control">
                    <option value="releve_notes">Relevé de notes</option>
                    <option value="attestation">Attestation</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-group">
                <label>Fichier PDF (max 5 Mo)</label>
                <input type="file" name="document" class="form-control" accept=".pdf" required>
            </div>
            <button type="submit" class="btn btn-pink">📤 Uploader</button>
        </form>
    </div>

    <!-- Liste documents -->
    <div class="card">
        <h2>📄 Documents (<?= count($documents) ?>)</h2>
        <?php if (empty($documents)): ?>
            <p class="alert alert-info">Aucun document disponible pour cet étudiant.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Nom</th><th>Type</th><th>Taille</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($documents as $doc): ?>
                <tr>
                    <td><?= htmlspecialchars($doc['nom_fichier']) ?></td>
                    <td><span class="badge badge-info" style="background:#e8ecff;color:#294898;"><?= htmlspecialchars($doc['type_doc']) ?></span></td>
                    <td><?= round($doc['taille'] / 1024) ?> Ko</td>
                    <td><?= date('d/m/Y H:i', strtotime($doc['uploaded_at'])) ?></td>
                    <td>
                        <?php $filepath = '../../' . $doc['chemin']; ?>
                        <?php if (file_exists($filepath)): ?>
                            <a href="../../<?= htmlspecialchars($doc['chemin']) ?>" download="<?= htmlspecialchars($doc['nom_fichier']) ?>" class="btn btn-primary btn-sm">⬇️</a>
                        <?php else: ?>
                            <span style="color:#aaa;font-size:.8rem;">Fichier introuvable</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</div><!-- /grid -->

<?php require_once '../../includes/footer.php'; ?>
