<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
$flash = $_SESSION['user_flash'] ?? null;
unset($_SESSION['user_flash']);

$currentAdmin = $_SESSION['user']['name'] ?? 'Admin';
$currentUserId = intval($_SESSION['user']['id'] ?? 0);
$users = $users ?? [];
$editingUser = $editingUser ?? null;
$isEditingUser = !empty($editingUser);
$editingUserId = $isEditingUser ? intval($editingUser['id']) : 0;
$totalUsers = count($users);
$roleCounts = [
    'admin' => 0,
    'pilote' => 0,
    'student' => 0,
];

foreach ($users as $userRow) {
    $role = strtolower(trim((string) ($userRow['role'] ?? '')));

    if (!array_key_exists($role, $roleCounts)) {
        $roleCounts[$role] = 0;
    }

    $roleCounts[$role]++;
}

$activeRoleCount = count(array_filter($roleCounts, static function ($count) {
    return $count > 0;
}));
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Administration des utilisateurs - InternHub</title>
    <link rel="stylesheet" href="styles.css?v=20260320">
    <style>
        .users-admin-page {
            min-height: 100vh;
        }

        .users-admin-main {
            position: relative;
            overflow: hidden;
            padding: 68px 20px 88px;
        }

        .users-admin-main::before,
        .users-admin-main::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(18px);
            pointer-events: none;
            opacity: 0.5;
        }

        .users-admin-main::before {
            width: 340px;
            height: 340px;
            top: 40px;
            left: -120px;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.32), transparent 70%);
        }

        .users-admin-main::after {
            width: 420px;
            height: 420px;
            right: -160px;
            bottom: -50px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.28), transparent 72%);
        }

        .users-admin-layout {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .users-admin-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(300px, 0.8fr);
            gap: 24px;
            align-items: stretch;
        }

        .users-admin-copy,
        .users-admin-summary,
        .users-admin-table-card,
        .users-admin-form-card,
        .users-admin-flash {
            border: 1px solid rgba(34, 211, 238, 0.2);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.8));
            border-radius: 28px;
            backdrop-filter: blur(12px);
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
        }

        .users-admin-copy,
        .users-admin-summary,
        .users-admin-table-card,
        .users-admin-form-card {
            padding: 30px;
        }

        .users-admin-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            margin-bottom: 18px;
            border: 1px solid rgba(34, 211, 238, 0.24);
            background: rgba(15, 23, 42, 0.5);
            color: #67e8f9;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .users-admin-copy h1 {
            font-size: clamp(2.6rem, 6vw, 4.9rem);
            line-height: 1.03;
            margin-bottom: 18px;
            letter-spacing: -0.04em;
        }

        .users-admin-copy h1 span,
        .users-admin-section-title span {
            background: linear-gradient(90deg, #22d3ee, #ec4899, #facc15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .users-admin-copy p {
            max-width: 680px;
            color: #cbd5e1;
            margin-bottom: 24px;
            font-size: 1.02rem;
        }

        .users-admin-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .users-admin-tag {
            padding: 12px 16px;
            border-radius: 16px;
            border: 1px solid rgba(34, 211, 238, 0.18);
            background: rgba(15, 23, 42, 0.46);
            color: #e2e8f0;
        }

        .users-admin-tag strong {
            display: block;
            margin-bottom: 4px;
            color: #67e8f9;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .users-admin-summary {
            display: flex;
            flex-direction: column;
            gap: 20px;
            justify-content: space-between;
        }

        .users-admin-summary h2,
        .users-admin-section-title {
            font-size: 1.7rem;
            color: #f8fafc;
        }

        .users-admin-summary p {
            color: #94a3b8;
        }

        .users-admin-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .users-admin-metric {
            padding: 18px;
            border-radius: 20px;
            background: rgba(15, 23, 42, 0.52);
            border: 1px solid rgba(34, 211, 238, 0.18);
        }

        .users-admin-metric .value {
            display: block;
            margin-bottom: 6px;
            font-size: 2rem;
            font-weight: 800;
            color: #f8fafc;
        }

        .users-admin-metric .label {
            color: #94a3b8;
            font-size: 0.92rem;
        }

        .users-admin-flash {
            padding: 16px 20px;
            color: #f8fafc;
        }

        .users-admin-flash.success {
            border-color: rgba(34, 197, 94, 0.45);
            background: linear-gradient(135deg, rgba(20, 83, 45, 0.78), rgba(15, 23, 42, 0.86));
        }

        .users-admin-flash.error {
            border-color: rgba(239, 68, 68, 0.45);
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.78), rgba(15, 23, 42, 0.86));
        }

        .users-admin-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 24px;
            align-items: start;
        }

        .users-admin-section-title {
            margin-bottom: 10px;
        }

        .users-admin-subtitle {
            color: #94a3b8;
            margin-bottom: 22px;
        }

        .users-admin-table-wrap {
            overflow-x: auto;
            border-radius: 22px;
            border: 1px solid rgba(34, 211, 238, 0.15);
            background: rgba(15, 23, 42, 0.45);
        }

        .users-admin-table {
            width: 100%;
            min-width: 920px;
            border-collapse: collapse;
        }

        .users-admin-table th,
        .users-admin-table td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            vertical-align: middle;
        }

        .users-admin-table th {
            background: rgba(34, 211, 238, 0.08);
            color: #67e8f9;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .users-admin-table tbody tr {
            transition: background 0.2s ease;
        }

        .users-admin-table tbody tr:hover,
        .users-admin-table tbody tr.is-editing {
            background: rgba(34, 211, 238, 0.06);
        }

        .users-admin-table td {
            color: #e2e8f0;
        }

        .users-role-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid rgba(34, 211, 238, 0.22);
            background: rgba(34, 211, 238, 0.12);
            color: #67e8f9;
        }

        .users-role-pill.role-admin {
            border-color: rgba(250, 204, 21, 0.28);
            background: rgba(250, 204, 21, 0.14);
            color: #fde68a;
        }

        .users-role-pill.role-pilote {
            border-color: rgba(236, 72, 153, 0.28);
            background: rgba(236, 72, 153, 0.14);
            color: #f9a8d4;
        }

        .users-role-pill.role-student {
            border-color: rgba(34, 211, 238, 0.28);
            background: rgba(34, 211, 238, 0.14);
            color: #67e8f9;
        }

        .users-actions-cell {
            width: 210px;
        }

        .users-table-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .users-table-action-form {
            margin: 0;
        }

        .users-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .users-action-btn:hover {
            transform: translateY(-1px);
        }

        .users-action-btn-edit {
            background: rgba(34, 211, 238, 0.14);
            border-color: rgba(34, 211, 238, 0.28);
            color: #67e8f9;
        }

        .users-action-btn-delete {
            background: rgba(239, 68, 68, 0.14);
            border-color: rgba(239, 68, 68, 0.28);
            color: #fca5a5;
        }

        .users-action-btn-delete:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            transform: none;
        }

        .users-admin-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .users-admin-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .users-admin-form label {
            color: #dbeafe;
            font-weight: 600;
            font-size: 0.92rem;
        }

        .users-admin-form input,
        .users-admin-form select {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(34, 211, 238, 0.22);
            background: rgba(15, 23, 42, 0.68);
            color: #f8fafc;
            font: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .users-admin-form input::placeholder {
            color: #64748b;
        }

        .users-admin-form input:focus,
        .users-admin-form select:focus {
            border-color: #22d3ee;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.12);
            transform: translateY(-1px);
        }

        .users-admin-form select option {
            background: #0f172a;
            color: #f8fafc;
        }

        .users-admin-form-note {
            margin: 2px 0 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .users-admin-form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 6px;
        }

        .users-admin-form-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .users-admin-form-actions .btn-primary {
            box-shadow: 0 18px 40px rgba(236, 72, 153, 0.22);
        }

        @media (max-width: 1080px) {
            .users-admin-hero,
            .users-admin-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .users-admin-main {
                padding: 48px 16px 72px;
            }

            .users-admin-copy,
            .users-admin-summary,
            .users-admin-table-card,
            .users-admin-form-card {
                padding: 24px 20px;
                border-radius: 24px;
            }

            .users-admin-metrics {
                grid-template-columns: 1fr;
            }

            .users-admin-form-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="users-admin-page">
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <div class="logo-icon">I</div>
                <span class="logo-text">InternHub</span>
            </div>

            <nav class="nav-desktop">
                <a href="index.html">Browse</a>
                <a href="companies.php">Companies</a>
                <a href="users.php">Manage Users</a>
                <a href="#">Resources</a>
            </nav>

            <div class="cta-desktop">
                <a href="index.html" class="btn btn-outline">Accueil</a>
                <a href="users.php?action=logout" class="btn btn-primary">Se deconnecter</a>
            </div>

            <button class="menu-toggle" type="button" aria-label="Ouvrir le menu" onclick="toggleMenu()">Menu</button>
        </div>

        <div class="nav-mobile" id="mobileMenu">
            <a href="index.html">Browse</a>
            <a href="companies.php">Companies</a>
            <a href="users.php">Manage Users</a>
            <a href="#">Resources</a>

            <div class="mobile-cta">
                <a href="index.html" class="btn btn-outline">Accueil</a>
                <a href="users.php?action=logout" class="btn btn-primary">Se deconnecter</a>
            </div>
        </div>
    </header>

    <main class="users-admin-main">
        <div class="container users-admin-layout">
            <section class="users-admin-hero">
                <div class="users-admin-copy">
                    <div class="users-admin-kicker">Admin Console</div>
                    <h1>
                        Gerer les
                        <span>utilisateurs</span>
                    </h1>
                    <p>
                        Cette page reprend l'identite visuelle d'InternHub pour rendre
                        l'administration plus propre, plus lisible et plus proche du reste du site.
                    </p>

                    <div class="users-admin-tags">
                        <div class="users-admin-tag">
                            <strong>Connecte</strong>
                            <?php echo htmlspecialchars($currentAdmin); ?>
                        </div>
                        <div class="users-admin-tag">
                            <strong>Acces</strong>
                            Zone reservee aux administrateurs
                        </div>
                        <div class="users-admin-tag">
                            <strong>Mode</strong>
                            <?php echo $isEditingUser ? 'Edition utilisateur' : 'Creation utilisateur'; ?>
                        </div>
                    </div>
                </div>

                <aside class="users-admin-summary">
                    <div>
                        <h2>Vue d'ensemble</h2>
                        <p>Un resume rapide pour suivre l'etat des comptes et administrer les acces sans quitter la page.</p>
                    </div>

                    <div class="users-admin-metrics">
                        <div class="users-admin-metric">
                            <span class="value"><?php echo htmlspecialchars((string) $totalUsers); ?></span>
                            <span class="label">Utilisateurs total</span>
                        </div>
                        <div class="users-admin-metric">
                            <span class="value"><?php echo htmlspecialchars((string) $activeRoleCount); ?></span>
                            <span class="label">Roles actifs</span>
                        </div>
                        <div class="users-admin-metric">
                            <span class="value"><?php echo htmlspecialchars((string) ($roleCounts['admin'] ?? 0)); ?></span>
                            <span class="label">Admins</span>
                        </div>
                        <div class="users-admin-metric">
                            <span class="value"><?php echo htmlspecialchars((string) (($roleCounts['pilote'] ?? 0) + ($roleCounts['student'] ?? 0))); ?></span>
                            <span class="label">Pilotes et students</span>
                        </div>
                    </div>
                </aside>
            </section>

            <?php if ($flash): ?>
                <div class="users-admin-flash <?php echo htmlspecialchars($flash['type']); ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <section class="users-admin-grid">
                <div class="users-admin-table-card">
                    <h2 class="users-admin-section-title">
                        Liste des <span>utilisateurs</span>
                    </h2>
                    <p class="users-admin-subtitle">
                        Modifiez ou supprimez directement les comptes depuis ce tableau.
                    </p>

                    <div class="users-admin-table-wrap">
                        <table class="users-admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Cree le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                    <?php $role = strtolower(trim((string) ($u['role'] ?? 'student'))); ?>
                                    <?php $userId = intval($u['id']); ?>
                                    <tr class="<?php echo $editingUserId === $userId ? 'is-editing' : ''; ?>">
                                        <td><?php echo htmlspecialchars($u['id']); ?></td>
                                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span class="users-role-pill role-<?php echo htmlspecialchars($role); ?>">
                                                <?php echo htmlspecialchars($u['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                                        <td class="users-actions-cell">
                                            <div class="users-table-actions">
                                                <a href="users.php?action=edit&id=<?php echo $userId; ?>" class="users-action-btn users-action-btn-edit">Modifier</a>
                                                <form class="users-table-action-form" method="post" action="users.php?action=delete" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                    <input type="hidden" name="id" value="<?php echo $userId; ?>">
                                                    <button
                                                        type="submit"
                                                        class="users-action-btn users-action-btn-delete"
                                                        <?php echo $currentUserId === $userId ? 'disabled title="Impossible de supprimer votre propre compte"' : ''; ?>
                                                    >
                                                        Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">Aucun utilisateur trouve.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="users-admin-form-card">
                    <h2 class="users-admin-section-title">
                        <?php if ($isEditingUser): ?>
                            Modifier un <span>utilisateur</span>
                        <?php else: ?>
                            Creer un <span>utilisateur</span>
                        <?php endif; ?>
                    </h2>
                    <p class="users-admin-subtitle">
                        <?php if ($isEditingUser): ?>
                            Mettez a jour les informations du compte selectionne.
                        <?php else: ?>
                            Ajoutez un nouveau compte sans quitter l'espace d'administration.
                        <?php endif; ?>
                    </p>

                    <form class="users-admin-form" method="post" action="users.php?action=<?php echo $isEditingUser ? 'edit' : 'create'; ?>">
                        <?php if ($isEditingUser): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editingUser['id']); ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="user-name">Nom</label>
                            <input
                                id="user-name"
                                type="text"
                                name="name"
                                required
                                placeholder="Ex: Jane Admin"
                                value="<?php echo htmlspecialchars($editingUser['name'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="user-email">Email</label>
                            <input
                                id="user-email"
                                type="email"
                                name="email"
                                required
                                placeholder="admin@internhub.com"
                                value="<?php echo htmlspecialchars($editingUser['email'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="user-password">Mot de passe</label>
                            <input
                                id="user-password"
                                type="password"
                                name="password"
                                <?php echo $isEditingUser ? '' : 'required'; ?>
                                placeholder="<?php echo $isEditingUser ? 'Laisser vide pour conserver le mot de passe' : 'Mot de passe'; ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="user-role">Role</label>
                            <select id="user-role" name="role">
                                <option value="student" <?php echo (($editingUser['role'] ?? '') === 'student') ? 'selected' : ''; ?>>student</option>
                                <option value="pilote" <?php echo (($editingUser['role'] ?? '') === 'pilote') ? 'selected' : ''; ?>>pilote</option>
                                <option value="admin" <?php echo (($editingUser['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>admin</option>
                            </select>
                        </div>

                        <p class="users-admin-form-note">
                            <?php if ($isEditingUser): ?>
                                Laissez le mot de passe vide si vous ne souhaitez pas le modifier.
                            <?php else: ?>
                                Le mot de passe sera securise automatiquement lors de la creation du compte.
                            <?php endif; ?>
                        </p>

                        <div class="users-admin-form-actions">
                            <?php if ($isEditingUser): ?>
                                <a href="users.php" class="btn btn-outline">Annuler</a>
                                <button type="submit" class="btn btn-primary">Mettre a jour</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">Creer le compte</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <script src="script.js?v=20260320"></script>
</body>
</html>
