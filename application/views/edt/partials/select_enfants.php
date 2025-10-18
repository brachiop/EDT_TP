<option value="">Choisir...</option>
<?php foreach ($enfants as $enfant): ?>
    <option value="<?php echo $enfant->codeGroupe; ?>">
        <?php echo $enfant->nom; ?>
    </option>
<?php endforeach; ?>