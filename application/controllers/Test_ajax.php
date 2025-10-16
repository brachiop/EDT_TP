<?php
class Test_ajax extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->database();
    }

    public function index() {
        echo "<h1>Test des appels AJAX</h1>";
        
        // Test de connexion à la base
        echo "<h2>Test Base de Données:</h2>";
        if ($this->db->initialize()) {
            echo "✅ Connexion BD OK<br>";
            
            // Test des tables
            $tables = array('sections', 'groupes_td', 'groupes_tp', 'creneaux', 'edt_existants', 'edt_tp');
            foreach ($tables as $table) {
                if ($this->db->table_exists($table)) {
                    $count = $this->db->count_all($table);
                    echo "✅ Table $table: $count enregistrements<br>";
                } else {
                    echo "❌ Table $table: N'EXISTE PAS<br>";
                }
            }
        } else {
            echo "❌ Erreur connexion BD<br>";
        }
        
        echo "<h2>Test URLs AJAX:</h2>";
        $urls = array(
            'get_groupes_td' => site_url('edt/get_groupes_td?section_id=1'),
            'get_groupes_tp' => site_url('edt/get_groupes_tp?groupe_td_id=1'),
            'get_edt_groupe' => site_url('edt/get_edt_groupe?groupe_tp_id=1'),
            'get_creneaux_disponibles' => site_url('edt/get_creneaux_disponibles?groupe_tp_id=1')
        );
        
        foreach ($urls as $nom => $url) {
            echo "$nom: <a href='$url' target='_blank'>$url</a><br>";
        }
    }
}
?>