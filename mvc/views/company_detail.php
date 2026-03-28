<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
// $company, $offers, $evaluations provided
$userRole = strtolower(trim((string) ($_SESSION['user']['role'] ?? 'public')));
if ($userRole === 'recruiter') {
    $userRole = 'pilote';
}
$canManageCompanies = in_array($userRole, ['pilote', 'admin'], true);
$canEvaluateCompanies = in_array($userRole, ['pilote', 'admin'], true);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company['name']); ?> - InternHub</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .detail-section { padding: 60px 20px; min-height: 100vh; }
        .detail-header { text-align: center; margin-bottom: 40px; }
        .detail-header h1 { font-size: 2.2rem; background: linear-gradient(90deg,#22d3ee,#ec4899); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .detail-header p { color:#d1d5db; }
        .detail-buttons { margin-top: 16px; }
        .detail-buttons a, .detail-buttons form button { margin-right:8px; }
        .info-block { background: rgba(255,255,255,0.03); border:1px solid rgba(34,211,238,0.2); border-radius:16px; padding:30px; max-width:800px; margin:20px auto; }
        .info-block p { margin-bottom:12px; }
        .offers-list, .evals-list { max-width:800px; margin:20px auto; }
        .offers-header { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:12px; }
        .offers-list ul { list-style:none; padding-left:0; }
        .offers-list li { background: rgba(255,255,255,0.03); border:1px solid rgba(34,211,238,0.2); border-radius:8px; padding:12px 16px; margin-bottom:10px; }
        .offer-link { color:#67e8f9; text-decoration:none; }
        .offer-link:hover { color:#f9a8d4; }
        .offer-inline-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
        .offer-inline-actions a, .offer-inline-actions button { text-decoration:none; }
        .evals-table { width:100%; max-width:800px; margin:20px auto; border-collapse:collapse; }
        .evals-table th, .evals-table td { padding:12px; border:1px solid rgba(34,211,238,0.2); }
        .evals-table th { background:rgba(34,211,238,0.05); }
        .eval-form { max-width:600px; margin:30px auto; background:rgba(255,255,255,0.03); border:1px solid rgba(34,211,238,0.2); border-radius:16px; padding:30px; }
        .eval-form label { display:block; margin-bottom:6px; color:#d1d5db; }
        .eval-form input, .eval-form select, .eval-form textarea { width:100%; padding:10px; margin-bottom:16px; background:rgba(255,255,255,0.05); border:1px solid rgba(34,211,238,0.2); border-radius:8px; color:#e5e7eb; }
        .eval-form button { width:100%; background:linear-gradient(90deg,#ec4899,#ef4444); }
        .btn-delete { padding:10px 14px; background:rgba(239,68,68,0.18); border:1px solid rgba(239,68,68,0.35); color:#fca5a5; border-radius:10px; cursor:pointer; font-weight:700; }
        .btn-delete:hover { background:rgba(239,68,68,0.28); }
        .back-link { text-align:center; margin-top:50px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <div class="logo-icon">I</div>
                <span class="logo-text">InternHub</span>
            </div>
            <nav class="nav-desktop">
                <a href="offers.php">Offers</a>
                <a href="companies.php">Companies</a>
                <a href="#">Resources</a>
            </nav>
            <div class="cta-desktop">
                <a href="companies.php" class="btn btn-outline">← Retour</a>
            </div>
        </div>
    </header>

    <section class="detail-section">
        <div class="container">
            <div class="detail-header">
                <h1><?php echo htmlspecialchars($company['name']); ?></h1>
                <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
            </div>
            <div class="detail-buttons">
                <?php if ($canManageCompanies): ?>
                    <a href="companies.php?action=edit&id=<?php echo $company['id']; ?>" class="btn btn-primary">Modifier</a>
                    <form style="display:inline" method="post" action="companies.php?action=delete" onsubmit="return confirm('Supprimer cette entreprise ?');">
                        <input type="hidden" name="id" value="<?php echo $company['id']; ?>">
                        <button type="submit" class="btn-delete">Suppr.</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="info-block">
                <?php if ($company['email']): ?><p>📧 <a href="mailto:<?php echo htmlspecialchars($company['email']); ?>"><?php echo htmlspecialchars($company['email']); ?></a></p><?php endif; ?>
                <?php if ($company['phone']): ?><p>📞 <?php echo htmlspecialchars($company['phone']); ?></p><?php endif; ?>
            </div>

            <div class="offers-list">
                <div class="offers-header">
                    <h2>Offres</h2>
                    <?php if ($canManageCompanies): ?>
                        <a href="offers.php?action=create&company_id=<?php echo $company['id']; ?>" class="btn btn-primary">Nouvelle offre</a>
                    <?php endif; ?>
                </div>
                <?php if (!empty($offers)): ?>
                    <ul>
                        <?php foreach ($offers as $o): ?>
                            <li>
                                <strong><a class="offer-link" href="offers.php?action=view&id=<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['title']); ?></a></strong><br>
                                <?php echo nl2br(htmlspecialchars($o['description'])); ?>
                                <?php if ($canManageCompanies): ?>
                                    <div class="offer-inline-actions">
                                        <a href="offers.php?action=edit&id=<?php echo $o['id']; ?>" class="btn btn-outline">Modifier</a>
                                        <form style="display:inline; margin:0" method="post" action="offers.php?action=delete" onsubmit="return confirm('Supprimer cette offre ?');">
                                            <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                                            <input type="hidden" name="redirect_to" value="companies.php?action=view&id=<?php echo $company['id']; ?>">
                                            <button type="submit" class="btn-delete">Suppr.</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucune offre associée.</p>
                <?php endif; ?>
            </div>

            <div class="evals-list">
                <h2>Évaluations</h2>
                <?php if (!empty($evaluations)): ?>
                    <table class="evals-table">
                        <thead><tr><th>Évaluateur</th><th>Note</th><th>Commentaire</th><th>Le</th></tr></thead>
                        <tbody>
                        <?php foreach ($evaluations as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($e['evaluator_name']); ?></td>
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
            </div>

            <?php if ($canEvaluateCompanies && !empty($offers)): ?>
                <div class="eval-form">
                    <h3>Ajouter une évaluation</h3>
                    <form method="post" action="companies.php?action=evaluate">
                        <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                        <!-- user_id will be taken from session -->
                        <label>Note (1–5)</label>
                        <select name="rating">
                            <?php for ($i=1; $i<=5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <label>Commentaire</label>
                        <textarea name="comment"></textarea>
                        <button type="submit" class="btn-submit">Envoyer</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="back-link"><a href="companies.php" class="btn btn-outline">← Retour à la liste</a></div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
