<!DOCTYPE html>
<html>
<head>
    <title>Planification des TP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .creneau-disponible { padding: 10px; margin: 5px; border: 2px solid #28a745; border-radius: 5px; cursor: pointer; }
        .creneau-disponible:hover { background-color: #f8f9fa; }
        .creneau-selectionne { background-color: #d4edda; border-color: #155724; }
    </style>

</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">📅 Planification des Travaux Pratiques</h1>
        
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Sélection hiérarchique</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo site_url('edt/planifier_tp'); ?>">
                    
<div class="row mb-3">
    <!-- Section -->
    <div class="col-md-3">
        <label class="form-label">Section:</label>
        <select name="section_id" class="form-select" onchange="chargerEnfants(this.value, 'groupe_td_id', 'select-groupe-td')">
            <option value="">Choisir une section</option>
            <?php foreach ($sections as $section): ?>
                <option value="<?php echo $section->codeGroupe; ?>"><?php echo $section->nom; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Groupe TD -->
    <div class="col-md-3">
        <label class="form-label">Groupe TD:</label>
        <select name="groupe_td_id" class="form-select" id="select-groupe-td" onchange="chargerEnfants(this.value, 'groupe_tp_id', 'select-groupe-tp')" disabled>
            <option value="">Choisir d'abord la section</option>
        </select>
    </div>
    
    <!-- Groupe TP -->
    <div class="col-md-3">
        <label class="form-label">Groupe TP:</label>
        <select name="groupe_tp_id" class="form-select" id="select-groupe-tp" onchange="chargerContenu()" disabled>
            <option value="">Choisir d'abord le groupe TD</option>
        </select>
    </div>
    
    <!-- Matière -->
<div class="col-md-3">
    <label class="form-label">Matière TP:</label>
    <select name="code_enseignement" class="form-select" id="select-matieres" onchange="chargerSallesParMatiere()" disabled>
        <option value="">Choisir d'abord le groupe TP</option>
    </select>
</div>

<!--
    <div class="col-md-3">
        <label class="form-label">Matière TP:</label>
        <select name="code_enseignement" class="form-select" id="select-matieres" disabled>
            <option value="">Choisir d'abord le groupe TP</option>
        </select>
    </div>
</div>
-->

<!-- Salle -->
<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Salle:</label>
        <select name="salle_id" class="form-select" id="select-salles" disabled>
            <option value="">Choisir d'abord le groupe TP</option>
        </select>
    </div>
</div>
                    
                    <!-- Créneaux disponibles -->
                    <div id="creneaux-disponibles" class="mb-3">
                        <div class="alert alert-info">
                            Sélectionnez un groupe TP pour voir les créneaux disponibles
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg" id="btn-submit" disabled>
                        ✅ Planifier le TP
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Fonction générique pour charger les enfants d'une ressource
/*
function chargerEnfants(ressourceId, selectName, selectId) {
    if (!ressourceId) {
        document.getElementById(selectId).innerHTML = '<option value="">Choisir d\'abord le niveau précédent</option>';
        document.getElementById(selectId).disabled = true;
        reinitialiserSelectsSuivants(selectId);
        return;
    }
    
    fetch(`../edt/get_enfants/${ressourceId}?select_name=${selectName}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById(selectId).innerHTML = html;
            document.getElementById(selectId).disabled = false;
            reinitialiserSelectsSuivants(selectId);
        })
        .catch(error => {
            console.error('Erreur fetch:', error);
            document.getElementById(selectId).innerHTML = '<option value="">Erreur de chargement</option>';
        });
}
*/ 

function chargerEnfants(ressourceId, selectName, selectId) {
    console.log('🔍 chargerEnfants appelé:', {ressourceId, selectName, selectId});
    
    if (!ressourceId) {
        console.log('❌ Ressource ID manquant');
        document.getElementById(selectId).innerHTML = '<option value="">Choisir d\'abord le niveau précédent</option>';
        document.getElementById(selectId).disabled = true;
        reinitialiserSelectsSuivants(selectId);
        return;
    }
    
    const url = `<?php echo site_url('edt/get_enfants/'); ?>${ressourceId}?select_name=${selectName}`;
    console.log('🌐 Fetch URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('📥 Response status:', response.status, response.statusText);
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return response.text();
        })
        .then(html => {
            console.log('✅ HTML reçu:', html.substring(0, 100) + '...');
            document.getElementById(selectId).innerHTML = html;
            document.getElementById(selectId).disabled = false;
            reinitialiserSelectsSuivants(selectId);
        })
        .catch(error => {
            console.error('❌ Erreur fetch:', error);
            document.getElementById(selectId).innerHTML = '<option value="">Erreur de chargement</option>';
        });
}
   
    // Réinitialiser les selects suivants dans la hiérarchie
// Réinitialiser les selects suivants - VERSION CORRIGÉE
function reinitialiserSelectsSuivants(apresSelectId) {
    const selects = ['select-groupe-td', 'select-groupe-tp', 'select-matieres', 'select-salles'];
    let found = false;
    
    selects.forEach(selectId => {
        const selectElement = document.getElementById(selectId);
        if (selectElement) {
            if (found) {
                // Réinitialiser le select, pas le remplacer par un nouveau
                selectElement.innerHTML = '<option value="">Choisir d\'abord le niveau précédent</option>';
                selectElement.disabled = true;
                
                // Pour la zone des créneaux (qui n'est pas un select)
                if (selectId === 'creneaux-disponibles') {
                    selectElement.innerHTML = '<div class="alert alert-info">Sélectionnez un groupe TP</div>';
                }
            }
            if (selectId === apresSelectId) found = true;
        }
    });
    
    document.getElementById('btn-submit').disabled = true;
}


    
    // Charger le contenu après sélection du groupe TP
function chargerContenu() {
    const groupeTpId = document.getElementById('select-groupe-tp').value;
    if (!groupeTpId) return;
    
    // Activer seulement le select matières
    document.getElementById('select-matieres').disabled = false;
    
    // Désactiver et vider le select salles en attendant la matière
    document.getElementById('select-salles').innerHTML = '<option value="">Choisir d\'abord la matière</option>';
    document.getElementById('select-salles').disabled = true;
    
    // Charger créneaux disponibles (URL relative)
       //fetch(`../edt/get_creneaux_disponibles/${groupeTpId}`)
       fetch(`<?php echo site_url('edt/get_creneaux_disponibles/'); ?>${groupeTpId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('creneaux-disponibles').innerHTML = html; 
        });
    
    // Charger matières (URL relative)
    fetch('<?php echo site_url('edt/get_matieres'); ?>')
    //fetch('../edt/get_matieres')
        .then(response => response.text())
        .then(html => {
            document.getElementById('select-matieres').innerHTML = html;
        });
}


// Charger les salles par matière (URL relative)
function chargerSallesParMatiere() {
    const codeEnseignement = document.getElementById('select-matieres').value;
    if (!codeEnseignement) {
        document.getElementById('select-salles').innerHTML = '<option value="">Choisir d\'abord la matière</option>';
        document.getElementById('select-salles').disabled = true;
        return;
    }
    //fetch('<?php echo site_url('edt/get_salles_par_matiere/'); ?>')
    fetch(`<?php echo site_url('edt/get_salles_par_matiere/'); ?>${codeEnseignement}`)
    //fetch(`../edt/get_salles_par_matiere/${codeEnseignement}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('select-salles').innerHTML = html;
            document.getElementById('select-salles').disabled = false;
        });
}

    
function selectionnerCreneau(creneauElement, creneauId, heureDebut) {
    console.log('📅 Sélection créneau:', creneauId, heureDebut);
    
    // Récupérer le jour et la période depuis l'élément HTML
    const jour = creneauElement.querySelector('.jour-text').textContent;
    const periode = creneauElement.querySelector('.periode-text').textContent;
    
    console.log('🔍 Jour/Période:', jour, periode);
    
    // Calculer la date réelle (semaine prochaine)
    const dateReelle = calculerDateParJour(jour);
    console.log('📅 Date calculée:', dateReelle);
    
    document.getElementById('btn-submit').disabled = false;
    
    let form = document.querySelector('form');
    
    // Supprimer les anciens champs
    ['creneau_id', 'date_seance', 'heure_debut', 'jour_creneau'].forEach(name => {
        let input = document.querySelector(`input[name="${name}"]`);
        if (input) input.remove();
    });
    
    // Ajouter nouveaux champs
    let inputs = [
        {name: 'creneau_id', value: creneauId},
        {name: 'date_seance', value: dateReelle},
        {name: 'heure_debut', value: heureDebut},
        {name: 'jour_creneau', value: jour} // Pour debug
    ];
    
    inputs.forEach(input => {
        let field = document.createElement('input');
        field.type = 'hidden';
        field.name = input.name;
        field.value = input.value;
        form.appendChild(field);
    });
    
    // Mise en surbrillance
    document.querySelectorAll('.creneau-disponible').forEach(el => {
        el.classList.remove('creneau-selectionne');
    });
    creneauElement.classList.add('creneau-selectionne');
}

// Calculer la date réelle basée sur le jour de la semaine
function calculerDateParJour(jourFrancais) {
    const jours = {
        'Lundi': 1, 'Mardi': 2, 'Mercredi': 3, 
        'Jeudi': 4, 'Vendredi': 5, 'Samedi': 6
    };
    
    const jourCible = jours[jourFrancais];
    const aujourdhui = new Date();
    const jourActuel = aujourdhui.getDay(); // 0=dimanche, 1=lundi...
    
    // Calculer la différence de jours
    let difference = jourCible - (jourActuel === 0 ? 7 : jourActuel);
    if (difference <= 0) {
        difference += 7; // Semaine prochaine
    }
    
    const dateCible = new Date(aujourdhui);
    dateCible.setDate(aujourdhui.getDate() + difference);
    
    return dateCible.toISOString().split('T')[0]; // Format YYYY-MM-DD
}
    </script>
</body>
</html>