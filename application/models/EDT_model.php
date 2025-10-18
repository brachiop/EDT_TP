<?php
class EDT_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

// Récupérer les sections - VERSION COMPLÈTEMENT GÉNÉRIQUE
// Récupérer les SECTIONS - VERSION LIKE
public function get_sections() {
    // Sections = enfants des niveaux, avec des noms courts (pas des TD/TP)
    $sql = "
        SELECT rg_section.codeGroupe, rg_section.nom 
        FROM ressources_groupes rg_niveau
        INNER JOIN hierarchies_groupes hg ON rg_niveau.codeGroupe = hg.codeRessource
        INNER JOIN ressources_groupes rg_section ON hg.codeRessourceFille = rg_section.codeGroupe
        WHERE rg_niveau.codeGroupe NOT IN (
            SELECT codeRessourceFille FROM hierarchies_groupes WHERE codeRessourceFille IS NOT NULL
        )
        AND LENGTH(rg_section.nom) <= 5 -- SV1A, SMP3B = 4-5 caractères
        AND rg_section.nom NOT LIKE '% %' -- Pas d'espaces
        AND rg_section.nom NOT LIKE '%G%' -- Exclut les noms avec G (groupes)
        ORDER BY rg_section.nom
    ";
    
    $query = $this->db->query($sql);
    return $query->result();
}

 
    // Récupérer les enfants d'une ressource (générique)
    public function get_enfants_ressource($ressource_id) {
        $this->db->select('rg.codeGroupe, rg.nom');
        $this->db->from('ressources_groupes rg');
        $this->db->join('hierarchies_groupes hg', 'rg.codeGroupe = hg.codeRessourceFille');
        $this->db->where('hg.codeRessource', $ressource_id);
        $this->db->order_by('rg.nom');
        return $this->db->get()->result();
    }
    
    // Récupérer les créneaux TP disponibles pour un groupe TP
/*
    public function get_creneaux_tp_disponibles($groupe_tp_id) {
        $this->db->where('type_cours_id', 3);
        $this->db->where('sous_periode', 'Demi-journée');
        $this->db->where("(jour != 'Samedi' OR periode != 'Après-midi')");
        $tous_creneaux = $this->db->get('creneaux')->result_array();
        
        $creneaux_disponibles = array();
        foreach ($tous_creneaux as $creneau) {
            if (!$this->est_creneau_occupe($groupe_tp_id, $creneau)) {
                $creneaux_disponibles[] = $creneau;
            }
        }
        
        return $creneaux_disponibles;
    }
*/ 
// ***************************************************************

// Récupérer les créneaux TP disponibles AVEC FILTRAGE RÉEL
public function get_creneaux_tp_disponibles($groupe_tp_id) {
    // 1. Récupérer tous les créneaux TP possibles
    $this->db->where('type_cours_id', 3);
    $this->db->where('sous_periode', 'Demi-journée');
    $this->db->where("(jour != 'Samedi' OR periode != 'Après-midi')");
    $tous_creneaux = $this->db->get('creneaux')->result_array();
    
    // 2. Récupérer les créneaux OCCUPÉS pour ce groupe TP
    $creneaux_occupes = $this->get_creneaux_occupes_groupe_tp($groupe_tp_id);
    
    // 3. Filtrer pour garder seulement les créneaux DISPONIBLES
    $creneaux_disponibles = array();
    foreach ($tous_creneaux as $creneau) {
        if (!$this->est_creneau_occupe_par_groupe($creneau, $creneaux_occupes)) {
            $creneaux_disponibles[] = $creneau;
        }
    }
    
    return $creneaux_disponibles;
}

// Récupérer tous les créneaux occupés pour un groupe TP
public function get_creneaux_occupes_groupe_tp($groupe_tp_id) {
    // Récupérer le groupe TP pour avoir sa hiérarchie
    $groupe_tp = $this->db->where('codeGroupe', $groupe_tp_id)->get('ressources_groupes')->row();
    if (!$groupe_tp) return array();
    
    // Récupérer le groupe TD parent
    $groupe_td_id = $this->get_groupe_td_parent($groupe_tp_id);
    
    // Récupérer la section parente
    $section_id = $this->get_section_parente($groupe_td_id);
    
    // Initialiser les tableaux d'occupations
    $occupations_tp = array();
    $occupations_td = array();
    $occupations_section = array();
    
    // 1. Séances DIRECTES du groupe TP
    $occupations_tp = $this->get_occupations_par_ressource($groupe_tp_id);
    
    // 2. Séances du groupe TD (TD)
    if ($groupe_td_id) {
        $occupations_td = $this->get_occupations_par_ressource($groupe_td_id);
    }
    
    // 3. Séances de la section (CM)
    if ($section_id) {
        $occupations_section = $this->get_occupations_par_ressource($section_id);
    }
    
    // Fusionner toutes les occupations
    $occupations = array_merge($occupations_tp, $occupations_td, $occupations_section);
    
    // Éliminer les doublons (même jour + même période)
    $occupations_uniques = array();
    foreach ($occupations as $occ) {
        $key = $occ['jour'] . '|' . $occ['periode'];
        if (!isset($occupations_uniques[$key])) {
            $occupations_uniques[$key] = $occ;
        }
    }
    
    return array_values($occupations_uniques);
}

// Récupérer les occupations d'une ressource (groupe/section)
private function get_occupations_par_ressource($ressource_id) {
    // Récupérer toutes les séances de cette ressource
    $this->db->select('s.dateSeance, s.heureSeance, s.dureeSeance');
    $this->db->from('seances s');
    $this->db->join('seances_groupes sg', 's.codeSeance = sg.codeSeance');
    $this->db->where('sg.codeRessource', $ressource_id);
    $seances = $this->db->get()->result_array();
    
    $occupations = array();
    
    foreach ($seances as $seance) {
        // Convertir la séance en créneau (jour + période)
        $creneau = $this->convertir_seance_en_creneau($seance);
        if ($creneau) {
            $occupations[] = $creneau;
        }
    }
    
    return $occupations;
}

// Convertir une séance (date+heure) en créneau (jour+periode)
private function convertir_seance_en_creneau($seance) {
    $date = $seance['dateSeance'];
    $heure_debut_int = $seance['heureSeance'];
    
    // Convertir la date en jour de la semaine
    $jour_semaine = date('N', strtotime($date)); // 1=lundi, 7=dimanche
    $jours_francais = array('', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
    $jour = $jours_francais[$jour_semaine];
    
    // Déterminer la période (Matin/Après-midi)
    $periode = ($heure_debut_int < 1200) ? 'Matin' : 'Après-midi'; // 1200 = 12h00
    
    return array(
        'jour' => $jour,
        'periode' => $periode,
        'dateSeance' => $date,
        'heureSeance' => $heure_debut_int
    );
}

// Vérifier si un créneau est occupé par le groupe
private function est_creneau_occupe_par_groupe($creneau, $creneaux_occupes) {
    foreach ($creneaux_occupes as $occupe) {
        if ($creneau['jour'] == $occupe['jour'] && $creneau['periode'] == $occupe['periode']) {
            return true;
        }
    }
    return false;
}

// Récupérer le groupe TD parent d'un groupe TP
public function get_groupe_td_parent($groupe_tp_id) {
    $this->db->select('hg.codeRessource');
    $this->db->from('hierarchies_groupes hg');
    $this->db->where('hg.codeRessourceFille', $groupe_tp_id);
    $result = $this->db->get()->row();
    return $result ? $result->codeRessource : null;
}

// Récupérer la section parente d'un groupe TD
public function get_section_parente($groupe_td_id) {
    $this->db->select('hg.codeRessource');
    $this->db->from('hierarchies_groupes hg');
    $this->db->where('hg.codeRessourceFille', $groupe_td_id);
    $result = $this->db->get()->row();
    return $result ? $result->codeRessource : null;
}


// ***************************************************************
    private function est_creneau_occupe($groupe_tp_id, $creneau) {
        $date_seance = $this->get_prochaine_date_par_jour($creneau['jour']);
        $heure_seance = $this->convertir_heure_vers_int($creneau['heure_debut']);
        
        $this->db->select('s.codeSeance');
        $this->db->from('seances s');
        $this->db->join('seances_groupes sg', 's.codeSeance = sg.codeSeance');
        $this->db->where('sg.codeRessource', $groupe_tp_id);
        $this->db->where('s.dateSeance', $date_seance);
        $this->db->where('s.heureSeance', $heure_seance);
        
        return $this->db->count_all_results() > 0;
    }
    
    // Récupérer les matières disponibles pour TP
    public function get_matieres_tp() {
        $this->db->select('m.*, e.codeEnseignement');
        $this->db->from('matieres m');
        $this->db->join('enseignements e', 'e.codeMatiere = m.codeMatiere');
        $this->db->where('e.codeTypeActivite', 3);
        $this->db->group_by('m.codeMatiere');
        return $this->db->get()->result();
    }

    

    
    // Créer une séance de TP
// Créer une séance de TP - VERSION CORRIGÉE
public function creer_seance_tp($data) {
    $this->db->trans_start();
    
    // 1. Récupérer la durée depuis la table enseignements
    $this->db->select('dureeSeance');
    $this->db->from('enseignements');
    $this->db->where('codeEnseignement', $data['code_enseignement']);
    $enseignement = $this->db->get()->row();
    
    $duree = $enseignement ? $enseignement->dureeSeance : 180; // Fallback à 180min
    
    // 2. Créer la séance
    $seance_data = array(
        'dateSeance' => $data['date_seance'], // Maintenant la date correcte
        'heureSeance' => $this->convertir_heure_vers_int($data['heure_debut']),
        'dureeSeance' => $duree, // Durée réelle depuis enseignements
        'codeEnseignement' => $data['code_enseignement']
    );
    
    $this->db->insert('seances', $seance_data);
    $code_seance = $this->db->insert_id();
    
    // 3. Lier le groupe
    $this->db->insert('seances_groupes', array(
        'codeSeance' => $code_seance,
        'codeRessource' => $data['groupe_tp_id']
    ));
    
    // 4. Lier la salle
    $this->db->insert('seances_salles', array(
        'codeSeance' => $code_seance,
        'codeRessource' => $data['salle_id']
    ));
    
    $this->db->trans_complete();
    return $this->db->trans_status();
}
    
    // Méthodes utilitaires
    private function get_prochaine_date_par_jour($jour_francais) {
        $jours = array('Lundi'=>1, 'Mardi'=>2, 'Mercredi'=>3, 'Jeudi'=>4, 'Vendredi'=>5, 'Samedi'=>6);
        $jour_cible = $jours[$jour_francais];
        $aujourdhui = date('N');
        
        $difference = $jour_cible - $aujourdhui;
        if ($difference < 0) $difference += 7;
        
        return date('Y-m-d', strtotime("+$difference days"));
    }
    
    private function convertir_heure_vers_int($heure_mysql) {
        $parts = explode(':', $heure_mysql);
        return intval($parts[0]) * 100 + intval($parts[1]);
    }
    
// Récupérer les salles par département de la matière
// Récupérer les salles par département de la matière - VERSION SÉCURISÉE
public function get_salles_par_matiere($code_enseignement) {
    try {
        // 1. Vérifier si le champ departement existe
        $champ_existe = $this->db->field_exists('departement', 'matieres');
        
        if (!$champ_existe) {
            error_log("Champ 'departement' non trouvé dans la table matieres");
            // Fallback: retourner toutes les salles TP
            return $this->get_salles_tp();
        }
        
        // 2. Récupérer le département de la matière
        $this->db->select('m.departement, m.nom as matiere_nom');
        $this->db->from('matieres m');
        $this->db->join('enseignements e', 'e.codeMatiere = m.codeMatiere');
        $this->db->where('e.codeEnseignement', $code_enseignement);
        $matiere = $this->db->get()->row();
        
        if (!$matiere || empty($matiere->departement)) {
            error_log("Département non trouvé pour code_enseignement: " . $code_enseignement);
            // Fallback: retourner toutes les salles TP
            return $this->get_salles_tp();
        }
        
        // 3. Récupérer les salles du même département
        $this->db->select('codeSalle, nom, typeSalle, departement');
        $this->db->from('ressources_salles');
        $this->db->where('departement', $matiere->departement);
        $this->db->where('typeSalle', 'TP');
        $this->db->order_by('nom');
        
        $salles = $this->db->get()->result();
        
        error_log("Salles trouvées pour département " . $matiere->departement . ": " . count($salles));
        
        return $salles;
        
    } catch (Exception $e) {
        error_log("Erreur dans get_salles_par_matiere: " . $e->getMessage());
        // Fallback en cas d'erreur
        return $this->get_salles_tp();
    }
}

// Méthode fallback - toutes les salles TP
public function get_salles_tp() {
    $this->db->where('typeSalle', 'TP');
    $this->db->order_by('nom');
    return $this->db->get('ressources_salles')->result();
}
/*
    // Récupérer les salles disponibles pour TP
    public function get_salles_tp() {
        $this->db->where('typeSalle', 'TP');
        $this->db->order_by('departement');
        return $this->db->get('ressources_salles')->result();
    }
*/
    
}
?>