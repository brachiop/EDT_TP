<!DOCTYPE html>
<html>
<head>
    <title>Planification des TP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .creneau { padding: 10px; margin: 5px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; }
        .creneau.disponible { background: #d4edda; border-color: #c3e6cb; }
        .creneau.selected { background: #cce5ff; border-color: #b8daff; }
        .creneau.occupe { background: #f8d7da; border-color: #f5c6cb; opacity: 0.6; }
        .loading { opacity: 0.6; pointer-events: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo base_url(); ?>demo/tableau_de_bord">EDT TP - Planification</a>
            <div class="navbar-nav">
                <a class="nav-link" href="<?php echo base_url(); ?>demo/tableau_de_bord">← Retour au Tableau de Bord</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Planification des Travaux Pratiques</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="section" class="form-label">Section:</label>
                        <select id="section" class="form-select">
                            <option value="">Sélectionnez une section</option>
                            <?php foreach($sections as $section): ?>
                            <option value="<?php echo $section->id; ?>"><?php echo $section->nom; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="groupe_td" class="form-label">Groupe TD:</label>
                        <select id="groupe_td" class="form-select" disabled>
                            <option value="">Sélectionnez d'abord une section</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="groupe_tp" class="form-label">Groupe TP:</label>
                        <select id="groupe_tp" class="form-select" disabled>
                            <option value="">Sélectionnez d'abord un groupe TD</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Créneaux disponibles</h5>
                        <small id="debug-creneaux" class="text-muted"></small>
                    </div>
                    <div class="card-body">
                        <div id="liste-creneaux">
                            <p class="text-muted">Sélectionnez un groupe TP pour voir les créneaux disponibles</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div id="form-ajout-tp" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ajouter un TP</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="matiere" class="form-label">Matière:</label>
                                <input type="text" id="matiere" class="form-control" placeholder="Nom de la matière" required>
                            </div>
                            <div class="mb-3">
                                <label for="salle" class="form-label">Salle:</label>
                                <input type="text" id="salle" class="form-control" placeholder="Numéro de salle" required>
                            </div>
                            <button class="btn btn-success" onclick="ajouterTP()" id="btn-ajouter-tp">
                                ✅ Planifier ce TP
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>Emploi du temps du groupe <span id="nom-groupe"></span></h5>
                <small id="debug-edt" class="text-muted"></small>
            </div>
            <div class="card-body">
                <div id="affichage-edt">
                    <p class="text-muted">Sélectionnez un groupe TP pour voir son EDT</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    let creneauSelectionne = null;
    let groupeTpSelectionne = null;

    // Gestion des selects avec AJAX
    $('#section').change(function() {
        const sectionId = $(this).val();
        if(sectionId) {
            // Afficher loading
            $('#groupe_td').prop('disabled', true).addClass('loading').html('<option value="">Chargement...</option>');
            
            $.ajax({
                url: '<?php echo base_url(); ?>edt/get_groupes_td?section_id=' + sectionId,
                dataType: 'json',
                success: function(groupes) {
                    $('#groupe_td').html('<option value="">Sélectionnez un groupe TD</option>');
                    groupes.forEach(g => {
                        $('#groupe_td').append(`<option value="${g.id}">${g.nom}</option>`);
                    });
                    $('#groupe_td').prop('disabled', false).removeClass('loading');
                    $('#groupe_tp').prop('disabled', true).html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
                    reinitialiserAffichages();
                },
                error: function(xhr, status, error) {
                    console.error('Erreur chargement groupes TD:', error);
                    $('#groupe_td').html('<option value="">Erreur de chargement</option>').removeClass('loading');
                    alert('Erreur lors du chargement des groupes TD');
                }
            });
        } else {
            $('#groupe_td').prop('disabled', true).html('<option value="">Sélectionnez d\'abord une section</option>');
            $('#groupe_tp').prop('disabled', true).html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
            reinitialiserAffichages();
        }
    });

    $('#groupe_td').change(function() {
        const groupeTdId = $(this).val();
        if(groupeTdId) {
            // Afficher loading
            $('#groupe_tp').prop('disabled', true).addClass('loading').html('<option value="">Chargement...</option>');
            
            $.ajax({
                url: '<?php echo base_url(); ?>edt/get_groupes_tp?groupe_td_id=' + groupeTdId,
                dataType: 'json',
                success: function(groupes) {
                    $('#groupe_tp').html('<option value="">Sélectionnez un groupe TP</option>');
                    groupes.forEach(g => {
                        $('#groupe_tp').append(`<option value="${g.id}">${g.nom}</option>`);
                    });
                    $('#groupe_tp').prop('disabled', false).removeClass('loading');
                    reinitialiserAffichages();
                },
                error: function(xhr, status, error) {
                    console.error('Erreur chargement groupes TP:', error);
                    $('#groupe_tp').html('<option value="">Erreur de chargement</option>').removeClass('loading');
                    alert('Erreur lors du chargement des groupes TP');
                }
            });
        } else {
            $('#groupe_tp').prop('disabled', true).html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
            reinitialiserAffichages();
        }
    });

    $('#groupe_tp').change(function() {
        const groupeTpId = $(this).val();
        if(groupeTpId) {
            groupeTpSelectionne = groupeTpId;
            const nomGroupe = $('#groupe_tp option:selected').text();
            $('#nom-groupe').text(' - ' + nomGroupe);
            
            // Afficher loading
            $('#liste-creneaux').html('<div class="text-center"><div class="spinner-border" role="status"></div><br>Chargement...</div>');
            $('#affichage-edt').html('<div class="text-center"><div class="spinner-border" role="status"></div><br>Chargement...</div>');
            
            // Charger l'EDT et les créneaux
            chargerEDT(groupeTpId);
            chargerCreneauxDisponibles(groupeTpId);
        } else {
            reinitialiserAffichages();
        }
    });

    function chargerEDT(groupeTpId) {
        $.ajax({
            url: '<?php echo base_url(); ?>edt/get_edt_groupe?groupe_tp_id=' + groupeTpId,
            dataType: 'json',
            success: function(edt) {
                console.log('EDT chargé:', edt);
                $('#debug-edt').text(edt.length + ' cours planifiés');
                afficherEDT(edt);
            },
            error: function(xhr, status, error) {
                console.error('Erreur chargement EDT:', error);
                $('#debug-edt').text('Erreur de chargement');
                $('#affichage-edt').html('<div class="alert alert-danger">Erreur lors du chargement de l\'EDT</div>');
            }
        });
    }

    function chargerCreneauxDisponibles(groupeTpId) {
        $.ajax({
            url: '<?php echo base_url(); ?>edt/get_creneaux_disponibles?groupe_tp_id=' + groupeTpId,
            dataType: 'json',
            success: function(creneaux) {
                console.log('Créneaux disponibles chargés:', creneaux);
                $('#debug-creneaux').text(creneaux.length + ' créneaux disponibles');
                afficherCreneaux(creneaux);
            },
            error: function(xhr, status, error) {
                console.error('Erreur chargement créneaux:', error);
                $('#debug-creneaux').text('Erreur de chargement');
                $('#liste-creneaux').html('<div class="alert alert-danger">Erreur lors du chargement des créneaux</div>');
            }
        });
    }

    function afficherEDT(edt) {
        if (!Array.isArray(edt)) {
            $('#affichage-edt').html('<div class="alert alert-danger">Erreur: format de données invalide</div>');
            return;
        }

        let html = '<table class="table table-striped table-sm"><thead><tr><th>Jour</th><th>Heure</th><th>Type</th><th>Matière</th><th>Salle</th></tr></thead><tbody>';
        
        if (edt.length === 0) {
            html += '<tr><td colspan="5" class="text-center text-muted">Aucun cours planifié</td></tr>';
        } else {
            // Trier par jour et heure
            edt.sort((a, b) => {
                const jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                const jourA = jours.indexOf(a.jour);
                const jourB = jours.indexOf(b.jour);
                if (jourA !== jourB) return jourA - jourB;
                return a.heure_debut.localeCompare(b.heure_debut);
            });
            
            edt.forEach(function(cours) {
                const badgeClass = cours.type === 'TD' ? 'bg-success' : cours.type === 'TP' ? 'bg-warning' : 'bg-primary';
                // Formater l'heure (enlever les secondes si présentes)
                const heure_debut = cours.heure_debut.length > 5 ? cours.heure_debut.substring(0, 5) : cours.heure_debut;
                const heure_fin = cours.heure_fin.length > 5 ? cours.heure_fin.substring(0, 5) : cours.heure_fin;
                
                html += `<tr>
                    <td>${cours.jour}</td>
                    <td>${heure_debut} - ${heure_fin}</td>
                    <td><span class="badge ${badgeClass}">${cours.type}</span></td>
                    <td>${cours.matiere}</td>
                    <td>${cours.salle}</td>
                </tr>`;
            });
        }
        
        html += '</tbody></table>';
        $('#affichage-edt').html(html);
    }

    function afficherCreneaux(creneaux) {
        if (!Array.isArray(creneaux)) {
            $('#liste-creneaux').html('<div class="alert alert-danger">Erreur: format de données invalide</div>');
            return;
        }

        $('#liste-creneaux').empty();
        
        if (creneaux.length === 0) {
            $('#liste-creneaux').html('<div class="alert alert-warning">Aucun créneau disponible pour ce groupe</div>');
            return;
        }
        
        // Trier par jour et heure
        creneaux.sort((a, b) => {
            const jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            const jourA = jours.indexOf(a.jour);
            const jourB = jours.indexOf(b.jour);
            if (jourA !== jourB) return jourA - jourB;
            return a.heure_debut.localeCompare(b.heure_debut);
        });
        
        creneaux.forEach(function(creneau) {
            // Formater l'heure (enlever les secondes)
            const heure_debut = creneau.heure_debut.substring(0, 5);
            const heure_fin = creneau.heure_fin.substring(0, 5);
            
            $('#liste-creneaux').append(`
                <div class="creneau disponible">
                    <strong>${creneau.jour}</strong><br>
                    ${heure_debut} - ${heure_fin}
                    <button class="btn btn-sm btn-primary mt-2" onclick="selectionnerCreneau(${creneau.id}, this)">
                        Sélectionner
                    </button>
                </div>
            `);
        });
    }

    function selectionnerCreneau(creneauId, element) {
        creneauSelectionne = creneauId;
        $('.creneau').removeClass('selected');
        $(element).parent().addClass('selected');
        $('#form-ajout-tp').show();
        console.log('Créneau sélectionné:', creneauId);
    }

    function ajouterTP() {
        const matiere = $('#matiere').val().trim();
        const salle = $('#salle').val().trim();

        if(!creneauSelectionne || !matiere || !salle || !groupeTpSelectionne) {
            alert('Veuillez remplir tous les champs et sélectionner un créneau');
            return;
        }

        // Désactiver le bouton pendant l'ajout
        $('#btn-ajouter-tp').prop('disabled', true).text('Ajout en cours...');
        
        $.ajax({
            url: '<?php echo base_url(); ?>edt/planifier_tp',
            method: 'POST',
            data: {
                groupe_tp_id: groupeTpSelectionne,
                creneau_id: creneauSelectionne,
                matiere: matiere,
                salle: salle
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('✅ ' + response.message);
                    // Recharger les données
                    chargerEDT(groupeTpSelectionne);
                    chargerCreneauxDisponibles(groupeTpSelectionne);
                    // Réinitialiser le formulaire
                    $('#form-ajout-tp').hide();
                    $('#matiere').val('');
                    $('#salle').val('');
                    $('.creneau').removeClass('selected');
                    creneauSelectionne = null;
                } else {
                    alert('❌ Erreur: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur ajout TP:', error);
                alert('❌ Erreur lors de l\'ajout du TP');
            },
            complete: function() {
                $('#btn-ajouter-tp').prop('disabled', false).text('✅ Planifier ce TP');
            }
        });
    }

    function reinitialiserAffichages() {
        $('#liste-creneaux').html('<p class="text-muted">Sélectionnez un groupe TP pour voir les créneaux disponibles</p>');
        $('#affichage-edt').html('<p class="text-muted">Sélectionnez un groupe TP pour voir son EDT</p>');
        $('#form-ajout-tp').hide();
        $('#debug-creneaux').text('');
        $('#debug-edt').text('');
        $('#nom-groupe').text('');
        creneauSelectionne = null;
    }

    // Debug: afficher les erreurs dans la console
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('Erreur AJAX:', {
            url: settings.url,
            error: error,
            status: xhr.status,
            responseText: xhr.responseText
        });
    });
    </script>
</body>
</html>