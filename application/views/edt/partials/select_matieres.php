<option value="">Choisir une matière</option>
<?php foreach ($matieres as $matiere): ?>
    <option value="<?php echo $matiere->codeEnseignement; ?>">
        <?php echo $matiere->nom; ?>
    </option>
<?php endforeach; ?>
