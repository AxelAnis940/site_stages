<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
// $company, $offers, $evaluations provided
$userRole = $_SESSION['user']['role'] ?? 'public';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Détails de <?php echo htmlspecialchars($company['name']); ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background:#0f172a; color:#e5e7eb; padding:30px }
        table { width:100%; border-collapse:collapse; margin-bottom:20px }
        th, td { padding:8px 10px; border:1px solid rgba(255,255,255,0.06) }
        th { background:rgba(34,211,238,0.06) }
        form { max-width:520px; background:rgba(255,255,255,0.03); padding:16px; border-radius:8px }
        input, textarea, select { width:100%; padding:8px; margin-bottom:8px; border-radius:4px; border:1px solid rgba(255,255,255,0.06); background:transparent; color:#e5e7eb }
        button { padding:10px 14px; background:#22d3ee; border:none; color:#0f172a; font-weight:700; border-radius:8px }
        a { color:#22d3ee }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($company['name']); ?></h1>
<?php if (in_array($userRole,['recruiter','admin'], true)): ?>
    <p><a href="companies.php?action=edit&id=<?php echo $company['id']; ?>">Modifier</a> |
       <form style="display:inline" method="post" action="companies.php?action=delete" onsubmit="return confirm('Supprimer cette entreprise ?');">
            <input type="hidden" name="id" value="<?php echo $company['id']; ?>">
            <button type="submit" style="background:#ef4444;">Suppr.</button>
       </form>
    </p>
<?php endif; ?>
    <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
    <p>Email : <?php echo htmlspecialchars($company['email']); ?></p>
    <p>Téléphone : <?php echo htmlspecialchars($company['phone']); ?></p>

    <h2>Offres</h2>
    <?php if (!empty($offers)): ?>
        <ul>
        <?php foreach ($offers as $o): ?>
            <li><?php echo htmlspecialchars($o['title']); ?> – <?php echo nl2br(htmlspecialchars($o['description'])); ?></li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucune offre associée.</p>
    <?php endif; ?>

    <h2>Evaluations</h2>
    <?php if (!empty($evaluations)): ?>
        <table>
            <thead><tr><th>Étudiant</th><th>Note</th><th>Commentaire</th><th>Le</th></tr></thead>
            <tbody>
            <?php foreach ($evaluations as $e): ?>
                <tr>
                    <td><?php echo htmlspecialchars($e['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($e['rating']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($e['comment'])); ?></td>
                    <td><?php echo htmlspecialchars($e['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune évaluation pour le moment.</p>
    <?php endif; ?>

<?php if ($userRole === 'student'): ?>
    <h3>Ajouter une évaluation (démo)</h3>
    <form method="post" action="companies.php?action=evaluate">
        <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
        <!-- dans une vraie app, user_id proviendrait de la session -->
        <label>ID étudiant</label>
        <input type="number" name="user_id" required>
        <label>Note (1–5)</label>
        <select name="rating">
            <?php for ($i=1; $i<=5; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <label>Commentaire</label>
        <textarea name="comment"></textarea>
        <button type="submit">Envoyer</button>
    </form>
<?php endif; ?>

    <p><a href="companies.php">Retour à la liste</a></p>
</body>
</html>