<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .card-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-calendar-alt me-2"></i>
                Gestion EDT - Tableau de Bord
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord</h1>
                <p class="lead">Gestion complète des emplois du temps TP</p>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card dashboard-card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users card-icon mb-3"></i>
                        <h5 class="card-title">Groupes TP</h5>
                        <div class="stat-number">
                            <?php 
                            $this->db->like('nom', 'TP', 'both');
                            echo $this->db->count_all_results('ressources_groupes');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card dashboard-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-book card-icon mb-3"></i>
                        <h5 class="card-title">Matières</h5>
                        <div class="stat-number">
                            <?php echo $this->db->count_all('matieres'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card dashboard-card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-building card-icon mb-3"></i>
                        <h5 class="card-title">Salles TP</h5>
                        <div class="stat-number">
                            <?php 
                            $this->db->where('typeSalle', 'TP');
                            echo $this->db->count_all_results('ressources_salles');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card dashboard-card bg-warning text-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-clock card-icon mb-3"></i>
                        <h5 class="card-title">Séances Planifiées</h5>
                        <div class="stat-number">
                            <?php echo $this->db->count_all('seances'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation principale -->
        <div class="row g-4">
            <!-- Planification TP -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-plus-circle text-primary me-3" style="font-size: 1.5rem;"></i>
                            <h4 class="card-title mb-0">Planification</h4>
                        </div>
                        <p class="card-text">Planifier de nouveaux travaux pratiques</p>
                        <a href="<?php echo site_url('edt'); ?>" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-plus me-2"></i>Planifier un TP
                        </a>
                    </div>
                </div>
            </div>

            <!-- Visualisation EDT -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-eye text-success me-3" style="font-size: 1.5rem;"></i>
                            <h4 class="card-title mb-0">Visualisation</h4>
                        </div>
                        <p class="card-text">Consulter les emplois du temps existants</p>
                        <a href="<?php echo site_url('edt/voir_edt'); ?>" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-calendar me-2"></i>Voir les EDT
                        </a>
                    </div>
                </div>
            </div>

            <!-- État des tables -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-database text-info me-3" style="font-size: 1.5rem;"></i>
                            <h4 class="card-title mb-0">État des Données</h4>
                        </div>
                        <p class="card-text">Vérifier le contenu de la base de données</p>
                        <a href="<?php echo site_url('tableau_de_bord/etat_tables'); ?>" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-table me-2"></i>État des Tables
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestion des données -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-cogs text-warning me-3" style="font-size: 1.5rem;"></i>
                            <h4 class="card-title mb-0">Gestion</h4>
                        </div>
                        <p class="card-text">Outils de gestion avancée</p>
                        <a href="<?php echo site_url('tableau_de_bord/gestion_donnees'); ?>" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-tools me-2"></i>Gestion des Données
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section actions rapides -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions Rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-auto">
                                <a href="<?php echo site_url('edt/debug_dernier_tp'); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-bug me-1"></i>Debug Dernier TP
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?php echo site_url('edt/debug_creneaux_disponibles/20'); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-search me-1"></i>Test Créneaux
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?php echo site_url('edt/test_filtrage_salles'); ?>" class="btn btn-outline-info">
                                    <i class="fas fa-filter me-1"></i>Test Filtrage Salles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">Système de Gestion d'Emploi du Temps - TP © 2024</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>