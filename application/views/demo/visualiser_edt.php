<div class="row">
    <div class="col-12">
        <h1>Visualiser les Emplois du Temps</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Sélectionner un groupe TP</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo base_url(); ?>demo/visualiser_edt">
                    <div class="row">
                        <div class="col-md-8">
                            <select name="groupe_tp_id" class="form-select">
                                <option value="">Choisir un groupe TP</option>
                                <?php foreach ($groupes_tp as $groupe): ?>
                                <option value="<?php echo $groupe->id; ?>" 
                                    <?php echo (isset($_GET['groupe_tp_id']) && $_GET['groupe_tp_id'] == $groupe->id) ? 'selected' : ''; ?>>
                                    <?php echo $groupe->nom . ' (TD: ' . $groupe->groupe_td_nom . ', Section: ' . $groupe->section_nom . ')'; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Afficher l'EDT</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($edt_selectionne) && !empty($edt_selectionne)): ?>
        <div class="card">
            <div class="card-header">
                <h5>EDT du groupe <?php echo $groupe_tp_selectionne->nom; ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Jour</th>
                                <th>Heure</th>
                                <th>Type</th>
                                <th>Matière</th>
                                <th>Salle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($edt_selectionne as $cours): ?>
                            <tr>
                                <td><?php echo $cours['jour']; ?></td>
                                <td><?php echo $cours['heure_debut'] . ' - ' . $cours['heure_fin']; ?></td>
                                <td>
                                    <span class="badge 
                                        <?php echo $cours['type'] == 'CM' ? 'bg-primary' : ($cours['type'] == 'TD' ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo $cours['type']; ?>
                                    </span>
                                </td>
                                <td><?php echo $cours['matiere']; ?></td>
                                <td><?php echo $cours['salle']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (isset($_GET['groupe_tp_id'])): ?>
        <div class="alert alert-info">
            Aucun cours planifié pour ce groupe.
        </div>
        <?php endif; ?>
    </div>
</div>