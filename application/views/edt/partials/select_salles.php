<option value="">Choisir une salle</option>
<?php foreach ($salles as $salle): ?>
    <option value="<?php echo $salle->codeSalle; ?>">
        <?php echo $salle->nom; ?> (<?php echo $salle->typeSalle; ?>)
    </option>
<?php endforeach; ?>