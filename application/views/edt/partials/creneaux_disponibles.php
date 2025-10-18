<?php if (empty($creneaux)): ?>
    <div class="alert alert-warning">
        <strong>⚠️ Aucun créneau disponible</strong><br>
        Toutes les demi-journées TP sont actuellement occupées pour ce groupe.
    </div>
<?php else: ?>
    <h5>📅 Créneaux TP disponibles :</h5>
    <div class="row">
        <?php foreach ($creneaux as $creneau): ?>
            <div class="col-md-6 mb-2">
                <div class="creneau-disponible" 
                     onclick="selectionnerCreneau(this, 
                         '<?php echo $creneau['id']; ?>', 
                         '<?php echo $creneau['heure_debut']; ?>')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="jour-text"><?php echo $creneau['jour']; ?></strong><br>
                            <small class="text-muted periode-text">
                                <?php echo $creneau['periode']; ?> | 
                                <?php echo substr($creneau['heure_debut'], 0, 5); ?> - <?php echo substr($creneau['heure_fin'], 0, 5); ?>
                            </small>
                        </div>
                        <div class="text-success">
                            ✅
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <small class="text-muted">Cliquez sur un créneau pour le sélectionner</small>
<?php endif; ?>