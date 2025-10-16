<!DOCTYPE html>
<html>
<head>
    <title>Planification TP - Contraintes Mises à Jour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .container { max-width: 1400px; }
        .creneau { padding: 12px; margin: 6px; border-radius: 6px; cursor: pointer; }
        .creneau.tp { background: #e8f5e8; border-left: 4px solid #4caf50; }
        .creneau.cm-td { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .creneau.disponible:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .creneau.selected { background: #fff3cd; border-left: 4px solid #ffc107; }
        .creneau.occupe { background: #f8d7da; opacity: 0.5; cursor: not-allowed; }
        .creneau.interdit { background: #6c757d; opacity: 0.3; cursor: not-allowed; }
        .badge-tp { background: #4caf50; }
        .badge-cm-td { background: #2196f3; }
        .sous-periode { font-size: 0.85em; color: #666; }
        .info-contraintes { font-size: 0.9em; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo base_url(); ?>demo/tableau_de_bord">EDT - Contraintes Mises à Jour</a>
            <div class="navbar-nav">
                <a class="nav-link" href="<?php echo base_url(); ?>demo/tableau_de_bord">← Tableau de Bord</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Planification des TP - Contraintes Mises à Jour</h1>
        <div class="alert alert-info">
            <strong>📚 Règles mises à jour :</strong><br>
            • <strong>TP</strong> : Demi-journées complètes (Sauf samedi après-midi)<br>
            • <strong>CM/TD</strong> : Créneaux de 1h30 (Toutes périodes autorisées, y compris samedi après-midi)<br>
            • <strong>Exclusion</strong> : Aucun TP le samedi après-midi
        </div>
        
        <!-- Le reste du code HTML reste identique jusqu'à la fonction JavaScript -->
        
    </div>

    <script>
    // ... code JavaScript précédent ...

    function chargerCreneauxTPDisponibles() {
        if (!groupeTpSelectionne || !matiereSelectionnee) return;
        
        // Simulation données créneaux TP disponibles (sans samedi après-midi)
        const demiJourneesDisponibles = [
            {id: 1, type: 'TP', periode: 'Matin', sous_periode: 'Demi-journée', jour: 'Jeudi', heure_debut: '08:30:00', heure_fin: '12:00:00'},
            {id: 2, type: 'TP', periode: 'Après-midi', sous_periode: 'Demi-journée', jour: 'Jeudi', heure_debut: '14:30:00', heure_fin: '18:00:00'},
            {id: 3, type: 'TP', periode: 'Matin', sous_periode: 'Demi-journée', jour: 'Vendredi', heure_debut: '08:30:00', heure_fin: '12:00:00'},
            {id: 4, type: 'TP', periode: 'Après-midi', sous_periode: 'Demi-journée', jour: 'Vendredi', heure_debut: '14:30:00', heure_fin: '18:00:00'},
            {id: 5, type: 'TP', periode: 'Matin', sous_periode: 'Demi-journée', jour: 'Samedi', heure_debut: '08:30:00', heure_fin: '12:00:00'}
            // Pas de samedi après-midi pour TP
        ];
        
        // Ajouter les créneaux interdits pour illustration
        const creneauxInterdits = [
            {id: 0, type: 'TP', periode: 'Après-midi', sous_periode: 'Demi-journée', jour: 'Samedi', heure_debut: '14:30:00', heure_fin: '18:00:00', interdit: true}
        ];
        
        afficherCreneauxTP(demiJourneesDisponibles, creneauxInterdits);
    }

    function afficherCreneauxTP(creneauxTP, creneauxInterdits = []) {
        $('#liste-creneaux').empty();
        
        if (creneauxTP.length === 0) {
            $('#liste-creneaux').html('<div class="alert alert-warning">Aucune demi-journée disponible pour les TP</div>');
            return;
        }
        
        // Grouper par jour
        const jours = {};
        
        // Ajouter les créneaux disponibles
        creneauxTP.forEach(creneau => {
            if (!jours[creneau.jour]) {
                jours[creneau.jour] = [];
            }
            jours[creneau.jour].push({...creneau, disponible: true});
        });
        
        // Ajouter les créneaux interdits
        creneauxInterdits.forEach(creneau => {
            if (!jours[creneau.jour]) {
                jours[creneau.jour] = [];
            }
            jours[creneau.jour].push({...creneau, disponible: false});
        });
        
        // Afficher par jour
        for (const [jour, creneaux] of Object.entries(jours)) {
            $('#liste-creneaux').append(`<h6 class="mt-3 mb-2">${jour}</h6>`);
            
            creneaux.forEach(creneau => {
                const icone = creneau.periode === 'Matin' ? '☀️' : '🌙';
                const estDisponible = creneau.disponible !== false;
                const classeCreneau = estDisponible ? 'tp disponible' : 'tp interdit';
                
                $('#liste-creneaux').append(`
                    <div class="creneau ${classeCreneau}" 
                         ${estDisponible ? `onclick="selectionnerCreneauTP(${creneau.id}, this)" data-creneau='${JSON.stringify(creneau)}'` : ''}>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge badge-tp">${icone} TP ${creneau.periode}</span>
                                <strong>${creneau.jour}</strong>
                                ${!estDisponible ? '<span class="badge bg-secondary ms-2">Interdit</span>' : ''}
                            </div>
                            <div class="text-end">
                                <small>${creneau.heure_debut.substring(0, 5)} - ${creneau.heure_fin.substring(0, 5)}</small>
                                <div class="sous-periode">Demi-journée complète</div>
                                ${!estDisponible ? '<div class="info-contraintes text-danger">TP non autorisé</div>' : ''}
                            </div>
                        </div>
                    </div>
                `);
            });
        }
    }

    function afficherEDTComplet(groupeTpId) {
        const edt = exempleEDT[groupeTpId] || [];
        
        let html = `
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
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
            html += '<tr><td colspan="7" class="text-center text-muted">Aucun cours planifié</td></tr>';
        } else {
            // Trier par jour et heure
            edt.sort((a, b) => {
                const jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                const jourA = jours.indexOf(a.jour);
                const jourB = jours.indexOf(b.jour);
                if (jourA !== jourB) return jourA - jourB;
                return a.heure_debut.localeCompare(b.heure_debut);
            });
            
            edt.forEach(cours => {
                const badgeClasse = cours.type === 'TP' ? 'badge-tp' : 'badge-cm-td';
                
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

    // ... reste du code JavaScript ...
    </script>
</body>
</html>