<?php
class Demo extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('EDT_model');
        $this->load->model('Section_model');
        $this->load->database();
        $this->load->helper('url');
    }

    public function index() {
        redirect('demo/tableau_de_bord');
    }

    public function tableau_de_bord() {
        $data['title'] = 'Tableau de Bord - Démonstration EDT TP';
        
        // Récupérer les données pour l'aperçu
        $data['sections'] = $this->Section_model->get_all_sections();
        $data['statistiques'] = $this->get_statistiques();
        $data['derniers_tp'] = $this->get_derniers_tp_ajoutes();
        
        $this->load->view('demo/header', $data);
        $this->load->view('demo/tableau_de_bord', $data);
        $this->load->view('demo/footer');
    }

    public function peupler_base() {
        // Exécuter le script SQL de peuplement
        $sql_file = APPPATH . 'database/peuplement.sql';
        
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            $queries = explode(';', $sql);
            
            foreach ($queries as $query) {
                if (trim($query) != '') {
                    $this->db->query($query);
                }
            }
            
            $data['message'] = 'Base de données peuplée avec succès !';
            $data['message_type'] = 'success';
        } else {
            $data['message'] = 'Fichier de peuplement non trouvé';
            $data['message_type'] = 'error';
        }
        
        $this->load->view('demo/header', $data);
        $this->load->view('demo/message', $data);
        $this->load->view('demo/footer');
    }

    public function visualiser_edt($groupe_tp_id = null) {
        $data['groupes_tp'] = $this->get_all_groupes_tp();
        
        if ($groupe_tp_id) {
            $data['edt_selectionne'] = $this->EDT_model->get_edt_complet_groupe_tp($groupe_tp_id);
            $data['groupe_tp_selectionne'] = $this->get_groupe_tp_info($groupe_tp_id);
        }
        
        $this->load->view('demo/header', $data);
        $this->load->view('demo/visualiser_edt', $data);
        $this->load->view('demo/footer');
    }

    public function etat_tables() {
        $data['tables'] = [
            'sections' => $this->db->get('sections')->result(),
            'groupes_td' => $this->db->get('groupes_td')->result(),
            'groupes_tp' => $this->db->get('groupes_tp')->result(),
            'creneaux' => $this->db->get('creneaux')->result(),
            'edt_existants' => $this->db->get('edt_existants')->result(),
            'edt_tp' => $this->db->get('edt_tp')->result()
        ];
        
        $this->load->view('demo/header', $data);
        $this->load->view('demo/etat_tables', $data);
        $this->load->view('demo/footer');
    }

    private function get_statistiques() {
        return [
            'total_sections' => $this->db->count_all('sections'),
            'total_groupes_td' => $this->db->count_all('groupes_td'),
            'total_groupes_tp' => $this->db->count_all('groupes_tp'),
            'total_creneaux' => $this->db->count_all('creneaux'),
            'total_cours_existants' => $this->db->count_all('edt_existants'),
            'total_tp_planifies' => $this->db->count_all('edt_tp')
        ];
    }

    private function get_derniers_tp_ajoutes() {
        $this->db->select('etp.*, gtp.nom as groupe_tp_nom');
        $this->db->from('edt_tp etp');
        $this->db->join('groupes_tp gtp', 'gtp.id = etp.groupe_tp_id');
        $this->db->order_by('etp.id', 'DESC');
        $this->db->limit(5);
        return $this->db->get()->result();
    }

    private function get_all_groupes_tp() {
        $this->db->select('gtp.*, gtd.nom as groupe_td_nom, s.nom as section_nom');
        $this->db->from('groupes_tp gtp');
        $this->db->join('groupes_td gtd', 'gtd.id = gtp.groupe_td_id');
        $this->db->join('sections s', 's.id = gtd.section_id');
        return $this->db->get()->result();
    }

    private function get_groupe_tp_info($groupe_tp_id) {
        $this->db->select('gtp.*, gtd.nom as groupe_td_nom, s.nom as section_nom');
        $this->db->from('groupes_tp gtp');
        $this->db->join('groupes_td gtd', 'gtd.id = gtp.groupe_td_id');
        $this->db->join('sections s', 's.id = gtd.section_id');
        $this->db->where('gtp.id', $groupe_tp_id);
        return $this->db->get()->row();
    }
}
?>