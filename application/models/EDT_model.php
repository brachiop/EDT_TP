<?php
class EDT_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database(); // ← AJOUT IMPORTANT
    }

    // Récupérer tous les créneaux occupés pour un groupe TP
    public function get_creneaux_occupes_groupe_tp($groupe_tp_id) {
        $this->db->select('c.*');
        $this->db->from('creneaux c');
        
        // Sous-requête pour les créneaux de TP du groupe
        $this->db->join('edt_tp tp', 'tp.creneau_id = c.id AND tp.groupe_tp_id = '.$groupe_tp_id, 'left');
        
        // Sous-requête pour les créneaux de TD du groupe TD parent
        $this->db->join('groupes_tp gtp', 'gtp.id = '.$groupe_tp_id);
        $this->db->join('edt_existants td', 'td.groupe_td_id = gtp.groupe_td_id AND td.creneau_id = c.id', 'left');
        
        // Sous-requête pour les créneaux de CM de la section
        $this->db->join('groupes_td gtd', 'gtd.id = gtp.groupe_td_id');
        $this->db->join('edt_existants cm', 'cm.section_id = gtd.section_id AND cm.creneau_id = c.id', 'left');
        
        $this->db->where('tp.id IS NOT NULL OR td.id IS NOT NULL OR cm.id IS NOT NULL');
        
        return $this->db->get()->result();
    }

    // Récupérer les créneaux disponibles pour un groupe TP
    public function get_creneaux_disponibles_groupe_tp($groupe_tp_id) {
        $creneaux_occupes = $this->get_creneaux_occupes_groupe_tp($groupe_tp_id);
        $creneaux_occupes_ids = array();
        
        foreach($creneaux_occupes as $creneau) {
            $creneaux_occupes_ids[] = $creneau->id;
        }
        
        if(empty($creneaux_occupes_ids)) {
            return $this->db->get('creneaux')->result();
        }
        
        $this->db->where_not_in('id', $creneaux_occupes_ids);
        return $this->db->get('creneaux')->result();
    }

    // Ajouter un TP à l'EDT
    public function ajouter_tp($data) {
        return $this->db->insert('edt_tp', $data);
    }

    // Récupérer l'EDT complet d'un groupe TP
    public function get_edt_complet_groupe_tp($groupe_tp_id) {
        // Requête pour les TP
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
        
        // Trier par jour et heure
        usort($result, function($a, $b) {
            $jours = array('Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 3, 'Jeudi' => 4, 'Vendredi' => 5, 'Samedi' => 6);
            if ($a['jour'] == $b['jour']) {
                return strcmp($a['heure_debut'], $b['heure_debut']);
            }
            return $jours[$a['jour']] - $jours[$b['jour']];
        });
        
        return $result;
    }
    
            

public function get_creneaux_reellement_disponibles($groupe_tp_id) {
    // Récupérer tous les créneaux existants
    $this->db->select('*');
    $this->db->from('creneaux');
    $this->db->order_by('jour, heure_debut');
    $tous_creneaux = $this->db->get()->result();
    
    // Récupérer l'EDT complet du groupe TP (CM + TD + TP existants)
    $edt_actuel = $this->get_edt_complet_groupe_tp($groupe_tp_id);
    
    // Filtrer les créneaux déjà occupés
    $creneaux_disponibles = array();
    
    foreach ($tous_creneaux as $creneau) {
        $est_occupe = false;
        
        foreach ($edt_actuel as $cours) {
            // Vérifier si le créneau est déjà occupé (même jour et même heure)
            if ($cours['jour'] == $creneau->jour && 
                $cours['heure_debut'] == $creneau->heure_debut) {
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

// Ajoutez ces méthodes dans EDT_model.php
public function get_groupes_td_by_section($section_id) {
    return $this->db->where('section_id', $section_id)->get('groupes_td')->result();
}

public function get_groupes_tp_by_td($groupe_td_id) {
    return $this->db->where('groupe_td_id', $groupe_td_id)->get('groupes_tp')->result();
}

public function get_edt_simule_groupe_tp($groupe_tp_id) {
    // Données simulées cohérentes selon le groupe
    $edt_simule = array();
    
    switch($groupe_tp_id) {
        case 1: // A1a
            $edt_simule = array(
                array('jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Mathématiques', 'salle' => 'Amphi A'),
                array('jour' => 'Lundi', 'heure_debut' => '13:30', 'heure_fin' => '15:30', 'type' => 'TD', 'matiere' => 'TD Mathématiques', 'salle' => 'Salle 101')
            );
            break;
        case 2: // A1b
            $edt_simule = array(
                array('jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Mathématiques', 'salle' => 'Amphi A'),
                array('jour' => 'Mardi', 'heure_debut' => '10:15', 'heure_fin' => '12:15', 'type' => 'TD', 'matiere' => 'TD Physique', 'salle' => 'Salle 102')
            );
            break;
        case 5: // B1a
            $edt_simule = array(
                array('jour' => 'Mardi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Physique', 'salle' => 'Amphi B'),
                array('jour' => 'Mercredi', 'heure_debut' => '13:30', 'heure_fin' => '15:30', 'type' => 'TD', 'matiere' => 'TD Informatique', 'salle' => 'Salle 103')
            );
            break;
        case 6: // B1b
            $edt_simule = array(
                array('jour' => 'Mardi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Physique', 'salle' => 'Amphi B'),
                array('jour' => 'Jeudi', 'heure_debut' => '10:15', 'heure_fin' => '12:15', 'type' => 'TD', 'matiere' => 'TD Chimie', 'salle' => 'Labo A')
            );
            break;
        default:
            $edt_simule = array(
                array('jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Cours général', 'salle' => 'Amphi')
            );
    }
    
    return $edt_simule;
}

// Dans EDT_model.php - Ajoutez cette méthode pour les tests
public function get_edt_avec_contraintes($groupe_tp_id) {
    // Données de test avec contraintes réalistes
    $edt_base = array();
    
    switch($groupe_tp_id) {
        case 1: // A1a - A des cours Lundi 8h-10h et Lundi 13h30-15h30
            $edt_base = array(
                array('jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Mathématiques', 'salle' => 'Amphi A'),
                array('jour' => 'Lundi', 'heure_debut' => '13:30', 'heure_fin' => '15:30', 'type' => 'TD', 'matiere' => 'TD Mathématiques', 'salle' => 'Salle 101')
            );
            break;
        case 2: // A1b - A des cours Lundi 8h-10h et Mardi 10h15-12h15
            $edt_base = array(
                array('jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Mathématiques', 'salle' => 'Amphi A'),
                array('jour' => 'Mardi', 'heure_debut' => '10:15', 'heure_fin' => '12:15', 'type' => 'TD', 'matiere' => 'TD Physique', 'salle' => 'Salle 102')
            );
            break;
        case 5: // B1a - A des cours Mardi 8h-10h et Mercredi 13h30-15h30
            $edt_base = array(
                array('jour' => 'Mardi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Physique', 'salle' => 'Amphi B'),
                array('jour' => 'Mercredi', 'heure_debut' => '13:30', 'heure_fin' => '15:30', 'type' => 'TD', 'matiere' => 'TD Informatique', 'salle' => 'Salle 103')
            );
            break;
        case 6: // B1b - A des cours Mardi 8h-10h et Jeudi 10h15-12h15
            $edt_base = array(
                array('jour' => 'Mardi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Physique', 'sallet' => 'Amphi B'),
                array('jour' => 'Jeudi', 'heure_debut' => '10:15', 'heure_fin' => '12:15', 'type' => 'TD', 'matiere' => 'TD Chimie', 'salle' => 'Labo A')
            );
            break;
        default:
            $edt_base = array(
                array('jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'type' => 'CM', 'matiere' => 'Cours général', 'salle' => 'Amphi')
            );
    }
    
    return $edt_base;
}

// Méthode pour les créneaux disponibles cohérents
public function get_creneaux_disponibles_cohérents($groupe_tp_id) {
    // Tous les créneaux possibles
    $tous_creneaux = $this->db->get('creneaux')->result();
    
    // EDT existant du groupe
    $edt_existant = $this->get_edt_avec_contraintes($groupe_tp_id);
    
    // Créneaux occupés
    $creneaux_occupes = array();
    foreach ($edt_existant as $cours) {
        $creneaux_occupes[] = $cours['jour'] . '_' . $cours['heure_debut'];
    }
    
    // Filtrer
    $creneaux_disponibles = array();
    foreach ($tous_creneaux as $creneau) {
        $cle_creneau = $creneau->jour . '_' . $creneau->heure_debut;
        if (!in_array($cle_creneau, $creneaux_occupes)) {
            $creneaux_disponibles[] = $creneau;
        }
    }
    
    return $creneaux_disponibles;
}



}
?>