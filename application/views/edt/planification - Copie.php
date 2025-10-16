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
                        <select id="groupe_td" class="form-select">
                            <option value="">Sélectionnez d'abord une section</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="groupe_tp" class="form-label">Groupe TP:</label>
                        <select id="groupe_tp" class="form-select">
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
                            <button class="btn btn-success" onclick="ajouterTP()">
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

    // Données locales COMPLÈTES
    const donneesLocales = {
        // Sections
        sections: [
            {id: 1, nom: 'A'},
            {id: 2, nom: 'B'}
        ],
        // Groupes TD par section
        groupesTD: {
            '1': [ // Section A
                {id: 1, nom: 'A1', section_id: 1},
                {id: 2, nom: 'A2', section_id: 1}
            ],
            '2': [ // Section B
                {id: 3, nom: 'B1', section_id: 2},
                {id: 4, nom: 'B2', section_id: 2}
            ]
        },
        // Groupes TP par groupe TD
        groupesTP: {
            '1': [ // TD A1
                {id: 1, nom: 'A1a', groupe_td_id: 1},
                {id: 2, nom: 'A1b', groupe_td_id: 1}
            ],
            '2': [ // TD A2
                {id: 3, nom: 'A2a', groupe_td_id: 2},
                {id: 4, nom: 'A2b', groupe_td_id: 2}
            ],
            '3': [ // TD B1
                {id: 5, nom: 'B1a', groupe_td_id: 3},
                {id: 6, nom: 'B1b', groupe_td_id: 3}
            ],
            '4': [ // TD B2
                {id: 7, nom: 'B2a', groupe_td_id: 4},
                {id: 8, nom: 'B2b', groupe_td_id: 4}
            ]
        },
        // EDT existant par groupe TP
        edtGroupes: {
            '1': [ // A1a - Cours existants
                {jour: 'Lundi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Mathématiques', salle: 'Amphi A'},
                {jour: 'Lundi', heure_debut: '13:30', heure_fin: '15:30', type: 'TD', matiere: 'TD Mathématiques', salle: 'Salle 101'}
            ],
            '2': [ // A1b
                {jour: 'Lundi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Mathématiques', salle: 'Amphi A'},
                {jour: 'Mardi', heure_debut: '10:15', heure_fin: '12:15', type: 'TD', matiere: 'TD Physique', salle: 'Salle 102'}
            ],
            '5': [ // B1a
                {jour: 'Mardi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Physique', salle: 'Amphi B'},
                {jour: 'Mercredi', heure_debut: '13:30', heure_fin: '15:30', type: 'TD', matiere: 'TD Informatique', salle: 'Salle 103'}
            ],
            '6': [ // B1b
                {jour: 'Mardi', heure_debut: '08:00', heure_fin: '10:00', type: 'CM', matiere: 'Physique', salle: 'Amphi B'},
                {jour: 'Jeudi', heure_debut: '10:15', heure_fin: '12:15', type: 'TD', matiere: 'TD Chimie', salle: 'Labo A'}
            ]
        },
        // Tous les créneaux de la semaine
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

    // Initialisation au chargement de la page
    $(document).ready(function() {
        console.log('✅ Page chargée - Données locales disponibles');
        initialiserSelects();
    });

    function initialiserSelects() {
        // Section
        $('#section').html('<option value="">Sélectionnez une section</option>');
        donneesLocales.sections.forEach(section => {
            $('#section').append(`<option value="${section.id}">${section.nom}</option>`);
        });

        // Groupes TD (vide au départ)
        $('#groupe_td').html('<option value="">Sélectionnez d\'abord une section</option>');
        
        // Groupes TP (vide au départ)
        $('#groupe_tp').html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
    }

    // Gestion des selects avec données locales
    $('#section').change(function() {
        const sectionId = $(this).val();
        console.log('Section sélectionnée:', sectionId);
        
        if(sectionId && donneesLocales.groupesTD[sectionId]) {
            $('#groupe_td').html('<option value="">Sélectionnez un groupe TD</option>');
            donneesLocales.groupesTD[sectionId].forEach(groupe => {
                $('#groupe_td').append(`<option value="${groupe.id}">${groupe.nom}</option>`);
            });
            
            $('#groupe_tp').html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
            reinitialiserAffichages();
        } else {
            $('#groupe_td').html('<option value="">Sélectionnez d\'abord une section</option>');
            $('#groupe_tp').html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
            reinitialiserAffichages();
        }
    });

    $('#groupe_td').change(function() {
        const groupeTdId = $(this).val();
        console.log('Groupe TD sélectionné:', groupeTdId);
        
        if(groupeTdId && donneesLocales.groupesTP[groupeTdId]) {
            $('#groupe_tp').html('<option value="">Sélectionnez un groupe TP</option>');
            donneesLocales.groupesTP[groupeTdId].forEach(groupe => {
                $('#groupe_tp').append(`<option value="${groupe.id}">${groupe.nom}</option>`);
            });
            reinitialiserAffichages();
        } else {
            $('#groupe_tp').html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
            reinitialiserAffichages();
        }
    });

    $('#groupe_tp').change(function() {
        const groupeTpId = $(this).val();
        console.log('Groupe TP sélectionné:', groupeTpId);
        
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
        console.log('Chargement des données pour le groupe TP:', groupeTpId);
        
        // Charger EDT existant
        const edt = donneesLocales.edtGroupes[groupeTpId] || [];
        console.log('EDT chargé:', edt);
        afficherEDT(edt);
        
        // Calculer les créneaux disponibles
        const creneauxDisponibles = calculerCreneauxDisponibles(edt);
        console.log('Créneaux disponibles:', creneauxDisponibles);
        
        $('#debug-edt').text(edt.length + ' cours existants');
        $('#debug-creneaux').text(creneauxDisponibles.length + ' créneaux disponibles');
        afficherCreneaux(creneauxDisponibles, edt);
    }

    function calculerCreneauxDisponibles(edt) {
        // Convertir les créneaux occupés en clés de comparaison
        const creneauxOccupes = edt.map(cours => {
            // Normaliser le format d'heure "08:00" -> "08:00:00"
            const heureDebut = cours.heure_debut.length === 5 ? cours.heure_debut + ':00' : cours.heure_debut;
            return `${cours.jour}_${heureDebut}`;
        });
        
        console.log('Créneaux occupés à exclure:', creneauxOccupes);
        
        // Filtrer les créneaux disponibles
        return donneesLocales.tousCreneaux.filter(creneau => {
            const cleCreneau = `${creneau.jour}_${creneau.heure_debut}`;
            const estOccupe = creneauxOccupes.includes(cleCreneau);
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
                // Formater l'heure
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

        // Simuler l'ajout en base (pour la démo)
        const nouveauTP = {
            id: Date.now(),
            groupe_tp_id: groupeTpSelectionne,
            creneau_id: creneauSelectionne,
            matiere: matiere,
            salle: salle,
            type: 'TP'
        };

        // Ajouter aux données locales
        if (!donneesLocales.edtGroupes[groupeTpSelectionne]) {
            donneesLocales.edtGroupes[groupeTpSelectionne] = [];
        }
        donneesLocales.edtGroupes[groupeTpSelectionne].push(nouveauTP);

        alert('✅ TP ajouté avec succès!\n\nMatière: ' + matiere + '\nSalle: ' + salle + '\nGroupe TP: ' + $('#groupe_tp option:selected').text());
        
        // Recharger l'affichage
        chargerDonneesAvecFiltrage(groupeTpSelectionne);
        
        // Réinitialiser le formulaire
        $('#form-ajout-tp').hide();
        $('#matiere').val('');
        $('#salle').val('');
        $('.creneau').removeClass('selected');
        creneauSelectionne = null;
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
    </script>
</body>
</html>