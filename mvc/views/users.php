<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
// $users is expected from controller
$flash = $_SESSION['user_flash'] ?? null;
unset($_SESSION['user_flash']);
$currentAdmin = $_SESSION['user']['name'] ?? 'Admin';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Administration des utilisateurs</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background:#0f172a; color:#e5e7eb; padding:30px }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px }
        .topbar a { color:#22d3ee; text-decoration:none }
        .panel { max-width:520px; background:rgba(255,255,255,0.03); padding:16px; border-radius:8px; margin-bottom:24px }
        table { width:100%; border-collapse:collapse; margin-bottom:20px }
        th, td { padding:8px 10px; border:1px solid rgba(255,255,255,0.06) }
        th { background:rgba(34,211,238,0.06) }
        form { max-width:520px; background:rgba(255,255,255,0.03); padding:16px; border-radius:8px }
        input, select { width:100%; padding:8px; margin-bottom:8px; border-radius:4px; border:1px solid rgba(255,255,255,0.06); background:transparent; color:#e5e7eb }
        button { padding:10px 14px; background:#22d3ee; border:none; color:#0f172a; font-weight:700; border-radius:8px }
        a { color:#22d3ee }
        .flash { padding:12px 14px; border-radius:8px; margin-bottom:16px }
        .flash.success { background:rgba(34,197,94,0.14); border:1px solid rgba(34,197,94,0.4) }
        .flash.error { background:rgba(239,68,68,0.14); border:1px solid rgba(239,68,68,0.4) }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>Administration des utilisateurs</h1>
        <a href="index.html">Retour a l'accueil</a>
    </div>

    <div class="panel">
        <p><strong>Connecte en tant que :</strong> <?php echo htmlspecialchars($currentAdmin); ?></p>
        <p>Cette page est reservee a l'admin. Les nouveaux comptes sont crees ici, plus depuis la page publique de connexion.</p>
    </div>

    <?php if ($flash): ?>
        <div class="flash <?php echo htmlspecialchars($flash['type']); ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <p>Liste des utilisateurs presents dans la base <strong>internships_app</strong> :</p>

    <table>
        <thead>
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Role</th><th>Cree</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($users)): foreach ($users as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['id']); ?></td>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['role']); ?></td>
                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">Aucun utilisateur trouve.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h2>Creer un utilisateur</h2>
    <form method="post" action="users.php?action=create">
        <label>Nom</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <label>Role</label>
        <select name="role">
            <option value="student">student</option>
            <option value="pilote">pilote</option>
            <option value="admin">admin</option>
        </select>
        <button type="submit">Creer</button>
    </form>

    <p style="margin-top:20px">Apres avoir cree la base via <a href="setup_db.php">setup_db.php</a>, ouvrez <a href="/phpmyadmin/">phpMyAdmin</a> pour verifier la base <strong>internships_app</strong>.</p>
</body>
</html>
