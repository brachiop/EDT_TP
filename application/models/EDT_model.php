<?php
class EDT_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Récupérer les groupes TD par section
    public function get_groupes_td_by_section($section_id) {
        return $this->db->where('section_id', $section_id)->get('groupes_td')->result();
    }

    // Récupérer les groupes TP par groupe TD
    public function get_groupes_tp_by_td($groupe_td_id) {
        return $this->db->where('groupe_td_id', $groupe_td_id)->get('groupes_tp')->result();
    }

    // Récupérer l'EDT complet (CM + TD + TP) d'un groupe TP
    public function get_edt_complet_groupe_tp($groupe_tp_id) {
        // Requête pour les TP existants
        $this->db->select('c.jour, c.heure_debut, c.heure_fin, "TP" as type, et.matiere, et.salle');
        $this->db->from('edt_tp et');
        $this->db->join('creneaux c', 'c.id = et.creneau_id');
        $this->db->where('et.groupe_tp_id', $groupe_tp_id);
        $query_tp = $this->db->get();
        $result_tp = $query_tp->result_array();
        
        // Requête pour les TD
        $this->db->select('c.jour, c.heure_debut, c.heure_fin, "TD" as type, ee.matiere, "" as salle');
        $this->db->from('edt_existants ee');
        $this->db->join('creneaux c', 'c.id = ee.creneau_id');
        $this->db->join('groupes_tp gtp', 'gtp.groupe_td_id = ee.groupe_td_id');
        $this->db->where('gtp.id', $groupe_tp_id);
        $this->db->where('ee.type', 'TD');
        $query_td = $this->db->get();
        $result_td = $query_td->result_array();
        
        // Requête pour les CM
        $this->db->select('c.jour, c.heure_debut, c.heure_fin, "CM" as type, ee.matiere, "" as salle');
        $this->db->from('edt_existants ee');
        $this->db->join('creneaux c', 'c.id = ee.creneau_id');
        $this->db->join('groupes_td gtd', 'gtd.section_id = ee.section_id');
        $this->db->join('groupes_tp gtp', 'gtp.groupe_td_id = gtd.id');
        $this->db->where('gtp.id', $groupe_tp_id);
        $this->db->where('ee.type', 'CM');
        $query_cm = $this->db->get();
        $result_cm = $query_cm->result_array();
        
        // Fusionner tous les résultats
        $result = array_merge($result_tp, $result_td, $result_cm);
        
        return $result;
    }

    // Récupérer les créneaux disponibles (non occupés)
    public function get_creneaux_disponibles($groupe_tp_id) {
        // Récupérer tous les créneaux
        $tous_creneaux = $this->db->get('creneaux')->result_array();
        
        // Récupérer l'EDT complet
        $edt_actuel = $this->get_edt_complet_groupe_tp($groupe_tp_id);
        
        // Filtrer les créneaux occupés
        $creneaux_disponibles = array();
        
        foreach ($tous_creneaux as $creneau) {
            $est_occupe = false;
            
            foreach ($edt_actuel as $cours) {
                // Normaliser les formats d'heure pour la comparaison
                $heure_creneau = substr($creneau['heure_debut'], 0, 5);
                $heure_cours = substr($cours['heure_debut'], 0, 5);
                
                if ($cours['jour'] == $creneau['jour'] && $heure_cours == $heure_creneau) {
                    $est_occupe = true;
                    break;
                }
            }
            
            if (!$est_occupe) {
                $creneaux_disponibles[] = $creneau;
            }
        }
        
        return $creneaux_disponibles;
    }

    // Ajouter un TP à la base de données
    public function ajouter_tp($groupe_tp_id, $creneau_id, $matiere, $salle) {
        $data = array(
            'groupe_tp_id' => $groupe_tp_id,
            'creneau_id' => $creneau_id,
            'matiere' => $matiere,
            'salle' => $salle
        );
        
        return $this->db->insert('edt_tp', $data);
    }
}
?>