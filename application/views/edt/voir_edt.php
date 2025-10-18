<!DOCTYPE html>
<html>
<head>
    <title>Visualisation EDT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">📋 Emploi du Temps</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Sélection du groupe</h5>
            </div>
            <div class="card-body">
                <form method="get" action="<?php echo site_url('edt/voir_edt'); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Section:</label>
                            <select name="section_id" class="form-select" onchange="chargerGroupesTPView(this.value)">
                                <option value="">Choisir une section</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?php echo $section->codeGroupe; ?>"><?php echo $section->nom; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Groupe TP:</label>
                            <select name="groupe_tp_id" class="form-select" id="select-groupe-tp-view" onchange="this.form.submit()">
                                <option value="">Choisir d'abord la section</option>
                                <?php if ($groupe_tp_id): ?>
                                    <option value="<?php echo $groupe_tp_id; ?>" selected>
                                        Groupe sélectionné
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($groupe_tp_id && !empty($edt)): ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">EDT du Groupe</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Durée</th>
                                    <th>Matière</th>
                                    <th>Salle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($edt as $seance): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($seance->dateSeance)); ?></td>
                                        <td>
                                            <?php 
                                                $heure_int = $seance->heureSeance;
                                                $heures = floor($heure_int / 100);
                                                $minutes = $heure_int % 100;
                                                echo sprintf('%02d:%02d', $heures, $minutes);
                                            ?>
                                        </td>
                                        <td><?php echo $seance->dureeSeance; ?> min</td>
                                        <td><?php echo $seance->matiere; ?></td>
                                        <td><?php echo $seance->salle; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($groupe_tp_id && empty($edt)): ?>
            <div class="alert alert-warning text-center">
                <h5>📭 Aucune séance planifiée</h5>
                <p class="mb-0">Ce groupe n'a pas encore de TP planifié.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <h5>ℹ️ Sélectionnez un groupe</h5>
                <p class="mb-0">Choisissez une section et un groupe TP pour voir son emploi du temps.</p>
            </div>
        <?php endif; ?>
        
        <div class="mt-3">
            <a href="<?php echo site_url('edt'); ?>" class="btn btn-primary">
                ← Retour à la planification
            </a>
        </div>
    </div>

    <script>
    function chargerGroupesTPView(sectionId) {
        if (!sectionId) return;
        
        fetch('<?php echo site_url('edt/get_groupes_tp/'); ?>' + sectionId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('select-groupe-tp-view').innerHTML = html;
            });
    }
    </script>
</body>
</html>