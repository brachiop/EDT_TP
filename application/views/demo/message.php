<div class="row">
    <div class="col-12">
        <div class="alert alert-<?php echo isset($message_type) ? $message_type : 'info'; ?>">
            <h4 class="alert-heading">
                <?php echo (isset($message_type) && $message_type == 'success') ? 'Succès !' : 'Information'; ?>
            </h4>
            <p class="mb-0"><?php echo isset($message) ? $message : 'Opération terminée'; ?></p>
        </div>
        <a href="<?php echo base_url(); ?>demo/tableau_de_bord" class="btn btn-primary">Retour au tableau de bord</a>
    </div>
</div>