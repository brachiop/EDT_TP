<?php
class Tableau_de_bord extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('EDT_model');
        $this->load->helper('url');
        $this->load->database();
    }
    
    public function index() {
        $data['title'] = 'Tableau de Bord - Gestion EDT';
        $this->load->view('tableau_de_bord', $data);
    }
    
    // État des tables
    public function etat_tables() {
        $data['title'] = 'État des Tables - Gestion EDT';
        $this->load->view('etat_tables', $data);
    }
    
    // Gestion des données
    public function gestion_donnees() {
        $data['title'] = 'Gestion des Données - Gestion EDT';
        $this->load->view('gestion_donnees', $data);
    }
    
    // Visualisation EDT (redirection vers l'existant)
    public function visualiser_edt() {
        redirect('edt/voir_edt');
    }    
    
}
?>