<?php if (!defined('VIEW_INCLUDED')) { define('VIEW_INCLUDED', true); }
$userRole = $_SESSION['user']['role'] ?? 'public';
// $companies and optionally $criteria passed from controller
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entreprises - InternHub</title>
    <link rel="stylesheet" href="/styles.css">
    <style>
        .companies-section {
            min-height: 100vh;
            padding: 60px 20px;
        }
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #22d3ee, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .section-header p {
            font-size: 1.1rem;
            color: #d1d5db;
        }
        
        .search-form {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .search-form h3 {
            margin-bottom: 20px;
            color: #22d3ee;
        }
        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .search-form input {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 8px;
            color: #e5e7eb;
            font-size: 0.95rem;
        }
        .search-form input::placeholder {
            color: #6b7280;
        }
        .search-form input:focus {
            outline: none;
            border-color: #22d3ee;
            background: rgba(34,211,238,0.05);
        }
        
        .companies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }
        
        .company-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .company-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(34,211,238,0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .company-card:hover {
            border-color: #22d3ee;
            background: rgba(34,211,238,0.05);
            transform: translateY(-5px);
        }
        
        .company-card:hover::before {
            left: 100%;
        }
        
        .company-header {
            position: relative;
            z-index: 1;
            margin-bottom: 16px;
        }
        
        .company-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #22d3ee;
            margin-bottom: 8px;
            text-decoration: none;
            display: inline-block;
        }
        
        .company-name:hover {
            color: #ec4899;
        }
        
        .company-contact {
            font-size: 0.9rem;
            color: #d1d5db;
            margin-bottom: 12px;
        }
        
        .company-contact a {
            color: #22d3ee;
            text-decoration: none;
        }
        
        .company-contact a:hover {
            text-decoration: underline;
        }
        
        .company-stats {
            display: flex;
            gap: 20px;
            margin: 16px 0;
            padding: 12px 0;
            border-top: 1px solid rgba(34,211,238,0.2);
            border-bottom: 1px solid rgba(34,211,238,0.2);
        }
        
        .stat {
            flex: 1;
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #22d3ee;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 4px;
        }
        
        .company-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }
        
        .btn-view {
            flex: 1;
            padding: 10px;
            background: linear-gradient(90deg, #22d3ee, #0891b2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }
        
        .btn-edit {
            padding: 10px 12px;
            background: rgba(168,85,247,0.2);
            border: 1px solid rgba(168,85,247,0.5);
            color: #a855f7;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            background: rgba(168,85,247,0.3);
        }
        
        .btn-delete {
            padding: 10px 12px;
            background: rgba(239,68,68,0.2);
            border: 1px solid rgba(239,68,68,0.5);
            color: #ef4444;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-delete:hover {
            background: rgba(239,68,68,0.3);
        }
        
        .create-section {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 16px;
            padding: 40px;
            max-width: 600px;
            margin: 50px auto;
        }
        
        .create-section h3 {
            margin-bottom: 24px;
            font-size: 1.5rem;
            background: linear-gradient(90deg, #22d3ee, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #d1d5db;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 8px;
            color: #e5e7eb;
            font-family: inherit;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #22d3ee;
            background: rgba(34,211,238,0.05);
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #ec4899, #ef4444);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: #9ca3af;
            margin-bottom: 10px;
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
                <a href="/">Browse</a>
                <a href="/companies.php">Companies</a>
                <a href="#">Resources</a>
            </nav>
            <div class="cta-desktop">
                <a href="/" class="btn btn-outline">← Retour</a>
            </div>
        </div>
    </header>

    <section class="companies-section">
        <div class="container">
            <div class="section-header">
                <h1>Entreprises</h1>
                <p>Découvrez et explorez les entreprises partenaires</p>
            </div>

            <!-- Search Form -->
            <div class="search-form">
                <h3>🔍 Rechercher</h3>
                <form method="get" action="companies.php">
                    <input type="hidden" name="action" value="index">
                    <div class="search-grid">
                        <div>
                            <label style="display:block; margin-bottom:8px; color:#d1d5db">Nom</label>
                            <input type="text" name="name" placeholder="Nom de l'entreprise" value="<?php echo htmlspecialchars($criteria['name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; color:#d1d5db">Email</label>
                            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($criteria['email'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; color:#d1d5db">Téléphone</label>
                            <input type="text" name="phone" placeholder="Téléphone" value="<?php echo htmlspecialchars($criteria['phone'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; color:#d1d5db">Description</label>
                            <input type="text" name="description" placeholder="Mots-clés" value="<?php echo htmlspecialchars($criteria['description'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%">Rechercher</button>
                </form>
            </div>

            <!-- Companies Grid -->
            <?php if (!empty($companies)): ?>
                <div class="companies-grid">
                <?php foreach ($companies as $c): ?>
                    <div class="company-card">
                        <div class="company-header">
                            <a href="companies.php?action=view&id=<?php echo $c['id']; ?>" class="company-name">
                                <?php echo htmlspecialchars($c['name']); ?>
                            </a>
                        </div>
                        
                        <?php if ($c['email']): ?>
                            <div class="company-contact">
                                📧 <a href="mailto:<?php echo htmlspecialchars($c['email']); ?>"><?php echo htmlspecialchars($c['email']); ?></a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($c['phone']): ?>
                            <div class="company-contact">
                                📞 <?php echo htmlspecialchars($c['phone']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="company-stats">
                            <div class="stat">
                                <div class="stat-value"><?php echo htmlspecialchars($c['applicants_count'] ?? 0); ?></div>
                                <div class="stat-label">Candidats</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">⭐ <?php echo htmlspecialchars(round($c['avg_rating'] ?? 0, 1)); ?></div>
                                <div class="stat-label">Note</div>
                            </div>
                        </div>
                        
                        <div class="company-actions">
                            <a href="companies.php?action=view&id=<?php echo $c['id']; ?>" class="btn-view">Voir détails</a>
                            <?php if (in_array($userRole, ['recruiter','admin'], true)): ?>
                                <a href="companies.php?action=edit&id=<?php echo $c['id']; ?>" class="btn-edit">✎</a>
                                <form style="display:inline; margin:0" method="post" action="companies.php?action=delete" onsubmit="return confirm('Supprimer cette entreprise ?');">
                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" class="btn-delete">🗑</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Aucune entreprise trouvée</h3>
                    <p style="color:#9ca3af">Essayez d'ajuster vos critères de recherche ou créez une nouvelle entreprise.</p>
                </div>
            <?php endif; ?>

            <!-- Create Section -->
            <?php if (in_array($userRole, ['recruiter','admin'], true)): ?>
                <div class="create-section">
                    <h3>➕ Créer une entreprise</h3>
                    <form method="post" action="companies.php?action=create">
                        <div class="form-group">
                            <label>Nom de l'entreprise *</label>
                            <input type="text" name="name" required placeholder="Ex: TechVision AI">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="4" placeholder="Décrivez l'entreprise et ses activités..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="contact@entreprise.com">
                        </div>
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="tel" name="phone" placeholder="+33 1 23 45 67 89">
                        </div>
                        <button type="submit" class="btn-submit">Créer l'entreprise</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="/script.js"></script>
</body>
</html>
