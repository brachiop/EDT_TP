<?php
class EDT extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('EDT_model');
        $this->load->helper('url');
        $this->load->library('session');
    }
    
    // Page principale
    public function index() {
        $data['sections'] = $this->EDT_model->get_sections();
        $this->load->view('edt/planification', $data);
    }
    
    // AJAX: Enfants d'une ressource (générique)
    public function get_enfants($ressource_id) {
        $enfants = $this->EDT_model->get_enfants_ressource($ressource_id);
        $data['enfants'] = $enfants;
        $data['select_name'] = $this->input->get('select_name');
        $this->load->view('edt/partials/select_enfants', $data);
    }
    
    // AJAX: Créneaux disponibles

    public function get_creneaux_disponibles($groupe_tp_id) {
        $creneaux = $this->EDT_model->get_creneaux_tp_disponibles($groupe_tp_id);
        $data['creneaux'] = $creneaux;
        $this->load->view('edt/partials/creneaux_disponibles', $data);
    }

    
    // AJAX: Matières TP
    public function get_matieres() {
        $matieres = $this->EDT_model->get_matieres_tp();
        $data['matieres'] = $matieres;
        $this->load->view('edt/partials/select_matieres', $data);
    }
    
    // AJAX: Salles TP (toutes)
    public function get_salles() {
        $salles = $this->EDT_model->get_salles_tp();
        $data['salles'] = $salles;
        $this->load->view('edt/partials/select_salles', $data);
    }

    // AJAX: Salles par matière (NOUVELLE MÉTHODE)
    public function get_salles_par_matiere($code_enseignement = null) {
        // Récupérer le code_enseignement depuis l'URL si pas en paramètre
        if (!$code_enseignement) {
            $code_enseignement = $this->uri->segment(3);
        }
        
        if (!$code_enseignement) {
            echo "<option value=''>Erreur: code enseignement manquant</option>";
            return;
        }
        
        $salles = $this->EDT_model->get_salles_par_matiere($code_enseignement);
        $data['salles'] = $salles;
        $this->load->view('edt/partials/select_salles', $data);
    }

    
    // Planifier un TP
    public function planifier_tp() {
        if ($this->input->post()) {
            $success = $this->EDT_model->creer_seance_tp(array(
                'groupe_tp_id' => $this->input->post('groupe_tp_id'),
                'code_enseignement' => $this->input->post('code_enseignement'),
                'salle_id' => $this->input->post('salle_id'),
                'date_seance' => $this->input->post('date_seance'),
                'heure_debut' => $this->input->post('heure_debut')
            ));
            
            if ($success) {
                $this->session->set_flashdata('success', 'TP planifié avec succès!');
            } else {
                $this->session->set_flashdata('error', 'Erreur lors de la planification');
            }
            
            redirect('edt');
        }
    }
    

public function debug_structure_tables() {
    echo "<h1>Debug Structure des Tables</h1>";
    
    // Vérifier la table matieres
    echo "<h2>Structure de la table 'matieres':</h2>";
    $champs_matieres = $this->db->list_fields('matieres');
    foreach ($champs_matieres as $champ) {
        echo "Champ: $champ<br>";
    }
    
    // Vérifier la table ressources_salles
    echo "<h2>Structure de la table 'ressources_salles':</h2>";
    $champs_salles = $this->db->list_fields('ressources_salles');
    foreach ($champs_salles as $champ) {
        echo "Champ: $champ<br>";
    }
    
    // Vérifier les données existantes
    echo "<h2>Matières existantes:</h2>";
    $matieres = $this->db->get('matieres')->result();
    foreach ($matieres as $matiere) {
        $departement = isset($matiere->departement) ? $matiere->departement : 'NON DÉFINI';
        echo "Matière: $matiere->nom - Département: $departement<br>";
    }
    
    echo "<h2>Salles existantes:</h2>";
    $salles = $this->db->get('ressources_salles')->result();
    foreach ($salles as $salle) {
        echo "Salle: $salle->nom - Département: $salle->departement - Type: $salle->typeSalle<br>";
    }
}



public function test_filtrage_salles() {
    echo "<h1>Test Filtrage Salles par Département</h1>";
    
    // Test avec différentes matières
    $matieres_test = $this->db->select('e.codeEnseignement, m.nom, m.departement')
                              ->from('enseignements e')
                              ->join('matieres m', 'm.codeMatiere = e.codeMatiere')
                              ->limit(5)
                              ->get()->result();
    
    foreach ($matieres_test as $matiere) {
        echo "<h2>Matière: {$matiere->nom} (Département: {$matiere->departement})</h2>";
        
        $salles = $this->get_salles_par_matiere($matiere->codeEnseignement);
        
        if (empty($salles)) {
            echo "❌ Aucune salle TP trouvée pour le département {$matiere->departement}<br>";
        } else {
            foreach ($salles as $salle) {
                echo "✅ Salle: {$salle->nom} (Département: {$salle->departement})<br>";
            }
        }
        echo "<hr>";
    }
}

public function debug_matieres_tp() {
    echo "<h1>Debug Matières TP</h1>";
    
    $this->load->model('EDT_model');
    $matieres = $this->EDT_model->get_matieres_tp();
    
    echo "<h2>Méthode get_matieres_tp() - Résultats (" . count($matieres) . "):</h2>";
    
    if (empty($matieres)) {
        echo "❌ Aucune matière trouvée<br>";
        
        // Debug de la requête
        echo "<h3>Debug requête SQL:</h3>";
        $this->db->select('m.codeMatiere, m.nom, e.codeEnseignement, e.codeTypeActivite');
        $this->db->from('matieres m');
        $this->db->join('enseignements e', 'e.codeMatiere = m.codeMatiere');
        $this->db->where('e.codeTypeActivite', 3);
        $this->db->group_by('m.codeMatiere');
        
        echo "SQL: " . $this->db->last_query() . "<br>";
        
        // Voir les données brutes
        echo "<h3>Données brutes tables:</h3>";
        
        echo "<h4>Table matieres:</h4>";
        $matieres_table = $this->db->get('matieres')->result();
        foreach ($matieres_table as $m) {
            echo "Matière: $m->codeMatiere - $m->nom<br>";
        }
        
        echo "<h4>Table enseignements:</h4>";
        $enseignements_table = $this->db->get('enseignements')->result();
        foreach ($enseignements_table as $e) {
            echo "Enseignement: $e->codeEnseignement - Matière: $e->codeMatiere - Type: $e->codeTypeActivite<br>";
        }
        
        echo "<h4>Table types_activites:</h4>";
        $types_table = $this->db->get('types_activites')->result();
        foreach ($types_table as $t) {
            echo "Type: $t->codeTypeActivite - $t->nom<br>";
        }
        
    } else {
        foreach ($matieres as $matiere) {
            echo "✅ $matiere->codeMatiere - $matiere->nom (Enseignement: $matiere->codeEnseignement)<br>";
        }
    }
}

public function debug_creneaux_disponibles($groupe_tp_id = 20) {
    echo "<h1>Debug Créneaux Disponibles - VERSION CORRIGÉE</h1>";
    echo "<h2>Groupe TP ID: $groupe_tp_id</h2>";
    
    $this->load->model('EDT_model');
    
    // 1. Hiérarchie
    echo "<h3>1. Hiérarchie du groupe:</h3>";
    $groupe_td = $this->EDT_model->get_groupe_td_parent($groupe_tp_id);
    $section = $this->EDT_model->get_section_parente($groupe_td);
    echo "Groupe TP: $groupe_tp_id<br>";
    echo "Groupe TD parent: " . ($groupe_td ? $groupe_td : 'Non trouvé') . "<br>";
    echo "Section parente: " . ($section ? $section : 'Non trouvée') . "<br>";
    
    // 2. Séances existantes par niveau
    echo "<h3>2. Séances existantes:</h3>";
    
    // Séances du groupe TP
    echo "<h4>Séances du groupe TP ($groupe_tp_id):</h4>";
    $seances_tp = $this->get_seances_par_ressource($groupe_tp_id);
    $this->afficher_seances($seances_tp);
    
    // Séances du groupe TD
    if ($groupe_td) {
        echo "<h4>Séances du groupe TD ($groupe_td):</h4>";
        $seances_td = $this->get_seances_par_ressource($groupe_td);
        $this->afficher_seances($seances_td);
    }
    
    // Séances de la section
    if ($section) {
        echo "<h4>Séances de la section ($section):</h4>";
        $seances_section = $this->get_seances_par_ressource($section);
        $this->afficher_seances($seances_section);
    }
    
    // 3. Créneaux occupés
    echo "<h3>3. Créneaux occupés (convertis):</h3>";
    $occupes = $this->EDT_model->get_creneaux_occupes_groupe_tp($groupe_tp_id);
    
    if (empty($occupes)) {
        echo "✅ Aucun créneau occupé<br>";
    } else {
        foreach ($occupes as $occupe) {
            echo "❌ Occupé: {$occupe['jour']} - {$occupe['periode']} (Date: {$occupe['dateSeance']})<br>";
        }
    }
    
    // 4. Créneaux disponibles
    echo "<h3>4. Créneaux disponibles après filtrage:</h3>";
    $disponibles = $this->EDT_model->get_creneaux_tp_disponibles($groupe_tp_id);
    
    if (empty($disponibles)) {
        echo "❌ Aucun créneau disponible<br>";
    } else {
        foreach ($disponibles as $dispo) {
            echo "✅ Disponible: {$dispo['jour']} - {$dispo['periode']} ({$dispo['heure_debut']} - {$dispo['heure_fin']})<br>";
        }
    }
}

// Méthodes helper pour le debug
private function get_seances_par_ressource($ressource_id) {
    $this->db->select('s.*');
    $this->db->from('seances s');
    $this->db->join('seances_groupes sg', 's.codeSeance = sg.codeSeance');
    $this->db->where('sg.codeRessource', $ressource_id);
    return $this->db->get()->result_array();
}

private function afficher_seances($seances) {
    if (empty($seances)) {
        echo "Aucune séance<br>";
    } else {
        foreach ($seances as $seance) {
            $heure = sprintf('%04d', $seance['heureSeance']);
            $heure_formatee = substr($heure, 0, 2) . ':' . substr($heure, 2, 2);
            echo "- {$seance['dateSeance']} {$heure_formatee} (Durée: {$seance['dureeSeance']}min)<br>";
        }
    }
}
    
}
?>