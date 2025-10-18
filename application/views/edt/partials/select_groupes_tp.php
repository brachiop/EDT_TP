<select name="groupe_tp_id" class="form-select" id="select-groupe-tp" onchange="chargerContenu()" required>
    <option value="">Choisir un groupe TP</option>
    <?php foreach ($groupes as $groupe): ?>
        <option value="<?php echo $groupe->codeGroupe; ?>">
            <?php echo $groupe->nom; ?>
        </option>
    <?php endforeach; ?>
</select>