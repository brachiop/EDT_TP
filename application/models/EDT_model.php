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
/*
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
*/
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
    
// Récupérer les matières disponibles
    public function get_matieres() {
        return $this->db->get('matieres')->result();
    }

    // Récupérer les salles disponibles pour une matière
    public function get_salles_by_matiere($matiere_id) {
        $matiere = $this->db->where('id', $matiere_id)->get('matieres')->row();
        $salles_noms = explode(',', $matiere->salles_disponibles);
        
        $this->db->where_in('nom', $salles_noms);
        return $this->db->get('salles')->result();
    }

    // Vérifier la disponibilité d'une salle pour un créneau
    public function is_salle_disponible($salle_id, $creneau_id, $semaine) {
        $this->db->where('salle_id', $salle_id);
        $this->db->where('creneau_id', $creneau_id);
        $this->db->where('semaine_debut <=', $semaine);
        $this->db->where('semaine_fin >=', $semaine);
        return $this->db->count_all_results('planning_tp') == 0;
    }

    // Planifier un TP avec toutes les contraintes
    public function planifier_tp_avance($data) {
        // Vérifier les contraintes avant insertion
        if (!$this->verifier_contraintes($data)) {
            return false;
        }
        
        return $this->db->insert('planning_tp', $data);
    }

    private function verifier_contraintes($data) {
        // Vérifier si le groupe TP n'a pas déjà ce TP
        $this->db->where('groupe_tp_id', $data['groupe_tp_id']);
        $this->db->where('matiere_id', $data['matiere_id']);
        $this->db->where('seance_numero', $data['seance_numero']);
        if ($this->db->count_all_results('planning_tp') > 0) {
            return false; // Le groupe a déjà cette séance
        }

        // Vérifier disponibilité salle
        if (!$this->is_salle_disponible($data['salle_id'], $data['creneau_id'], $data['semaine_debut'])) {
            return false; // Salle occupée
        }

        return true;
    }

    // Récupérer le planning complet d'un groupe TP
    public function get_planning_complet_groupe_tp($groupe_tp_id) {
        $this->db->select('pt.*, m.nom as matiere_nom, s.nom as salle_nom, c.jour, c.heure_debut, c.heure_fin');
        $this->db->from('planning_tp pt');
        $this->db->join('matieres m', 'm.id = pt.matiere_id');
        $this->db->join('salles s', 's.id = pt.salle_id');
        $this->db->join('creneaux c', 'c.id = pt.creneau_id');
        $this->db->where('pt.groupe_tp_id', $groupe_tp_id);
        return $this->db->get()->result_array();
    }

    // Récupérer les créneaux disponibles (demi-journées)
    public function get_creneaux_disponibles($groupe_tp_id) {
        // Récupérer toutes les demi-journées
        $tous_creneaux = $this->db->get('creneaux')->result_array();
        
        // Récupérer l'EDT complet du groupe
        $edt_actuel = $this->get_edt_complet_groupe_tp($groupe_tp_id);
        
        // Filtrer les demi-journées occupées
        $creneaux_disponibles = array();
        
        foreach ($tous_creneaux as $creneau) {
            $est_occupe = false;
            
            foreach ($edt_actuel as $cours) {
                // Vérifier si la demi-journée est déjà occupée
                if ($cours['jour'] == $creneau['jour'] && $cours['periode'] == $creneau['periode']) {
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

    // Récupérer l'EDT complet avec périodes
/*
    public function get_edt_complet_groupe_tp($groupe_tp_id) {
        // Requête pour les TP existants
        $this->db->select('c.periode, c.jour, c.heure_debut, c.heure_fin, "TP" as type, et.matiere, et.salle');
        $this->db->from('edt_tp et');
        $this->db->join('creneaux c', 'c.id = et.creneau_id');
        $this->db->where('et.groupe_tp_id', $groupe_tp_id);
        $query_tp = $this->db->get();
        $result_tp = $query_tp->result_array();
        
        // Requête pour les TD (occupent aussi des demi-journées)
        $this->db->select('c.periode, c.jour, c.heure_debut, c.heure_fin, "TD" as type, ee.matiere, "" as salle');
        $this->db->from('edt_existants ee');
        $this->db->join('creneaux c', 'c.id = ee.creneau_id');
        $this->db->join('groupes_tp gtp', 'gtp.groupe_td_id = ee.groupe_td_id');
        $this->db->where('gtp.id', $groupe_tp_id);
        $this->db->where('ee.type', 'TD');
        $query_td = $this->db->get();
        $result_td = $query_td->result_array();
        
        // Requête pour les CM (occupent aussi des demi-journées)
        $this->db->select('c.periode, c.jour, c.heure_debut, c.heure_fin, "CM" as type, ee.matiere, "" as salle');
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
*/

    // Vérifier disponibilité salle pour une demi-journée
    public function is_salle_disponible_demi_journee($salle_id, $creneau_id, $semaine) {
        $this->db->where('salle_id', $salle_id);
        $this->db->where('creneau_id', $creneau_id);
        $this->db->where('semaine_debut <=', $semaine);
        $this->db->where('semaine_fin >=', $semaine);
        return $this->db->count_all_results('planning_tp') == 0;
    }

    // Planifier un TP pour une demi-journée
    public function planifier_tp_demi_journee($data) {
        // Vérifier que le créneau est une demi-journée complète
        $creneau = $this->db->where('id', $data['creneau_id'])->get('creneaux')->row();
        if (!$creneau) {
            return false;
        }
        
        // Vérifier les contraintes
        if (!$this->verifier_contraintes_demi_journee($data)) {
            return false;
        }
        
        return $this->db->insert('planning_tp', $data);
    }

    private function verifier_contraintes_demi_journee($data) {
        // Vérifier si le groupe TP n'a pas déjà un cours cette demi-journée
        $creneau = $this->db->where('id', $data['creneau_id'])->get('creneaux')->row();
        
        $this->db->select('pt.*, c.jour, c.periode');
        $this->db->from('planning_tp pt');
        $this->db->join('creneaux c', 'c.id = pt.creneau_id');
        $this->db->where('pt.groupe_tp_id', $data['groupe_tp_id']);
        $this->db->where('c.jour', $creneau->jour);
        $this->db->where('c.periode', $creneau->periode);
        $this->db->where('pt.semaine_debut <=', $data['semaine_debut']);
        $this->db->where('pt.semaine_fin >=', $data['semaine_debut']);
        
        if ($this->db->count_all_results() > 0) {
            return false; // Le groupe a déjà un cours cette demi-journée
        }

        // Vérifier disponibilité salle
        if (!$this->is_salle_disponible_demi_journee($data['salle_id'], $data['creneau_id'], $data['semaine_debut'])) {
            return false; // Salle occupée cette demi-journée
        }

        return true;
    }

    // Récupérer les créneaux disponibles pour les TP (demi-journées)
/*
    public function get_creneaux_tp_disponibles($groupe_tp_id) {
        // Récupérer seulement les créneaux TP (demi-journées)
        $this->db->where('type_cours_id', 3); // TP
        $tous_creneaux_tp = $this->db->get('creneaux')->result_array();
        
        // Récupérer l'EDT complet du groupe
        $edt_actuel = $this->get_edt_complet_groupe_tp($groupe_tp_id);
        
        // Filtrer les demi-journées occupées
        $creneaux_disponibles = array();
        
        foreach ($tous_creneaux_tp as $creneau) {
            $est_occupe = false;
            
            foreach ($edt_actuel as $cours) {
                // Pour les TP, vérifier si la demi-journée est occupée
                if ($cours['type'] == 'TP' && 
                    $cours['jour'] == $creneau['jour'] && 
                    $cours['periode'] == $creneau['periode']) {
                    $est_occupe = true;
                    break;
                }
                
                // Pour les CM/TD, vérifier s'ils occupent la même demi-journée
                if (($cours['type'] == 'CM' || $cours['type'] == 'TD') &&
                    $cours['jour'] == $creneau['jour'] &&
                    $this->est_dans_meme_demi_journee($cours, $creneau)) {
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
*/
    // Vérifier si un cours CM/TD est dans la même demi-journée qu'un créneau TP
/*
    private function est_dans_meme_demi_journee($cours, $creneau_tp) {
        if ($cours['jour'] != $creneau_tp['jour']) return false;
        
        // Déterminer la demi-journée du cours CM/TD
        $heure_cours = strtotime($cours['heure_debut']);
        $periode_cours = ($heure_cours < strtotime('12:00:00')) ? 'Matin' : 'Après-midi';
        
        return $periode_cours == $creneau_tp['periode'];
    }
*/
    // Récupérer l'EDT complet avec les nouveaux créneaux
    public function get_edt_complet_groupe_tp($groupe_tp_id) {
        $result = array();
        
        // TP existants
        $this->db->select('c.jour, c.periode, c.sous_periode, c.heure_debut, c.heure_fin, "TP" as type, et.matiere, et.salle');
        $this->db->from('edt_tp et');
        $this->db->join('creneaux c', 'c.id = et.creneau_id');
        $this->db->where('et.groupe_tp_id', $groupe_tp_id);
        $result = array_merge($result, $this->db->get()->result_array());
        
        // TD existants
        $this->db->select('c.jour, c.periode, c.sous_periode, c.heure_debut, c.heure_fin, "TD" as type, ee.matiere, "" as salle');
        $this->db->from('edt_existants ee');
        $this->db->join('creneaux c', 'c.id = ee.creneau_id');
        $this->db->join('groupes_tp gtp', 'gtp.groupe_td_id = ee.groupe_td_id');
        $this->db->where('gtp.id', $groupe_tp_id);
        $this->db->where('ee.type', 'TD');
        $result = array_merge($result, $this->db->get()->result_array());
        
        // CM existants
        $this->db->select('c.jour, c.periode, c.sous_periode, c.heure_debut, c.heure_fin, "CM" as type, ee.matiere, "" as salle');
        $this->db->from('edt_existants ee');
        $this->db->join('creneaux c', 'c.id = ee.creneau_id');
        $this->db->join('groupes_td gtd', 'gtd.section_id = ee.section_id');
        $this->db->join('groupes_tp gtp', 'gtp.groupe_td_id = gtd.id');
        $this->db->where('gtp.id', $groupe_tp_id);
        $this->db->where('ee.type', 'CM');
        $result = array_merge($result, $this->db->get()->result_array());
        
        return $result;
    }

    // Vérifier conflit pour une demi-journée de TP
    public function verifier_conflit_demi_journee_tp($groupe_tp_id, $creneau_id) {
        $creneau_tp = $this->db->where('id', $creneau_id)->get('creneaux')->row();
        if (!$creneau_tp) return true; // Créneau invalide
        
        // Récupérer l'EDT du groupe
        $edt_groupe = $this->get_edt_complet_groupe_tp($groupe_tp_id);
        
        foreach ($edt_groupe as $cours) {
            // Vérifier si un TP existe déjà cette demi-journée
            if ($cours['type'] == 'TP' && 
                $cours['jour'] == $creneau_tp->jour && 
                $cours['periode'] == $creneau_tp->periode) {
                return true; // Conflit
            }
            
            // Vérifier si un CM/TD existe dans cette demi-journée
            if (($cours['type'] == 'CM' || $cours['type'] == 'TD') &&
                $cours['jour'] == $creneau_tp->jour &&
                $this->est_dans_meme_demi_journee($cours, (array)$creneau_tp)) {
                return true; // Conflit
            }
        }
        
        return false; // Pas de conflit
    }

    // Récupérer les créneaux TP disponibles (exclut samedi après-midi)
    public function get_creneaux_tp_disponibles($groupe_tp_id) {
        // Récupérer seulement les créneaux TP (demi-journées) sauf samedi après-midi
        $this->db->where('type_cours_id', 3); // TP
        $this->db->where("(jour != 'Samedi' OR periode != 'Après-midi')"); // Exclure samedi après-midi
        $tous_creneaux_tp = $this->db->get('creneaux')->result_array();
        
        // Récupérer l'EDT complet du groupe
        $edt_actuel = $this->get_edt_complet_groupe_tp($groupe_tp_id);
        
        // Filtrer les demi-journées occupées
        $creneaux_disponibles = array();
        
        foreach ($tous_creneaux_tp as $creneau) {
            if (!$this->est_demi_journee_occupee($edt_actuel, $creneau)) {
                $creneaux_disponibles[] = $creneau;
            }
        }
        
        return $creneaux_disponibles;
    }





    // Vérifier disponibilité pour planifier un TP
    public function verifier_disponibilite_tp($groupe_tp_id, $creneau_id) {
        $creneau_tp = $this->db->where('id', $creneau_id)->get('creneaux')->row();
        if (!$creneau_tp || $creneau_tp->type_cours_id != 3) {
            return array('disponible' => false, 'message' => 'Créneau invalide pour TP');
        }
        
        // Vérifier exclusion samedi après-midi
        if ($creneau_tp->jour == 'Samedi' && $creneau_tp->periode == 'Après-midi') {
            return array('disponible' => false, 'message' => 'TP non autorisé le samedi après-midi');
        }
        
        // Vérifier conflits avec l'EDT existant
        $edt_groupe = $this->get_edt_complet_groupe_tp($groupe_tp_id);
        
        foreach ($edt_groupe as $cours) {
            if ($this->est_demi_journee_occupee(array($cours), (array)$creneau_tp)) {
                return array('disponible' => false, 'message' => 'Demi-journée déjà occupée');
            }
        }
        
        return array('disponible' => true, 'message' => 'Créneau disponible');
    }
    
    // Récupérer UNIQUEMENT les demi-journées TP disponibles
    public function get_demi_journees_tp_disponibles($groupe_tp_id) {
        // Récupérer UNIQUEMENT les créneaux de demi-journées TP
        $this->db->where('type_cours_id', 3); // Uniquement les TP
        $this->db->where('sous_periode', 'Demi-journée'); // UNIQUEMENT les demi-journées complètes
        $this->db->where("(jour != 'Samedi' OR periode != 'Après-midi')"); // Exclure samedi après-midi
        $this->db->order_by('jour, periode');
        $tous_creneaux_tp = $this->db->get('creneaux')->result_array();
        
        // Récupérer l'EDT complet du groupe pour vérifier les occupations
        $edt_actuel = $this->get_edt_complet_groupe_tp($groupe_tp_id);
        
        // Filtrer les demi-journées occupées
        $demi_journees_disponibles = array();
        
        foreach ($tous_creneaux_tp as $creneau) {
            if (!$this->est_demi_journee_occupee($edt_actuel, $creneau)) {
                $demi_journees_disponibles[] = $creneau;
            }
        }
        
        return $demi_journees_disponibles;
    }
 

    // Vérifier si une demi-journée est occupée
    private function est_demi_journee_occupee($edt_actuel, $creneau_tp) {
        foreach ($edt_actuel as $cours) {
            // Pour les TP, vérifier si la demi-journée est occupée
            if ($cours['type'] == 'TP' && 
                $cours['jour'] == $creneau_tp['jour'] && 
                $cours['periode'] == $creneau_tp['periode']) {
                return true;
            }
            
            // Pour les CM/TD, vérifier s'ils occupent la même demi-journée
            if (($cours['type'] == 'CM' || $cours['type'] == 'TD') &&
                $cours['jour'] == $creneau_tp['jour'] &&
                $this->est_dans_meme_demi_journee($cours, $creneau_tp)) {
                return true;
            }
        }
        
        return false;
    }

    // Vérifier si un cours CM/TD est dans la même demi-journée qu'un créneau TP
    private function est_dans_meme_demi_journee($cours, $creneau_tp) {
        if ($cours['jour'] != $creneau_tp['jour']) return false;
        
        // Déterminer la demi-journée du cours CM/TD
        $heure_cours = strtotime($cours['heure_debut']);
        $periode_cours = ($heure_cours < strtotime('12:00:00')) ? 'Matin' : 'Après-midi';
        
        return $periode_cours == $creneau_tp['periode'];
    }

       
}
?>