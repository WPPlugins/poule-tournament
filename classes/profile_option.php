<?php

/**
 * This file create the custom mete boxes.
 * 
 * It's create the custom meta boxes for custom post stype subpoule and the cusotm filed for custom taxonomie phase 
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The class that create it
 * 
 * validate the post input
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class profile_option{
	
	/**
	 * constructor
	 * 
	 * The constructor for the class. Its add the hooks and filters for wordpress
	 * 
	 * @package poule_tournament
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @access public
	 */
	public function __construct() {
		add_action( 'show_user_profile', array($this,'poule_enabled'));
		add_action( 'edit_user_profile', array($this,'poule_enabled'));

		add_action( 'personal_options_update', array($this,'save_poule_enabled'));
		add_action( 'edit_user_profile_update', array($this,'save_poule_enabled'));
	}
	
	/**
	 * Add the fields
	 * 
	 * Field for shoe on the podium
	 * 
	 * @param object $user user information
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function poule_enabled($user){
		
		echo '<h3>' . __('Poule tournament', 'poule-tournament') . '</h3>';
		
		echo '<table class="form-table">';
		echo '<tr>';
		
		echo '<th><label>' . __('Show on podium', 'poule-tournament') . '</label></th>';
		
		$check = '';
		if(get_the_author_meta( '_poule_podium', $user->ID ) != '' || get_the_author_meta( '_poule_podium', $user->ID ) == 1){
			$check = 'checked="checked"';
		}
		echo '<td>' . '<input name="podium" type="checkbox"' . $check . ' value="1"/>' . '</td>';
		
		//<span class="description"><?php _e('Please enter your address.', 'your_textdomain');</span>
		echo '</tr>';
		echo '</table>';
		
	}
	
	/**
	 * Save the settings
	 * 
	 * Save the settings
	 * 
	 * @param int $user_id Id of the current user
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function save_poule_enabled($user_id){
		if ( !current_user_can( 'edit_user', $user_id ) )
			return FALSE;
		
		if(isset($_POST['podium'])){
			$value="1";
		}else{
			$value="0";
		}
		update_user_meta( $user_id, '_poule_podium', $value );
	}
	
}

/**
 * initialization of the class
 * 
 * initialize of the class
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @package poule_tournament
 * @since version 2.2
 * @version 1
 */
new profile_option();

?>