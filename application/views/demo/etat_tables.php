<div class="row">
    <div class="col-12">
        <h1>État des Tables de la Base de Données</h1>
        
        <?php foreach ($tables as $nom_table => $donnees): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5>Table : <?php echo $nom_table; ?> (<?php echo count($donnees); ?> enregistrements)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($donnees)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <?php foreach (array_keys((array)$donnees[0]) as $colonne): ?>
                                <th><?php echo $colonne; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donnees as $ligne): ?>
                            <tr>
                                <?php foreach ((array)$ligne as $valeur): ?>
                                <td><?php echo $valeur; ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Table vide</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>