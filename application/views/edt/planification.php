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
                            <option value="1">A</option>
                            <option value="2">B</option>
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
                                <input type="text" id="matiere" class="form-control" placeholder="Nom de la matière">
                            </div>
                            <div class="mb-3">
                                <label for="salle" class="form-label">Salle:</label>
                                <input type="text" id="salle" class="form-control" placeholder="Numéro de salle">
                            </div>
                            <button class="btn btn-primary" onclick="ajouterTP()">Ajouter le TP</button>
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

    // Données locales avec créneaux cohérents
    const donneesLocales = {
        groupesTD: {
            '1': [{id: 1, nom: 'A1'}, {id: 2, nom: 'A2'}],
            '2': [{id: 3, nom: 'B1'}, {id: 4, nom: 'B2'}]
        },
        groupesTP: {
            '1': [{id: 1, nom: 'A1a'}, {id: 2, nom: 'A1b'}],
            '2': [{id: 3, nom: 'A2a'}, {id: 4, nom: 'A2b'}],
            '3': [{id: 5, nom: 'B1a'}, {id: 6, nom: 'B1b'}],
            '4': [{id: 7, nom: 'B2a'}, {id: 8, nom: 'B2b'}]
        },
        // EDT par groupe TP - cours existants qui bloquent ces créneaux
        edtGroupes: {
            '1': [ // A1a - Ne peut pas avoir de TP Lundi 8h-10h ni Lundi 13h30-15h30
                {jour: 'Lundi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Mathématiques', salle: 'Amphi A'},
                {jour: 'Lundi', heure_debut: '13:30', heure_fin: '15:30', type: 'TD', matiere: 'TD Mathématiques', salle: 'Salle 101'}
            ],
            '2': [ // A1b - Ne peut pas avoir de TP Lundi 8h-10h ni Mardi 10h15-12h15
                {jour: 'Lundi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Mathématiques', salle: 'Amphi A'},
                {jour: 'Mardi', heure_debut: '10:15', heure_fin: '12:15', type: 'TD', matiere: 'TD Physique', salle: 'Salle 102'}
            ],
            '5': [ // B1a - Ne peut pas avoir de TP Mardi 8h-10h ni Mercredi 13h30-15h30
                {jour: 'Mardi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Physique', salle: 'Amphi B'},
                {jour: 'Mercredi', heure_debut: '13:30', heure_fin: '15:30', type: 'TD', matiere: 'TD Informatique', salle: 'Salle 103'}
            ],
            '6': [ // B1b - Ne peut pas avoir de TP Mardi 8h-10h ni Jeudi 10h15-12h15
                {jour: 'Mardi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Physique', salle: 'Amphi B'},
                {jour: 'Jeudi', heure_debut: '10:15', heure_fin: '12:15', type: 'TD', matiere: 'TD Chimie', salle: 'Labo A'}
            ]
        },
        // Tous les créneaux possibles de la semaine
        tousCreneaux: [
            {id: 1, jour: 'Lundi', heure_debut: '08:00:00', heure_fin: '10:00:00'},
            {id: 2, jour: 'Lundi', heure_debut: '10:15:00', heure_fin: '12:15:00'},
            {id: 3, jour: 'Lundi', heure_debut: '13:30:00', heure_fin: '15:30:00'},
            {id: 4, jour: 'Lundi', heure_debut: '15:45:00', heure_fin: '17:45:00'},
            {id: 5, jour: 'Mardi', heure_debut: '08:00:00', heure_fin: '10:00:00'},
            {id: 6, jour: 'Mardi', heure_debut: '10:15:00', heure_fin: '12:15:00'},
            {id: 7, jour: 'Mardi', heure_debut: '13:30:00', heure_fin: '15:30:00'},
            {id: 8, jour: 'Mardi', heure_debut: '15:45:00', heure_fin: '17:45:00'},
            {id: 9, jour: 'Mercredi', heure_debut: '08:00:00', heure_fin: '10:00:00'},
            {id: 10, jour: 'Mercredi', heure_debut: '10:15:00', heure_fin: '12:15:00'},
            {id: 11, jour: 'Mercredi', heure_debut: '13:30:00', heure_fin: '15:30:00'},
            {id: 12, jour: 'Mercredi', heure_debut: '15:45:00', heure_fin: '17:45:00'},
            {id: 13, jour: 'Jeudi', heure_debut: '08:00:00', heure_fin: '10:00:00'},
            {id: 14, jour: 'Jeudi', heure_debut: '10:15:00', heure_fin: '12:15:00'},
            {id: 15, jour: 'Jeudi', heure_debut: '13:30:00', heure_fin: '15:30:00'},
            {id: 16, jour: 'Jeudi', heure_debut: '15:45:00', heure_fin: '17:45:00'},
            {id: 17, jour: 'Vendredi', heure_debut: '08:00:00', heure_fin: '10:00:00'},
            {id: 18, jour: 'Vendredi', heure_debut: '10:15:00', heure_fin: '12:15:00'},
            {id: 19, jour: 'Vendredi', heure_debut: '13:30:00', heure_fin: '15:30:00'},
            {id: 20, jour: 'Vendredi', heure_debut: '15:45:00', heure_fin: '17:45:00'}
        ]
    };

    // Gestion des selects
    $('#section').change(function() {
        const sectionId = $(this).val();
        if(sectionId && donneesLocales.groupesTD[sectionId]) {
            $('#groupe_td').prop('disabled', false).html('<option value="">Sélectionnez un groupe TD</option>');
            donneesLocales.groupesTD[sectionId].forEach(g => $('#groupe_td').append(`<option value="${g.id}">${g.nom}</option>`));
            $('#groupe_tp').prop('disabled', true).html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
            reinitialiserAffichages();
        }
    });

    $('#groupe_td').change(function() {
        const groupeTdId = $(this).val();
        if(groupeTdId && donneesLocales.groupesTP[groupeTdId]) {
            $('#groupe_tp').prop('disabled', false).html('<option value="">Sélectionnez un groupe TP</option>');
            donneesLocales.groupesTP[groupeTdId].forEach(g => $('#groupe_tp').append(`<option value="${g.id}">${g.nom}</option>`));
            reinitialiserAffichages();
        }
    });

    $('#groupe_tp').change(function() {
        const groupeTpId = $(this).val();
        if(groupeTpId) {
            groupeTpSelectionne = groupeTpId;
            const nomGroupe = $('#groupe_tp option:selected').text();
            $('#nom-groupe').text(' - ' + nomGroupe);
            chargerDonneesAvecFiltrage(groupeTpId);
        } else {
            reinitialiserAffichages();
        }
    });

    function chargerDonneesAvecFiltrage(groupeTpId) {
        // Charger EDT existant
        const edt = donneesLocales.edtGroupes[groupeTpId] || [];
        afficherEDT(edt);
        
        // Calculer les créneaux disponibles en excluant ceux de l'EDT
        const creneauxDisponibles = calculerCreneauxDisponibles(edt);
        
        $('#debug-edt').text(edt.length + ' cours existants');
        $('#debug-creneaux').text(creneauxDisponibles.length + ' créneaux disponibles (exclut les ' + edt.length + ' créneaux occupés)');
        afficherCreneaux(creneauxDisponibles, edt);
    }

    function calculerCreneauxDisponibles(edt) {
        // Convertir les créneaux occupés en clés de comparaison
        const creneauxOccupes = edt.map(cours => {
            // Normaliser le format d'heure "08:00" -> "08:00:00"
            const heureDebut = cours.heure_debut.length === 5 ? cours.heure_debut + ':00' : cours.heure_debut;
            return `${cours.jour}_${heureDebut}`;
        });
        
        console.log('Créneaux occupés:', creneauxOccupes);
        
        // Filtrer les créneaux disponibles
        return donneesLocales.tousCreneaux.filter(creneau => {
            const cleCreneau = `${creneau.jour}_${creneau.heure_debut}`;
            const estOccupe = creneauxOccupes.includes(cleCreneau);
            
            if (estOccupe) {
                console.log(`❌ Créneau occupé exclu: ${creneau.jour} ${creneau.heure_debut}-${creneau.heure_fin}`);
            }
            
            return !estOccupe;
        });
    }

    function afficherEDT(edt) {
        if (!Array.isArray(edt)) {
            $('#affichage-edt').html('<div class="alert alert-danger">Erreur: données invalides</div>');
            return;
        }

        let html = '<table class="table table-striped"><thead><tr><th>Jour</th><th>Heure</th><th>Type</th><th>Matière</th><th>Salle</th></tr></thead><tbody>';
        
        if (edt.length === 0) {
            html += '<tr><td colspan="5" class="text-center text-muted">Aucun cours planifié</td></tr>';
        } else {
            edt.forEach(function(cours) {
                const badgeClass = cours.type === 'TD' ? 'bg-success' : cours.type === 'TP' ? 'bg-warning' : 'bg-primary';
                html += `<tr>
                    <td>${cours.jour}</td>
                    <td>${cours.heure_debut} - ${cours.heure_fin}</td>
                    <td><span class="badge ${badgeClass}">${cours.type}</span></td>
                    <td>${cours.matiere}</td>
                    <td>${cours.salle}</td>
                </tr>`;
            });
        }
        
        html += '</tbody></table>';
        $('#affichage-edt').html(html);
    }

    function afficherCreneaux(creneaux, edt) {
        if (!Array.isArray(creneaux)) {
            $('#liste-creneaux').html('<div class="alert alert-danger">Erreur: données invalides</div>');
            return;
        }

        $('#liste-creneaux').empty();
        
        if (creneaux.length === 0) {
            $('#liste-creneaux').html('<div class="alert alert-warning">Aucun créneau disponible - tous les créneaux sont occupés</div>');
            return;
        }
        
        // Afficher d'abord les créneaux occupés (pour information)
        if (edt && edt.length > 0) {
            $('#liste-creneaux').append('<div class="mb-3"><strong>Créneaux occupés (non disponibles):</strong></div>');
            edt.forEach(function(cours) {
                $('#liste-creneaux').append(`
                    <div class="creneau occupe">
                        <strong>${cours.jour}</strong><br>
                        ${cours.heure_debut} - ${cours.heure_fin}<br>
                        <small class="text-muted">${cours.type} - ${cours.matiere}</small>
                    </div>
                `);
            });
            $('#liste-creneaux').append('<div class="mb-3 mt-3"><strong>Créneaux disponibles:</strong></div>');
        }
        
        // Afficher les créneaux disponibles
        creneaux.forEach(function(creneau) {
            $('#liste-creneaux').append(`
                <div class="creneau disponible">
                    <strong>${creneau.jour}</strong><br>
                    ${creneau.heure_debut.substring(0, 5)} - ${creneau.heure_fin.substring(0, 5)}
                    <button class="btn btn-sm btn-primary mt-2" onclick="selectionnerCreneau(${creneau.id}, this)">Sélectionner</button>
                </div>
            `);
        });
    }

    function selectionnerCreneau(creneauId, element) {
        creneauSelectionne = creneauId;
        $('.creneau').removeClass('selected');
        $(element).parent().addClass('selected');
        $('#form-ajout-tp').show();
    }

    function reinitialiserAffichages() {
        $('#liste-creneaux').html('<p class="text-muted">Sélectionnez un groupe TP pour voir les créneaux disponibles</p>');
        $('#affichage-edt').html('<p class="text-muted">Sélectionnez un groupe TP pour voir son EDT</p>');
        $('#form-ajout-tp').hide();
        $('#debug-creneaux').text('');
        $('#debug-edt').text('');
        $('#nom-groupe').text('');
    }

    function ajouterTP() {
        const matiere = $('#matiere').val();
        const salle = $('#salle').val();

        if(!creneauSelectionne || !matiere || !groupeTpSelectionne) {
            alert('Veuillez remplir tous les champs et sélectionner un créneau');
            return;
        }

        alert('TP ajouté avec succès!\n\nMatière: ' + matiere + '\nSalle: ' + salle + '\nGroupe TP: ' + $('#groupe_tp option:selected').text());
        
        $('#form-ajout-tp').hide();
        $('#matiere').val('');
        $('#salle').val('');
        $('.creneau').removeClass('selected');
        creneauSelectionne = null;
    }
    </script>
</body>
</html>