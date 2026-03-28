<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
$flash = $_SESSION['offer_flash'] ?? null;
unset($_SESSION['offer_flash']);

$editing = !empty($offer);
$companyOptions = $companyOptions ?? [];
$selectedCompanyId = intval($selectedCompanyId ?? ($offer['company_id'] ?? 0));
$hasCompanyOptions = !empty($companyOptions);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $editing ? 'Modifier' : 'Creer'; ?> une offre - InternHub</title>
    <link rel="stylesheet" href="styles.css?v=20260320">
    <style>
        .offer-form-section {
            position: relative;
            overflow: hidden;
            padding: 72px 20px 88px;
        }

        .offer-form-layout {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 560px);
            gap: 36px;
            align-items: start;
        }

        .offer-form-intro {
            padding-top: 24px;
        }

        .offer-form-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            margin-bottom: 20px;
            border-radius: 999px;
            border: 1px solid rgba(34, 211, 238, 0.24);
            background: rgba(15, 23, 42, 0.55);
            color: #67e8f9;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .offer-form-intro h1 {
            font-size: clamp(2.5rem, 6vw, 4.7rem);
            line-height: 1.02;
            margin-bottom: 18px;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .offer-form-intro h1 span {
            display: block;
            background: linear-gradient(90deg, #22d3ee, #ec4899, #facc15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .offer-form-intro p {
            max-width: 620px;
            color: #cbd5e1;
            font-size: 1.02rem;
            margin-bottom: 26px;
        }

        .offer-form-card,
        .offer-flash {
            border: 1px solid rgba(34, 211, 238, 0.24);
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.84));
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.4);
        }

        .offer-form-card {
            padding: 32px;
        }

        .offer-flash {
            padding: 16px 20px;
            margin-bottom: 20px;
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

        .offer-form-card h2 {
            color: #f8fafc;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .offer-form-card p {
            color: #94a3b8;
            margin-bottom: 22px;
        }

        .offer-editor-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .offer-editor-form label {
            display: block;
            margin-bottom: 8px;
            color: #dbeafe;
            font-weight: 600;
        }

        .offer-editor-form input,
        .offer-editor-form select,
        .offer-editor-form textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(34, 211, 238, 0.22);
            background: rgba(15, 23, 42, 0.68);
            color: #f8fafc;
            font: inherit;
            outline: none;
        }

        .offer-editor-form input:focus,
        .offer-editor-form select:focus,
        .offer-editor-form textarea:focus {
            border-color: #22d3ee;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.12);
        }

        .offer-editor-form textarea {
            min-height: 170px;
            resize: vertical;
        }

        .offer-form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .offer-form-note {
            color: #94a3b8;
            font-size: 0.92rem;
            margin-top: 4px;
        }

        @media (max-width: 980px) {
            .offer-form-layout {
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

    <section class="offer-form-section">
        <div class="container offer-form-layout">
            <div class="offer-form-intro">
                <div class="offer-form-kicker">Offer Studio</div>
                <h1>
                    <?php echo $editing ? 'Modifier' : 'Creer'; ?>
                    <span>une offre</span>
                </h1>
                <p>
                    Gere les offres de stage des entreprises avec la meme identite visuelle que le reste de l'application.
                </p>
            </div>

            <div>
                <?php if ($flash): ?>
                    <div class="offer-flash <?php echo htmlspecialchars($flash['type']); ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="offer-form-card">
                    <h2><?php echo $editing ? 'Mettre a jour l offre' : 'Publier une offre'; ?></h2>
                    <p>
                        Selectionnez l'entreprise rattachee, puis renseignez le titre et la description du stage.
                    </p>

                    <form class="offer-editor-form" method="post" action="offers.php?action=<?php echo $editing ? 'edit' : 'create'; ?>">
                        <?php if ($editing): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($offer['id']); ?>">
                        <?php endif; ?>

                        <div>
                            <label for="offer-company">Entreprise *</label>
                            <select id="offer-company" name="company_id" required>
                                <option value="">Choisir une entreprise</option>
                                <?php foreach ($companyOptions as $company): ?>
                                    <option value="<?php echo htmlspecialchars($company['id']); ?>" <?php echo $selectedCompanyId === intval($company['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($company['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="offer-title">Titre de l'offre *</label>
                            <input id="offer-title" type="text" name="title" required placeholder="Ex: Product Design Intern" value="<?php echo htmlspecialchars($offer['title'] ?? ''); ?>">
                        </div>

                        <div>
                            <label for="offer-description">Description</label>
                            <textarea id="offer-description" name="description" placeholder="Decrivez la mission, le contexte et les attentes du stage."><?php echo htmlspecialchars($offer['description'] ?? ''); ?></textarea>
                        </div>

                        <p class="offer-form-note">
                            <?php if ($hasCompanyOptions): ?>
                                Seuls les comptes admin et pilote peuvent publier ou modifier les offres.
                            <?php else: ?>
                                Creez d'abord une entreprise avant de publier une offre.
                            <?php endif; ?>
                        </p>

                        <div class="offer-form-actions">
                            <a href="offers.php" class="btn btn-outline">Annuler</a>
                            <button type="submit" class="btn btn-primary" <?php echo $hasCompanyOptions ? '' : 'disabled'; ?>>
                                <?php echo $editing ? 'Mettre a jour' : 'Publier l offre'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js?v=20260320"></script>
</body>
</html>
