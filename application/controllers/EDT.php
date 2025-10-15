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
}
?>