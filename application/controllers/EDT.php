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

    // Nouvelle méthode pour les groupes TD par section
    public function get_groupes_td() {
        $section_id = $this->input->get('section_id');
        $groupes_td = $this->EDT_model->get_groupes_td_by_section($section_id);
        echo json_encode($groupes_td);
    }

    // Nouvelle méthode pour les groupes TP par groupe TD
    public function get_groupes_tp() {
        $groupe_td_id = $this->input->get('groupe_td_id');
        $groupes_tp = $this->EDT_model->get_groupes_tp_by_td($groupe_td_id);
        echo json_encode($groupes_tp);
    }

    // Méthode pour les créneaux disponibles
public function get_creneaux_disponibles() {
    $groupe_tp_id = $this->input->get('groupe_tp_id');
    
    if ($groupe_tp_id) {
        // Utiliser la nouvelle méthode qui vérifie les chevauchements
        $creneaux_disponibles = $this->EDT_model->get_creneaux_reellement_disponibles($groupe_tp_id);
        echo json_encode($creneaux_disponibles);
    } else {
        echo json_encode(array());
    }
}

    // Méthode pour l'EDT du groupe
    public function get_edt_groupe() {
        $groupe_tp_id = $this->input->get('groupe_tp_id');
        
        if ($groupe_tp_id) {
            $edt = $this->EDT_model->get_edt_simule_groupe_tp($groupe_tp_id);
            echo json_encode($edt);
        } else {
            echo json_encode(array());
        }
    }

    public function planifier_tp() {
        // Simulation d'ajout
        $data = array(
            'success' => true,
            'message' => 'TP ajouté avec succès!'
        );
        echo json_encode($data);
    }
    
// Dans EDT.php - Ajoutez cette méthode de test
public function test_ajax() {
    $groupe_tp_id = $this->input->get('groupe_tp_id');
    
    // Données de test simples
    $test_data = array(
        array('jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'TEST', 'salle' => 'TEST')
    );
    
    echo json_encode($test_data);
}    
    
}
?>