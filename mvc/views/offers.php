<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
$flash = $_SESSION['offer_flash'] ?? null;
unset($_SESSION['offer_flash']);

$stats = $stats ?? [
    'total_offers' => 0,
    'total_applications' => 0,
    'offers_with_applications' => 0,
];
$offers = $offers ?? [];
$companyOptions = $companyOptions ?? [];
$criteria = $criteria ?? [];

$userRole = strtolower(trim((string) ($_SESSION['user']['role'] ?? 'public')));
if ($userRole === 'recruiter') {
    $userRole = 'pilote';
}
$canManageOffers = in_array($userRole, ['pilote', 'admin'], true);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres de stage - InternHub</title>
    <link rel="stylesheet" href="styles.css?v=20260320">
    <style>
        .offers-section {
            min-height: 100vh;
            padding: 60px 20px 88px;
        }

        .offers-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 32px;
        }

        .offers-stat-card,
        .offers-search-form,
        .offers-admin-cta,
        .offer-card,
        .offers-flash {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(2, 6, 23, 0.2);
        }

        .offers-stat-card,
        .offers-search-form,
        .offers-admin-cta,
        .offer-card {
            padding: 24px;
        }

        .offers-stat-card .value {
            display: block;
            font-size: 2rem;
            font-weight: 800;
            color: #f8fafc;
            margin-bottom: 8px;
        }

        .offers-stat-card .label {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .offers-search-form {
            margin-bottom: 28px;
        }

        .offers-search-form h3,
        .offers-admin-cta h3 {
            margin-bottom: 18px;
            color: #67e8f9;
        }

        .offers-search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .offers-search-form label {
            display: block;
            margin-bottom: 8px;
            color: #dbeafe;
            font-weight: 600;
        }

        .offers-search-form input,
        .offers-search-form select {
            width: 100%;
            padding: 12px 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 12px;
            color: #e5e7eb;
            font: inherit;
        }

        .offers-search-form input:focus,
        .offers-search-form select:focus {
            outline: none;
            border-color: #22d3ee;
            box-shadow: 0 0 0 3px rgba(34,211,238,0.08);
        }

        .offers-admin-cta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .offers-admin-cta p {
            color: #cbd5e1;
        }

        .offers-flash {
            padding: 16px 20px;
            margin-bottom: 24px;
            color: #f8fafc;
        }

        .offers-flash.success {
            border-color: rgba(34, 197, 94, 0.45);
            background: linear-gradient(135deg, rgba(20, 83, 45, 0.78), rgba(15, 23, 42, 0.86));
        }

        .offers-flash.error {
            border-color: rgba(239, 68, 68, 0.45);
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.78), rgba(15, 23, 42, 0.86));
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 22px;
        }

        .offer-card {
            display: flex;
            flex-direction: column;
            gap: 18px;
            position: relative;
            overflow: hidden;
        }

        .offer-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent, rgba(34,211,238,0.08), transparent);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }

        .offer-card:hover::before {
            transform: translateX(100%);
        }

        .offer-card:hover {
            border-color: #22d3ee;
            transform: translateY(-3px);
        }

        .offer-top {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
        }

        .offer-company {
            color: #67e8f9;
            font-size: 0.86rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .offer-top h3 {
            font-size: 1.4rem;
            color: #f8fafc;
        }

        .offer-badge {
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(236,72,153,0.16);
            border: 1px solid rgba(236,72,153,0.26);
            color: #f9a8d4;
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .offer-description {
            color: #cbd5e1;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .offer-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(34,211,238,0.16);
        }

        .offer-meta-item span {
            display: block;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-bottom: 4px;
        }

        .offer-meta-item strong {
            color: #f8fafc;
        }

        .offer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .offer-actions form {
            margin: 0;
        }

        .btn-offer-view,
        .btn-offer-edit,
        .btn-offer-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid transparent;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-offer-view {
            background: linear-gradient(90deg, #22d3ee, #0891b2);
            color: white;
        }

        .btn-offer-edit {
            background: rgba(168,85,247,0.18);
            border-color: rgba(168,85,247,0.4);
            color: #d8b4fe;
        }

        .btn-offer-delete {
            background: rgba(239,68,68,0.18);
            border-color: rgba(239,68,68,0.35);
            color: #fca5a5;
        }

        .offers-empty {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .offers-empty h3 {
            color: #f8fafc;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .offers-admin-cta {
                flex-direction: column;
                align-items: stretch;
            }

            .offer-top,
            .offer-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .offer-meta {
                grid-template-columns: 1fr;
            }
        }
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
                <a href="index.html" class="btn btn-outline">Accueil</a>
            </div>
        </div>
    </header>

    <section class="offers-section">
        <div class="container">
            <div class="section-header">
                <h1>Offres de stage</h1>
                <p>Recherchez les offres, consultez leurs statistiques et explorez les opportunites publiees par les entreprises.</p>
            </div>

            <div class="offers-stats">
                <div class="offers-stat-card">
                    <span class="value"><?php echo htmlspecialchars((string) ($stats['total_offers'] ?? 0)); ?></span>
                    <span class="label">Offres publiees</span>
                </div>
                <div class="offers-stat-card">
                    <span class="value"><?php echo htmlspecialchars((string) ($stats['offers_with_applications'] ?? 0)); ?></span>
                    <span class="label">Offres avec candidatures</span>
                </div>
                <div class="offers-stat-card">
                    <span class="value"><?php echo htmlspecialchars((string) ($stats['total_applications'] ?? 0)); ?></span>
                    <span class="label">Candidatures totales</span>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="offers-flash <?php echo htmlspecialchars($flash['type']); ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="offers-search-form">
                <h3>Rechercher une offre</h3>
                <form method="get" action="offers.php">
                    <div class="offers-search-grid">
                        <div>
                            <label for="offer-title">Titre</label>
                            <input id="offer-title" type="text" name="title" placeholder="Ex: Junior AI Engineer" value="<?php echo htmlspecialchars($criteria['title'] ?? ''); ?>">
                        </div>
                        <div>
                            <label for="offer-company">Entreprise</label>
                            <select id="offer-company" name="company_id">
                                <option value="">Toutes les entreprises</option>
                                <?php foreach ($companyOptions as $company): ?>
                                    <option value="<?php echo htmlspecialchars($company['id']); ?>" <?php echo ((string) ($criteria['company_id'] ?? '') === (string) $company['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($company['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="offer-description">Description</label>
                            <input id="offer-description" type="text" name="description" placeholder="Mots-cles ou mission" value="<?php echo htmlspecialchars($criteria['description'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%">Rechercher</button>
                </form>
            </div>

            <?php if ($canManageOffers): ?>
                <div class="offers-admin-cta">
                    <div>
                        <h3>Gestion des offres</h3>
                        <p>Les comptes admin et pilote peuvent publier, modifier et supprimer les offres de stage.</p>
                    </div>
                    <a href="offers.php?action=create" class="btn btn-primary">Nouvelle offre</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($offers)): ?>
                <div class="offers-grid">
                    <?php foreach ($offers as $offer): ?>
                        <article class="offer-card">
                            <div class="offer-top">
                                <div>
                                    <div class="offer-company"><?php echo htmlspecialchars($offer['company_name']); ?></div>
                                    <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
                                </div>
                                <div class="offer-badge">
                                    <?php echo htmlspecialchars((string) ($offer['applications_count'] ?? 0)); ?> candidatures
                                </div>
                            </div>

                            <p class="offer-description">
                                <?php echo htmlspecialchars($offer['description'] ?: 'Aucune description fournie pour cette offre.'); ?>
                            </p>

                            <div class="offer-meta">
                                <div class="offer-meta-item">
                                    <span>Entreprise</span>
                                    <strong><?php echo htmlspecialchars($offer['company_name']); ?></strong>
                                </div>
                                <div class="offer-meta-item">
                                    <span>Publiee le</span>
                                    <strong><?php echo htmlspecialchars($offer['created_at']); ?></strong>
                                </div>
                            </div>

                            <div class="offer-actions">
                                <a href="offers.php?action=view&id=<?php echo $offer['id']; ?>" class="btn-offer-view">Voir l'offre</a>
                                <?php if ($canManageOffers): ?>
                                    <a href="offers.php?action=edit&id=<?php echo $offer['id']; ?>" class="btn-offer-edit">Modifier</a>
                                    <form method="post" action="offers.php?action=delete" onsubmit="return confirm('Supprimer cette offre ?');">
                                        <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                        <button type="submit" class="btn-offer-delete">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="offers-empty">
                    <h3>Aucune offre trouvee</h3>
                    <p>Essayez d'ajuster vos filtres ou ajoutez une nouvelle offre si vous etes admin ou pilote.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="script.js?v=20260320"></script>
</body>
</html>
