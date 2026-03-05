<?php
/**
 * admin/etudiants/modifier_traitement.php — Traitement modification étudiant
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: liste.php');
    exit();
}

$pdo  = getConnexion();
$code = strtoupper(trim($_POST['Code'] ?? ''));
if ($code === '') { header('Location: liste.php'); exit(); }

// Récupérer l'étudiant actuel
$stmt = $pdo->prepare('SELECT * FROM etudiants WHERE Code = ?');
$stmt->execute([$code]);
$actuel = $stmt->fetch();
if (!$actuel) { header('Location: liste.php'); exit(); }

$nom            = trim($_POST['Nom']            ?? '');
$prenom         = trim($_POST['Prenom']         ?? '');
$filiere        = trim($_POST['Filiere']        ?? '') ?: null;
$note_raw       = trim($_POST['Note']           ?? '');
$note           = $note_raw !== '' ? (float)$note_raw : null;
$date_naissance = trim($_POST['date_naissance'] ?? '') ?: null;
$email          = trim($_POST['email']          ?? '') ?: null;
$telephone      = trim($_POST['telephone']      ?? '') ?: null;

$erreurs = [];
if ($nom    === '') $erreurs[] = 'Le nom est obligatoire.';
if ($prenom === '') $erreurs[] = 'Le prénom est obligatoire.';
if ($note !== null && ($note < 0 || $note > 20)) $erreurs[] = 'La note doit être entre 0 et 20.';

// Unicité email (sauf si c'est le même étudiant)
if ($email !== null && $email !== '') {
    $chk = $pdo->prepare('SELECT Code FROM etudiants WHERE email = ? AND Code != ?');
    $chk->execute([$email, $code]);
    if ($chk->fetch()) $erreurs[] = "L'email «$email» est déjà utilisé par un autre étudiant.";
}

// Upload photo
$photo_filename = $actuel['Photo'];
if (!empty($_FILES['photo']['name'])) {
    $f = $_FILES['photo'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $erreurs[] = 'Erreur upload photo (code ' . $f['error'] . ').';
    } elseif ($f['size'] > 2097152) {
        $erreurs[] = 'La photo dépasse 2 Mo.';
    } else {
        $ext_allowed = ['jpg','jpeg','png'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ext_allowed)) {
            $erreurs[] = 'Extension de photo non autorisée.';
        } else {
            $mime = mime_content_type($f['tmp_name']);
            if (!in_array($mime, ['image/jpeg','image/png'])) {
                $erreurs[] = 'Type MIME invalide pour la photo.';
            } else {
                // Supprimer l'ancienne photo
                if ($actuel['Photo'] && file_exists('../../uploads/photos/' . $actuel['Photo'])) {
                    unlink('../../uploads/photos/' . $actuel['Photo']);
                }
                $photo_filename = 'photo_' . $code . '.' . $ext;
                $dest = '../../uploads/photos/' . $photo_filename;
                if (!move_uploaded_file($f['tmp_name'], $dest)) {
                    $erreurs[] = 'Impossible de déplacer la photo.';
                    $photo_filename = $actuel['Photo'];
                }
            }
        }
    }
}

if (!empty($erreurs)) {
    $_SESSION['form_erreurs'] = $erreurs;
    $_SESSION['form_values']  = $_POST;
    header('Location: modifier.php?code=' . urlencode($code));
    exit();
}

$upd = $pdo->prepare('
    UPDATE etudiants SET
        Nom=?, Prenom=?, Filiere=?, Note=?, Photo=?, date_naissance=?, email=?, telephone=?
    WHERE Code=?
');
$upd->execute([$nom, $prenom, $filiere, $note, $photo_filename, $date_naissance, $email, $telephone, $code]);

header('Location: liste.php?msg=modifie');
exit();
