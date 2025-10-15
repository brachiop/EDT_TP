<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($title) ? $title : 'Démonstration EDT TP'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar { margin-bottom: 20px; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .table-responsive { max-height: 400px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo site_url('demo/tableau_de_bord'); ?>">EDT TP - Démo</a>
            <div class="navbar-nav">
                <a class="nav-link" href="<?php echo site_url('demo/tableau_de_bord'); ?>">Tableau de Bord</a>
                <a class="nav-link" href="<?php echo site_url('edt'); ?>">Planifier TP</a>
                <a class="nav-link" href="<?php echo site_url('demo/visualiser_edt'); ?>">Visualiser EDT</a>
                <a class="nav-link" href="<?php echo site_url('demo/etat_tables'); ?>">État des Tables</a>
                <a class="nav-link" href="<?php echo site_url('demo/peupler_base'); ?>">Peupler Base</a>
            </div>
        </div>
    </nav>
    <div class="container">