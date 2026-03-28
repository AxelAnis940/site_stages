<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
$editing = !empty($company);
$companyName = trim((string) ($company['name'] ?? ''));
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $editing ? 'Modifier' : 'Creer'; ?> une entreprise - InternHub</title>
    <link rel="stylesheet" href="styles.css?v=20260320">
    <style>
        .company-form-page {
            min-height: 100vh;
        }

        .company-form-section {
            position: relative;
            overflow: hidden;
            padding: 72px 20px 88px;
        }

        .company-form-section::before,
        .company-form-section::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(16px);
            opacity: 0.5;
            pointer-events: none;
        }

        .company-form-section::before {
            width: 300px;
            height: 300px;
            top: 80px;
            left: -80px;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.35), transparent 70%);
        }

        .company-form-section::after {
            width: 360px;
            height: 360px;
            right: -120px;
            bottom: 10px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.28), transparent 72%);
        }

        .company-form-layout {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 560px);
            gap: 36px;
            align-items: start;
        }

        .company-form-intro {
            padding-top: 32px;
        }

        .company-form-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            margin-bottom: 20px;
            border-radius: 999px;
            border: 1px solid rgba(34, 211, 238, 0.24);
            background: rgba(15, 23, 42, 0.55);
            color: #67e8f9;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .company-form-intro h1 {
            font-size: clamp(2.6rem, 6vw, 4.8rem);
            line-height: 1.02;
            margin-bottom: 18px;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .company-form-intro h1 span {
            display: block;
            background: linear-gradient(90deg, #22d3ee, #ec4899, #facc15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .company-form-intro p {
            max-width: 620px;
            color: #cbd5e1;
            font-size: 1.02rem;
            margin-bottom: 28px;
        }

        .company-form-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .company-form-highlight {
            padding: 12px 16px;
            border-radius: 16px;
            border: 1px solid rgba(34, 211, 238, 0.18);
            background: rgba(15, 23, 42, 0.5);
            color: #dbeafe;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }

        .company-form-highlight strong {
            display: block;
            color: #67e8f9;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }

        .company-form-card {
            position: relative;
            padding: 32px;
            border-radius: 28px;
            border: 1px solid rgba(34, 211, 238, 0.25);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.82));
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.45);
            backdrop-filter: blur(12px);
        }

        .company-form-card::before {
            content: "";
            position: absolute;
            inset: -1px;
            border-radius: 28px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(34, 211, 238, 0.45), rgba(236, 72, 153, 0.28), rgba(250, 204, 21, 0.25));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            opacity: 0.9;
        }

        .company-form-card h2 {
            font-size: 1.9rem;
            margin-bottom: 10px;
            color: #f8fafc;
        }

        .company-form-card p {
            color: #94a3b8;
            margin-bottom: 26px;
        }

        .company-editor-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .company-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .company-form-grid .form-group-full {
            grid-column: 1 / -1;
        }

        .company-editor-form .form-group {
            gap: 10px;
        }

        .company-editor-form label {
            font-size: 0.92rem;
        }

        .company-editor-form input,
        .company-editor-form textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(34, 211, 238, 0.22);
            background: rgba(15, 23, 42, 0.7);
            color: #f8fafc;
            font: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .company-editor-form input::placeholder,
        .company-editor-form textarea::placeholder {
            color: #64748b;
        }

        .company-editor-form input:focus,
        .company-editor-form textarea:focus {
            border-color: #22d3ee;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.12);
            transform: translateY(-1px);
        }

        .company-editor-form textarea {
            min-height: 150px;
            resize: vertical;
        }

        .company-form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            padding-top: 6px;
        }

        .company-form-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .company-form-actions .btn-outline {
            min-width: 180px;
            background: rgba(15, 23, 42, 0.4);
        }

        .company-submit-btn {
            min-width: 220px;
            box-shadow: 0 18px 40px rgba(236, 72, 153, 0.24);
        }

        .company-submit-btn:hover {
            transform: translateY(-1px);
        }

        .company-form-note {
            margin-top: 20px;
            color: #94a3b8;
            font-size: 0.92rem;
        }

        @media (max-width: 980px) {
            .company-form-layout {
                grid-template-columns: 1fr;
            }

            .company-form-intro {
                padding-top: 0;
            }
        }

        @media (max-width: 640px) {
            .company-form-section {
                padding: 48px 16px 72px;
            }

            .company-form-card {
                padding: 24px 20px;
                border-radius: 24px;
            }

            .company-form-grid {
                grid-template-columns: 1fr;
            }

            .company-form-actions .btn,
            .company-submit-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="company-form-page">
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
                <a href="#">Blog</a>
            </nav>

            <div class="cta-desktop">
                <a href="companies.php" class="btn btn-outline">Retour a la liste</a>
                <a href="index.html" class="btn btn-primary">Accueil</a>
            </div>

            <button class="menu-toggle" type="button" aria-label="Ouvrir le menu" onclick="toggleMenu()">Menu</button>
        </div>

        <div class="nav-mobile" id="mobileMenu">
            <a href="offers.php">Offers</a>
            <a href="companies.php">Companies</a>
            <a href="#">Resources</a>
            <a href="#">Blog</a>

            <div class="mobile-cta">
                <a href="companies.php" class="btn btn-outline">Retour a la liste</a>
                <a href="index.html" class="btn btn-primary">Accueil</a>
            </div>
        </div>
    </header>

    <section class="company-form-section">
        <div class="container company-form-layout">
            <div class="company-form-intro">
                <div class="company-form-kicker">Company Studio</div>
                <h1>
                    <?php echo $editing ? 'Modifier' : 'Creer'; ?>
                    <span>une entreprise</span>
                </h1>
                <p>
                    Retrouvez ici la meme ambiance visuelle que la page d'accueil InternHub pour
                    editer les informations d'une entreprise sans perdre le contexte du site.
                </p>

                <div class="company-form-highlights">
                    <div class="company-form-highlight">
                        <strong>Mode</strong>
                        <?php echo $editing ? 'Edition en cours' : 'Nouvelle fiche'; ?>
                    </div>
                    <div class="company-form-highlight">
                        <strong>Entreprise</strong>
                        <?php echo htmlspecialchars($companyName !== '' ? $companyName : 'A renseigner'); ?>
                    </div>
                    <div class="company-form-highlight">
                        <strong>Action</strong>
                        <?php echo $editing ? 'Mettre a jour les informations' : 'Creer une nouvelle entree'; ?>
                    </div>
                </div>
            </div>

            <div class="company-form-card">
                <h2><?php echo $editing ? 'Mettre a jour la fiche' : 'Creer une nouvelle fiche'; ?></h2>
                <p>
                    Completez les champs ci-dessous pour garder une presentation claire, coherente
                    et professionnelle dans l'espace entreprises.
                </p>

                <form class="company-editor-form" method="post" action="companies.php?action=<?php echo $editing ? 'edit' : 'create'; ?>">
                    <?php if ($editing): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($company['id']); ?>">
                    <?php endif; ?>

                    <div class="company-form-grid">
                        <div class="form-group form-group-full">
                            <label for="company-name">Nom de l'entreprise *</label>
                            <input
                                id="company-name"
                                type="text"
                                name="name"
                                required
                                placeholder="Ex: TechVision AI"
                                value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="company-email">Email</label>
                            <input
                                id="company-email"
                                type="email"
                                name="email"
                                placeholder="contact@entreprise.com"
                                value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="company-phone">Telephone</label>
                            <input
                                id="company-phone"
                                type="text"
                                name="phone"
                                placeholder="+33 1 23 45 67 89"
                                value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group form-group-full">
                            <label for="company-description">Description</label>
                            <textarea
                                id="company-description"
                                name="description"
                                placeholder="Presentez l'entreprise, ses activites, sa culture et les opportunites proposees."
                            ><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="company-form-actions">
                        <a href="companies.php" class="btn btn-outline">Annuler</a>
                        <button type="submit" class="btn btn-primary company-submit-btn">
                            <?php echo $editing ? 'Mettre a jour' : 'Creer l\'entreprise'; ?>
                        </button>
                    </div>
                </form>

                <p class="company-form-note">
                    Conseil: un nom clair, une description concise et des coordonnees a jour rendent la fiche plus credible.
                </p>
            </div>
        </div>
    </section>

    <script src="script.js?v=20260320"></script>
</body>
</html>
