<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
$flash = $_SESSION['offer_flash'] ?? null;
unset($_SESSION['offer_flash']);

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
    <title><?php echo htmlspecialchars($offer['title']); ?> - InternHub</title>
    <link rel="stylesheet" href="styles.css?v=20260320">
    <style>
        .offer-detail-section {
            min-height: 100vh;
            padding: 64px 20px 88px;
        }

        .offer-detail-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
            gap: 26px;
            align-items: start;
        }

        .offer-hero,
        .offer-stats-card,
        .offer-company-card,
        .offer-flash {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 24px;
            box-shadow: 0 24px 55px rgba(2, 6, 23, 0.24);
        }

        .offer-hero,
        .offer-stats-card,
        .offer-company-card {
            padding: 28px;
        }

        .offer-flash {
            padding: 16px 20px;
            margin-bottom: 22px;
            color: #f8fafc;
        }

        .offer-flash.success {
            border-color: rgba(34, 197, 94, 0.45);
            background: linear-gradient(135deg, rgba(20, 83, 45, 0.78), rgba(15, 23, 42, 0.86));
        }

        .offer-flash.error {
            border-color: rgba(239, 68, 68, 0.45);
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.78), rgba(15, 23, 42, 0.86));
        }

        .offer-kicker {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid rgba(34,211,238,0.24);
            color: #67e8f9;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .offer-hero h1 {
            font-size: clamp(2.4rem, 6vw, 4.4rem);
            line-height: 1.03;
            margin-bottom: 16px;
        }

        .offer-hero h1 span {
            display: block;
            background: linear-gradient(90deg, #22d3ee, #ec4899, #facc15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .offer-hero p {
            color: #cbd5e1;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .offer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .offer-actions form {
            margin: 0;
        }

        .offer-stats-card h2,
        .offer-company-card h2 {
            font-size: 1.4rem;
            margin-bottom: 18px;
            color: #f8fafc;
        }

        .offer-stats-grid {
            display: grid;
            gap: 14px;
        }

        .offer-stat {
            padding: 16px;
            border-radius: 18px;
            background: rgba(15,23,42,0.46);
            border: 1px solid rgba(34,211,238,0.16);
        }

        .offer-stat .label {
            display: block;
            color: #94a3b8;
            font-size: 0.82rem;
            margin-bottom: 6px;
        }

        .offer-stat .value {
            color: #f8fafc;
            font-size: 1.45rem;
            font-weight: 800;
        }

        .offer-company-card p {
            color: #cbd5e1;
            margin-bottom: 12px;
        }

        .offer-company-card a {
            color: #67e8f9;
            text-decoration: none;
        }

        .offer-company-card a:hover {
            color: #f9a8d4;
        }

        @media (max-width: 980px) {
            .offer-detail-layout {
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
                <a href="offers.php" class="btn btn-outline">Retour aux offres</a>
            </div>
        </div>
    </header>

    <section class="offer-detail-section">
        <div class="container">
            <?php if ($flash): ?>
                <div class="offer-flash <?php echo htmlspecialchars($flash['type']); ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="offer-detail-layout">
                <div class="offer-hero">
                    <div class="offer-kicker"><?php echo htmlspecialchars($offer['company_name']); ?></div>
                    <h1>
                        <?php echo htmlspecialchars($offer['title']); ?>
                        <span>offre de stage</span>
                    </h1>
                    <p><?php echo nl2br(htmlspecialchars($offer['description'] ?: 'Aucune description fournie pour cette offre.')); ?></p>

                    <div class="offer-actions">
                        <a href="offers.php" class="btn btn-outline">Toutes les offres</a>
                        <a href="companies.php?action=view&id=<?php echo $offer['company_id']; ?>" class="btn btn-outline">Voir l'entreprise</a>
                        <?php if ($canManageOffers): ?>
                            <a href="offers.php?action=edit&id=<?php echo $offer['id']; ?>" class="btn btn-primary">Modifier</a>
                            <form method="post" action="offers.php?action=delete" onsubmit="return confirm('Supprimer cette offre ?');">
                                <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                <button type="submit" class="btn btn-primary">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="offer-stats-card">
                    <h2>Statistiques de l'offre</h2>
                    <div class="offer-stats-grid">
                        <div class="offer-stat">
                            <span class="label">Candidatures</span>
                            <span class="value"><?php echo htmlspecialchars((string) ($offer['applications_count'] ?? 0)); ?></span>
                        </div>
                        <div class="offer-stat">
                            <span class="label">Entreprise</span>
                            <span class="value"><?php echo htmlspecialchars($offer['company_name']); ?></span>
                        </div>
                        <div class="offer-stat">
                            <span class="label">Publiee le</span>
                            <span class="value" style="font-size:1rem"><?php echo htmlspecialchars($offer['created_at']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="offer-company-card" style="margin-top:26px">
                <h2>Entreprise rattachee</h2>
                <p><strong>Nom:</strong> <a href="companies.php?action=view&id=<?php echo $offer['company_id']; ?>"><?php echo htmlspecialchars($offer['company_name']); ?></a></p>
                <?php if (!empty($offer['company_email'])): ?>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($offer['company_email']); ?>"><?php echo htmlspecialchars($offer['company_email']); ?></a></p>
                <?php endif; ?>
                <?php if (!empty($offer['company_phone'])): ?>
                    <p><strong>Telephone:</strong> <?php echo htmlspecialchars($offer['company_phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($offer['company_description'])): ?>
                    <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($offer['company_description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="script.js?v=20260320"></script>
</body>
</html>
