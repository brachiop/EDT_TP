<?php
class EDT extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('EDT_model');
        $this->load->model('Section_model');
        $this->load->helper('url');
        $this->load->database();
    }

    public function index() {
        $data['sections'] = $this->Section_model->get_all_sections();
        $this->load->view('edt/planification', $data);
    }

    // AJAX: Groupes TD par section
    public function get_groupes_td() {
        $section_id = $this->input->get('section_id');
        $groupes_td = $this->EDT_model->get_groupes_td_by_section($section_id);
        echo json_encode($groupes_td);
    }

    // AJAX: Groupes TP par groupe TD
    public function get_groupes_tp() {
        $groupe_td_id = $this->input->get('groupe_td_id');
        $groupes_tp = $this->EDT_model->get_groupes_tp_by_td($groupe_td_id);
        echo json_encode($groupes_tp);
    }

    // AJAX: EDT complet du groupe TP
    public function get_edt_groupe() {
        $groupe_tp_id = $this->input->get('groupe_tp_id');
        $edt = $this->EDT_model->get_edt_complet_groupe_tp($groupe_tp_id);
        echo json_encode($edt);
    }

    // AJAX: Créneaux disponibles
    public function get_creneaux_disponibles() {
        $groupe_tp_id = $this->input->get('groupe_tp_id');
        $creneaux = $this->EDT_model->get_creneaux_disponibles($groupe_tp_id);
        echo json_encode($creneaux);
    }

    // AJAX: Ajouter un TP
    public function planifier_tp() {
        $groupe_tp_id = $this->input->post('groupe_tp_id');
        $creneau_id = $this->input->post('creneau_id');
        $matiere = $this->input->post('matiere');
        $salle = $this->input->post('salle');

        if($this->EDT_model->ajouter_tp($groupe_tp_id, $creneau_id, $matiere, $salle)) {
            $response = array(
                'success' => true,
                'message' => 'TP ajouté avec succès!'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du TP'
            );
        }
        
        echo json_encode($response);
    }
    
    

public function planification_avancee() {
    $data['sections'] = $this->Section_model->get_all_sections();
    $this->load->view('edt/planification_avancee', $data);
}

public function get_matieres() {
    $matieres = $this->EDT_model->get_matieres();
    echo json_encode($matieres);
}

public function get_salles_by_matiere($matiere_id) {
    $salles = $this->EDT_model->get_salles_by_matiere($matiere_id);
    echo json_encode($salles);
}

public function planifier_tp_avance() {
    $data = $this->input->post();
    
    if($this->EDT_model->planifier_tp_avance($data)) {
        $response = array(
            'success' => true,
            'message' => 'TP planifié avec succès!'
        );
    } else {
        $response = array(
            'success' => false,
            'message' => 'Erreur: Contraintes non respectées'
        );
    }
    
    echo json_encode($response);
}   

    // AJAX: Récupérer les demi-journées TP disponibles
    public function get_demi_journees_tp_disponibles() {
        $groupe_tp_id = $this->input->get('groupe_tp_id');
        $demi_journees = $this->EDT_model->get_demi_journees_tp_disponibles($groupe_tp_id);
        echo json_encode($demi_journees);
    }    


public function debug_demi_journees_tp() {
    $this->load->model('EDT_model');
    
    // Test avec un groupe TP spécifique
    $groupe_tp_id = 1;
    
    echo "<h1>DEBUG Demi-journées TP</h1>";
    
    // Test 1: Créneaux TP de base
    echo "<h2>1. Toutes les demi-journées TP possibles:</h2>";
    $this->db->where('type_cours_id', 3);
    $this->db->where('sous_periode', 'Demi-journée');
    $this->db->where("(jour != 'Samedi' OR periode != 'Après-midi')");
    $creneaux_base = $this->db->get('creneaux')->result_array();
    
    foreach ($creneaux_base as $creneau) {
        echo "{$creneau['jour']} | {$creneau['periode']} | {$creneau['heure_debut']} - {$creneau['heure_fin']}<br>";
    }
    
    // Test 2: Demi-journées occupées
    echo "<h2>2. Demi-journées occupées pour le groupe TP {$groupe_tp_id}:</h2>";
    $occupees = $this->EDT_model->get_demi_journees_occupees_groupe_tp($groupe_tp_id);
    foreach ($occupees as $occupee) {
        echo "{$occupee['jour']} | {$occupee['periode']}<br>";
    }
    
    // Test 3: Demi-journées disponibles
    echo "<h2>3. Demi-journées disponibles:</h2>";
    $disponibles = $this->EDT_model->get_demi_journees_tp_disponibles($groupe_tp_id);
    foreach ($disponibles as $dispo) {
        echo "{$dispo['jour']} | {$dispo['periode']} | {$dispo['heure_debut']} - {$dispo['heure_fin']}<br>";
    }
}  

  
}
?>