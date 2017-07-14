<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * This class contains the matches functions
 *
 * @version 1
 * @author Stefan
 */
class matches {

	public function __construct() {
		include_once POULE_PATH . 'poule-admin/includes/table_match.php';
	}

	/**
	 * Function to create the matches automatic
	 * 
	 * @since version 1
	 * @version 1
	 * @access public
	 * @author Stefan de Bruin
	 * @global type $wpdb
	 * @return nothing show the template file
	 */
	public function auto() {
		global $wpdb, $pouletables;
		
		$table_match = new match_auto();

		$errors = array();
		$input_data  = array();
		
		$phase_error = FALSE;
		$phase_name = (isset($_GET['phase'])) ? $_GET['phase'] : "group";

		$arguments = array();
		
		$wk_ek = get_option('poule_phase_settings');
		
		$phase_info = $wpdb->get_row($wpdb->prepare("SELECT id FROM $pouletables->Phases WHERE name='%s'",$phase_name), ARRAY_A);
		$phase = 0;
        
        
        
		$arguments = array();

		if (count($groups_data) != 0) {
			$errors = array();

			if ($_SERVER['REQUEST_METHOD'] == "POST") {

				$i = 0;
				$input_data = $_POST['group'];
				$insert = TRUE;

				if (isset($input_data)) {
					
					if ($insert) {
						//insert code
						
						
						

						
					}
				}else{
					//error bestaat niet
				}
			}
		}
	}
	/**
	 * Function to sort a multi array
	 * 
	 * @author php.net
	 * @param type $multiArray
	 * @param type $col
	 * @param type $dir
	 * @return type
	 */
	
	
	public static function get_title($row){
		if(isset($_POST['group'][$row]['name'])){
			return $_POST['group'][$row]['name'];
		}else{
			return "";
		}
	}
}

?>
