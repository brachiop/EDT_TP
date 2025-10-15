<?php
class Section_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database(); // ← AJOUT
    }

    public function get_all_sections() {
        return $this->db->get('sections')->result();
    }

    public function get_groupes_td_by_section($section_id) {
        return $this->db->where('section_id', $section_id)->get('groupes_td')->result();
    }
}
?>