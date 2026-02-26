<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
// $company may be set when editing
$editing = !empty($company);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $editing ? 'Modifier' : 'Créer'; ?> entreprise</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background:#0f172a; color:#e5e7eb; padding:30px }
        form { max-width:520px; background:rgba(255,255,255,0.03); padding:16px; border-radius:8px }
        input, textarea { width:100%; padding:8px; margin-bottom:8px; border-radius:4px; border:1px solid rgba(255,255,255,0.06); background:transparent; color:#e5e7eb }
        button { padding:10px 14px; background:#22d3ee; border:none; color:#0f172a; font-weight:700; border-radius:8px }
        a { color:#22d3ee }
    </style>
</head>
<body>
    <h1><?php echo $editing ? 'Modifier' : 'Créer'; ?> une entreprise</h1>
    <form method="post" action="companies.php?action=<?php echo $editing ? 'edit' : 'create'; ?>">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($company['id']); ?>">
        <?php endif; ?>
        <label>Nom *</label>
        <input type="text" name="name" required value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>">
        <label>Description</label>
        <textarea name="description"><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>">
        <label>Téléphone</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>">
        <button type="submit"><?php echo $editing ? 'Mettre à jour' : 'Créer'; ?></button>
    </form>
    <p><a href="companies.php">Retour à la liste</a></p>
</body>
</html>