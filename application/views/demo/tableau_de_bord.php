<div class="row mb-4">
    <div class="col-12">
        <h1>Tableau de Bord</h1>
        <p class="lead">Interface de démonstration du système de planification des TP</p>
    </div>
</div>

<div class="row mb-4">
    <?php foreach ($statistiques as $nom => $valeur): ?>
    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title text-capitalize"><?php echo str_replace('_', ' ', $nom); ?></h5>
                <h2 class="text-primary"><?php echo $valeur; ?></h2>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Derniers TP ajoutés</h5>
            </div>
            <div class="card-body">
                <?php if ($derniers_tp): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Groupe TP</th>
                                <th>Matière</th>
                                <th>Salle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($derniers_tp as $tp): ?>
                            <tr>
                                <td><?php echo $tp->groupe_tp_nom; ?></td>
                                <td><?php echo $tp->matiere; ?></td>
                                <td><?php echo $tp->salle; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucun TP planifié pour le moment</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo site_url('edt'); ?>" class="btn btn-primary">Planifier un TP</a>
                    <a href="<?php echo site_url('demo/visualiser_edt'); ?>" class="btn btn-success">Visualiser un EDT</a>
                    <a href="<?php echo site_url('demo/peupler_base'); ?>" class="btn btn-warning">Peupler la base</a>
                    <a href="<?php echo site_url('demo/etat_tables'); ?>" class="btn btn-info">Voir état des tables</a>
                </div>
            </div>
        </div>
    </div>
</div>