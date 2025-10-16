<!DOCTYPE html>
<html>
<head>
    <title>Planification des TP - Demi-journées</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .container { max-width: 1400px; }
        .demi-journee { 
            padding: 15px; 
            margin: 8px; 
            border-radius: 8px; 
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .demi-journee.matin { 
            background: linear-gradient(135deg, #e8f5e8, #c8e6c9); 
            border-left: 5px solid #4caf50;
        }
        .demi-journee.apres-midi { 
            background: linear-gradient(135deg, #f3e5f5, #e1bee7); 
            border-left: 5px solid #9c27b0;
        }
        .demi-journee.disponible:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .demi-journee.selected { 
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-left: 5px solid #ffc107;
        }
        .demi-journee.occupe { 
            background: #f5f5f5; 
            color: #999;
            opacity: 0.6; 
            cursor: not-allowed;
        }
        .heure-display { 
            font-size: 1.1em; 
            font-weight: bold; 
            color: #333;
        }
        .jour-display { 
            font-size: 1.2em; 
            font-weight: bold; 
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .statut-badge { font-size: 0.8em; }
        .indisponible-message { font-size: 0.85em; color: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo base_url(); ?>demo/tableau_de_bord">📅 Planification des TP - Demi-journées</a>
            <div class="navbar-nav">
                <a class="nav-link" href="<?php echo base_url(); ?>demo/tableau_de_bord">← Tableau de Bord</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="my-4">Planification des Travaux Pratiques</h1>
        
        <div class="alert alert-info">
            <strong>ℹ️ Seules les demi-journées complètes sont disponibles pour les TP :</strong><br>
            • <strong>Matin</strong> : 8h30 - 12h00 (tous les jours sauf si occupé)<br>
            • <strong>Après-midi</strong> : 14h30 - 18h00 (du lundi au vendredi uniquement)
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Sélection du Groupe et de la Matière</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="section" class="form-label">Section:</label>
                        <select id="section" class="form-select">
                            <option value="">Sélectionnez une section</option>
                            <option value="1">A</option>
                            <option value="2">B</option>
                            <option value="3">C</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="groupe_td" class="form-label">Groupe TD:</label>
                        <select id="groupe_td" class="form-select" disabled>
                            <option value="">Section d'abord</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="groupe_tp" class="form-label">Groupe TP:</label>
                        <select id="groupe_tp" class="form-select" disabled>
                            <option value="">Groupe TD d'abord</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="matiere" class="form-label">Matière TP:</label>
                        <select id="matiere" class="form-select" disabled>
                            <option value="">Groupe TP d'abord</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Demi-journées TP disponibles</h5>
                        <div>
                            <span class="badge bg-light text-dark me-2">☀️ Matin 8h30-12h</span>
                            <span class="badge bg-light text-dark">🌙 Après-midi 14h30-18h</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="liste-demi-journees">
                            <p class="text-muted text-center py-4">
                                Sélectionnez un groupe TP et une matière pour voir les demi-journées disponibles
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div id="form-ajout-tp" style="display: none;">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Planification TP - <span id="nom-matiere"></span></h5>
                            <div id="info-demi-journee-selectionnee" class="text-light mt-2"></div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="seance_numero" class="form-label">Numéro de séance:</label>
                                    <select id="seance_numero" class="form-select">
                                        <option value="">Sélectionnez</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="salle" class="form-label">Salle:</label>
                                    <select id="salle" class="form-select">
                                        <option value="">Sélectionnez une séance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label for="semaine_debut" class="form-label">Semaine de début:</label>
                                    <input type="number" id="semaine_debut" class="form-control" min="1" max="52" placeholder="Ex: 5">
                                </div>
                                <div class="col-md-6">
                                    <label for="semaine_fin" class="form-label">Semaine de fin:</label>
                                    <input type="number" id="semaine_fin" class="form-control" readonly>
                                    <small class="text-muted" id="duree-seance"></small>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="btn btn-success btn-lg w-100 py-3" onclick="ajouterTP()" id="btn-ajouter-tp">
                                    ✅ Planifier cette Demi-journée de TP
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Emploi du temps - <span id="nom-groupe"></span></h5>
            </div>
            <div class="card-body">
                <div id="affichage-edt-complet">
                    <p class="text-muted text-center py-4">Sélectionnez un groupe TP pour voir son emploi du temps</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    let demiJourneeSelectionnee = null;
    let groupeTpSelectionne = null;
    let matiereSelectionnee = null;
    let infoMatiere = null;
    let infoDemiJourneeSelectionnee = null;

    const matieresData = {
        '1': { nom: 'Biologie', seances: 2, duree: 3, salles: ['102', '104'] },
        '2': { nom: 'Géologie', seances: 3, duree: 2, salles: ['121', '122', '124'] },
        '3': { nom: 'Physique', seances: 2, duree: 3, salles: ['31', '32'] },
        '4': { nom: 'Chimie', seances: 1, duree: 6, salles: ['61', '71'] }
    };

    // Gestion des selects
    $('#section').change(chargerGroupesTD);
    $('#groupe_td').change(chargerGroupesTP);
    $('#groupe_tp').change(function() {
        const groupeTpId = $(this).val();
        if (groupeTpId) {
            groupeTpSelectionne = groupeTpId;
            $('#nom-groupe').text($('#groupe_tp option:selected').text());
            chargerMatieres();
            afficherEDTComplet(groupeTpId);
        }
    });

    $('#matiere').change(function() {
        matiereSelectionnee = $(this).val();
        if (matiereSelectionnee) {
            infoMatiere = matieresData[matiereSelectionnee];
            $('#nom-matiere').text(infoMatiere.nom);
            chargerDemiJourneesDisponibles();
            initialiserFormulaire();
        }
    });

    function chargerGroupesTD() {
        const sectionId = $('#section').val();
        if (sectionId) {
            // Simulation groupes TD
            const groupes = [];
            for (let i = 1; i <= 5; i++) {
                groupes.push({ id: (sectionId-1)*5 + i, nom: sectionId + '' + i });
            }
            
            $('#groupe_td').html('<option value="">Sélectionnez un groupe TD</option>');
            groupes.forEach(g => $('#groupe_td').append(`<option value="${g.id}">${g.nom}</option>`));
            $('#groupe_td').prop('disabled', false);
            
            reinitialiserSelectsSuivants();
        }
    }

    function chargerGroupesTP() {
        const groupeTdId = $('#groupe_td').val();
        if (groupeTdId) {
            // Simulation groupes TP
            const groupes = [
                { id: groupeTdId * 4 - 3, nom: $('#groupe_td option:selected').text() + 'a' },
                { id: groupeTdId * 4 - 2, nom: $('#groupe_td option:selected').text() + 'b' },
                { id: groupeTdId * 4 - 1, nom: $('#groupe_td option:selected').text() + 'c' },
                { id: groupeTdId * 4, nom: $('#groupe_td option:selected').text() + 'd' }
            ];
            
            $('#groupe_tp').html('<option value="">Sélectionnez un groupe TP</option>');
            groupes.forEach(g => $('#groupe_tp').append(`<option value="${g.id}">${g.nom}</option>`));
            $('#groupe_tp').prop('disabled', false);
            
            reinitialiserSelectsSuivants();
        }
    }

    function chargerMatieres() {
        $('#matiere').html('<option value="">Sélectionnez une matière</option>');
        for (const [id, matiere] of Object.entries(matieresData)) {
            $('#matiere').append(`<option value="${id}">${matiere.nom}</option>`);
        }
        $('#matiere').prop('disabled', false);
    }

    function chargerDemiJourneesDisponibles() {
        if (!groupeTpSelectionne || !matiereSelectionnee) return;
        
        // Appel AJAX pour récupérer les vraies données
        $.ajax({
            url: '<?php echo base_url(); ?>edt/get_demi_journees_tp_disponibles?groupe_tp_id=' + groupeTpSelectionne,
            dataType: 'json',
            success: function(demiJournees) {
                afficherDemiJournees(demiJournees);
            },
            error: function() {
                // Fallback avec données simulées si AJAX échoue
                chargerDemiJourneesSimulees();
            }
        });
    }

    function chargerDemiJourneesSimulees() {
        // Données simulées - UNIQUEMENT des demi-journées TP complètes
        const demiJourneesDisponibles = [
            {id: 1, periode: 'Matin', jour: 'Lundi', heure_debut: '08:30:00', heure_fin: '12:00:00', disponible: true},
            {id: 2, periode: 'Après-midi', jour: 'Lundi', heure_debut: '14:30:00', heure_fin: '18:00:00', disponible: true},
            {id: 3, periode: 'Matin', jour: 'Mardi', heure_debut: '08:30:00', heure_fin: '12:00:00', disponible: true},
            {id: 4, periode: 'Après-midi', jour: 'Mardi', heure_debut: '14:30:00', heure_fin: '18:00:00', disponible: false, raison: 'Occupé par un CM'},
            {id: 5, periode: 'Matin', jour: 'Mercredi', heure_debut: '08:30:00', heure_fin: '12:00:00', disponible: true},
            {id: 6, periode: 'Après-midi', jour: 'Mercredi', heure_debut: '14:30:00', heure_fin: '18:00:00', disponible: true},
            {id: 7, periode: 'Matin', jour: 'Jeudi', heure_debut: '08:30:00', heure_fin: '12:00:00', disponible: false, raison: 'Occupé par un TD'},
            {id: 8, periode: 'Après-midi', jour: 'Jeudi', heure_debut: '14:30:00', heure_fin: '18:00:00', disponible: true},
            {id: 9, periode: 'Matin', jour: 'Vendredi', heure_debut: '08:30:00', heure_fin: '12:00:00', disponible: true},
            {id: 10, periode: 'Après-midi', jour: 'Vendredi', heure_debut: '14:30:00', heure_fin: '18:00:00', disponible: true},
            {id: 11, periode: 'Matin', jour: 'Samedi', heure_debut: '08:30:00', heure_fin: '12:00:00', disponible: true},
            // Pas de samedi après-midi pour TP
        ];
        
        afficherDemiJournees(demiJourneesDisponibles);
    }

function afficherDemiJournees(demiJournees) {
    $('#liste-demi-journees').empty();
    
    console.log('✅ Données reçues du serveur:', demiJournees);
    
    if (!demiJournees || demiJournees.length === 0) {
        $('#liste-demi-journees').html(`
            <div class="alert alert-warning text-center">
                <h6>❌ Aucune demi-journée disponible</h6>
                <p class="mb-0">Toutes les demi-journées TP sont actuellement occupées</p>
            </div>
        `);
        return;
    }

    // VÉRIFICATION : compter combien sont des vraies demi-journées
    const vraiesDemiJournees = demiJournees.filter(dj => 
        dj.sous_periode === 'Demi-journée' && 
        (dj.heure_debut === '08:30:00' || dj.heure_debut === '14:30:00')
    );
    
    console.log('🔍 Vraies demi-journées filtrées:', vraiesDemiJournees);

    if (vraiesDemiJournees.length === 0) {
        $('#liste-demi-journees').html(`
            <div class="alert alert-danger text-center">
                <h6>🚨 Problème de données</h6>
                <p class="mb-0">Le serveur retourne des créneaux qui ne sont pas des demi-journées TP</p>
                <small>Créneaux reçus: ${demiJournees.length} | Demi-journées valides: 0</small>
            </div>
        `);
        return;
    }

    // Grouper par jour
    const jours = {};
    vraiesDemiJournees.forEach(dj => {
        if (!jours[dj.jour]) {
            jours[dj.jour] = [];
        }
        jours[dj.jour].push(dj);
    });

    // Ordre des jours
    const ordreJours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    
    // Afficher par jour dans l'ordre
    ordreJours.forEach(jour => {
        if (jours[jour]) {
            const demiJourneesDuJour = jours[jour];
            
            const jourDiv = $('<div class="mb-4"></div>');
            jourDiv.append(`<h6 class="jour-display">${jour}</h6>`);
            
            const rowDiv = $('<div class="row g-2"></div>');
            
            // Trier par période (Matin d'abord)
            demiJourneesDuJour.sort((a, b) => {
                if (a.periode === 'Matin' && b.periode === 'Après-midi') return -1;
                if (a.periode === 'Après-midi' && b.periode === 'Matin') return 1;
                return 0;
            });

            demiJourneesDuJour.forEach(dj => {
                const classePeriode = dj.periode === 'Matin' ? 'matin' : 'apres-midi';
                const icone = dj.periode === 'Matin' ? '☀️' : '🌙';
                
                const colDiv = $('<div class="col-md-6"></div>');
                const demiJourneeDiv = $(`
                    <div class="demi-journee disponible ${classePeriode}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="heure-display">${dj.heure_debut.substring(0, 5)} - ${dj.heure_fin.substring(0, 5)}</div>
                                <div class="text-muted">${icone} ${dj.periode}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success statut-badge">Disponible</span>
                            </div>
                        </div>
                    </div>
                `);
                
                demiJourneeDiv.click(function() {
                    selectionnerDemiJournee(dj.id, this, dj);
                });
                
                colDiv.append(demiJourneeDiv);
                rowDiv.append(colDiv);
            });
            
            jourDiv.append(rowDiv);
            $('#liste-demi-journees').append(jourDiv);
        }
    });
    
    console.log('🎯 Affichage terminé. Demi-journées affichées:', vraiesDemiJournees.length);
}

    function selectionnerDemiJournee(demiJourneeId, element, infoDemiJournee) {
        demiJourneeSelectionnee = demiJourneeId;
        infoDemiJourneeSelectionnee = infoDemiJournee;
        
        $('.demi-journee').removeClass('selected');
        $(element).addClass('selected');
        
        $('#info-demi-journee-selectionnee').html(`
            <strong>${infoDemiJournee.jour} ${infoDemiJournee.periode}</strong><br>
            <small>${infoDemiJournee.heure_debut.substring(0, 5)} - ${infoDemiJournee.heure_fin.substring(0, 5)}</small>
        `);
    }




    function afficherEDTComplet(groupeTpId) {
        // Simulation EDT (identique à précédemment)
        const edt = exempleEDT[groupeTpId] || [];
        
        let html = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Type</th>
                            <th>Matière</th>
                            <th>Jour</th>
                            <th>Période</th>
                            <th>Créneau</th>
                            <th>Heure</th>
                            <th>Salle</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        if (edt.length === 0) {
            html += '<tr><td colspan="7" class="text-center text-muted py-3">Aucun cours planifié</td></tr>';
        } else {
            edt.forEach(cours => {
                const badgeClasse = cours.type === 'TP' ? 'bg-success' : 'bg-primary';
                
                html += `
                    <tr>
                        <td><span class="badge ${badgeClasse}">${cours.type}</span></td>
                        <td>${cours.matiere}</td>
                        <td>${cours.jour}</td>
                        <td>${cours.periode}</td>
                        <td>${cours.sous_periode}</td>
                        <td>${cours.heure_debut.substring(0, 5)}-${cours.heure_fin.substring(0, 5)}</td>
                        <td>${cours.salle}</td>
                    </tr>
                `;
            });
        }
        
        html += '</tbody></table></div>';
        $('#affichage-edt-complet').html(html);
    }

    function initialiserFormulaire() {
        $('#seance_numero').html('<option value="">Sélectionnez la séance</option>');
        for (let i = 1; i <= infoMatiere.seances; i++) {
            $('#seance_numero').append(`<option value="${i}">Séance ${i}</option>`);
        }
        
        $('#duree-seance').text(`Durée: ${infoMatiere.duree} semaines`);
        $('#form-ajout-tp').show();
    }

    function chargerSalles() {
        const seanceNumero = $('#seance_numero').val();
        if (seanceNumero && infoMatiere) {
            $('#salle').html('<option value="">Sélectionnez une salle</option>');
            infoMatiere.salles.forEach((salle, index) => {
                $('#salle').append(`<option value="${index + 1}">Salle ${salle}</option>`);
            });
        }
    }

    function calculerSemaineFin() {
        const semaineDebut = parseInt($('#semaine_debut').val());
        if (semaineDebut && infoMatiere) {
            $('#semaine_fin').val(semaineDebut + infoMatiere.duree - 1);
        }
    }

    function ajouterTP() {
        if (!validerFormulaire()) return;

        const tpData = {
            groupe_tp_id: groupeTpSelectionne,
            demi_journee_id: demiJourneeSelectionnee,
            matiere_id: matiereSelectionnee,
            salle_id: $('#salle').val(),
            seance_numero: $('#seance_numero').val(),
            semaine_debut: $('#semaine_debut').val(),
            semaine_fin: $('#semaine_fin').val()
        };

        alert(`✅ TP planifié avec succès !

📚 Matière: ${infoMatiere.nom}
👥 Groupe: ${$('#groupe_tp option:selected').text()}
🗓️ Période: ${infoDemiJourneeSelectionnee.jour} ${infoDemiJourneeSelectionnee.periode}
⏰ Horaire: ${infoDemiJourneeSelectionnee.heure_debut.substring(0, 5)} - ${infoDemiJourneeSelectionnee.heure_fin.substring(0, 5)}
🔢 Séance: ${$('#seance_numero option:selected').text()}
🏫 Salle: ${$('#salle option:selected').text()}
📅 Semaines: ${$('#semaine_debut').val()} à ${$('#semaine_fin').val()}`);

        reinitialiserFormulaire();
        afficherEDTComplet(groupeTpSelectionne);
    }

    function validerFormulaire() {
        if (!demiJourneeSelectionnee) {
            alert('Veuillez sélectionner une demi-journée');
            return false;
        }
        if (!$('#seance_numero').val()) {
            alert('Veuillez sélectionner le numéro de séance');
            return false;
        }
        if (!$('#salle').val()) {
            alert('Veuillez sélectionner une salle');
            return false;
        }
        if (!$('#semaine_debut').val()) {
            alert('Veuillez saisir la semaine de début');
            return false;
        }
        return true;
    }

    function reinitialiserFormulaire() {
        $('#seance_numero').val('');
        $('#salle').html('<option value="">Sélectionnez une séance</option>');
        $('#semaine_debut').val('');
        $('#semaine_fin').val('');
        $('.demi-journee').removeClass('selected');
        demiJourneeSelectionnee = null;
        infoDemiJourneeSelectionnee = null;
        $('#info-demi-journee-selectionnee').html('');
    }

    function reinitialiserSelectsSuivants() {
        $('#groupe_tp').prop('disabled', true).html('<option value="">Sélectionnez d\'abord un groupe TD</option>');
        $('#matiere').prop('disabled', true).html('<option value="">Sélectionnez d\'abord un groupe TP</option>');
        reinitialiserAffichages();
    }

    function reinitialiserAffichages() {
        $('#liste-demi-journees').html('<p class="text-muted text-center py-4">Sélectionnez un groupe TP et une matière</p>');
        $('#affichage-edt-complet').html('<p class="text-muted text-center py-4">Sélectionnez un groupe TP pour voir son emploi du temps</p>');
        $('#form-ajout-tp').hide();
        demiJourneeSelectionnee = null;
        matiereSelectionnee = null;
        infoMatiere = null;
        infoDemiJourneeSelectionnee = null;
    }

    // Données d'exemple pour l'EDT
    const exempleEDT = {
        '1': [
            { type: 'TP', matiere: 'TP Informatique', salle: 'Salle 101', jour: 'Mercredi', periode: 'Matin', sous_periode: 'Demi-journée', heure_debut: '08:30:00', heure_fin: '12:00:00' },
            { type: 'CM', matiere: 'Mathématiques', salle: '', jour: 'Lundi', periode: 'Matin', sous_periode: 'C1', heure_debut: '08:30:00', heure_fin: '10:00:00' },
            { type: 'TD', matiere: 'TD Maths A1', salle: '', jour: 'Lundi', periode: 'Après-midi', sous_periode: 'C3', heure_debut: '14:30:00', heure_fin: '16:00:00' }
        ]
    };
    </script>
</body>
</html>