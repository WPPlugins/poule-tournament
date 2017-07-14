<?php
/**
 * This file add the dashboard widget
 * 
 * Add the widget
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Add it
 * 
 * Add the widget and config page
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class dashboard {
	
	/**
	 * constructor
	 * 
	 * The constructor for the class for the plugin. Its add the hooks and filters for wordpress
	 * 
	 * @package poule_tournament
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @access public
	 */
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
		
		add_filter('poule_table_dashboard_column_place', array($this,'column_place'),10,1);
		add_filter('poule_table_dashboard_column_fullname', array($this,'column_fullname'),10,1);
		add_filter('poule_table_dashboard_column_points', array($this,'column_points'),10,1);
    }
    
	/**
	 * Call the function to add
	 * 
	 * Check if the user can manage_options to add the widget
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function add_dashboard_widgets(){
		if ( current_user_can('manage_options') ) {
// 			wp_add_dashboard_widget('poule-tournament', __('Poule score', 'poule-tournament'), array($this, 'gadget'));
// 
			//, array($this, 'config')
		}
    }
    
	/**
	 * the widget
	 * 
	 * Calculate the podium only the top 3
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function gadget() {
		global $wpdb;
		
		$users = array();
		
		$widget_settings = get_option('poule_widget_settings',0);
		
		if(is_array($widget_settings)){
			$subpoule = (array_key_exists('subpoule', $widget_settings)) ? $widget_settings['subpoule'] : "0" ;
		}else{
			$subpoule = 0;
		}
		
		if(get_post($subpoule) != null){
			
			$check = FALSE;
			
			foreach ($wpdb->get_results($wpdb->prepare("SELECT distinct user_id FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%s'",get_post($subpoule)->ID), ARRAY_A) as $user){
				if(get_current_user_id() == $user['user_id']) $check = true;

				$users[$user['user_id']] = array('id' => $user['user_id']);
			}
			
			if(!$check){
				$users = array();
			}
		}
	
		if(count($users) == 0){
			foreach(get_users(array()) as $user){
				
				$check = get_user_meta($user->ID, '_poule_podium', TRUE);
				
				if($check == null || $check == 0 ){
					continue;
				}
				$users[$user->ID] = array('id' => $user->ID);
			}
		}

		$matches = array();
		foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_matches",ARRAY_A) as $id => $match) {
			$matches[$match['id']] = $match;
		}

		/*
		 * Get the points
		 */
		foreach($users as $userid => $user){
			$points = 0;
			foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_score WHERE user_id='%s'",$userid),ARRAY_A) as $id => $phase) {
				$scores = unserialize($phase['score']);
				foreach($scores as $key => $score){
					if($score['score_1'] != '' && $score['score_2'] != ''){
						
						$match_info = unserialize($matches[$key]['score']);
						if($match_info['score_1'] == $score['score_1'] && $score['score_1'] == $score['score_2']){
							$points += 2;
						}else if($match_info['score_1'] > $match_info['score_2'] && $score['score_1'] > $score['score_2']){
							$points++;
						}else if($match_info['score_1'] < $match_info['score_2'] && $score['score_1'] < $score['score_2']){
							$points++;
						}
					}
				}
			}
			$users[$userid]['points'] = $points;
		}

		$place = 1;
		foreach($users as $id => $user){
			$user_info = get_userdata( $id );
			$users[$id]['fullname'] = $user_info->first_name . ' ' . $user_info->last_name;
			
			if(trim($users[$id]['fullname']) == ""){
				$users[$id]['fullname'] = $user_info->display_name;
			}
			$users[$id]['url'] = $user_info->user_nicename;
			$users[$id]['place'] = $place;
			$place++;
		}
		
		$new = array();
		
		$quantity = 3;
		if($quantity != null){
			$i = 1;
			foreach($users as $key => $user){
				if($i <= $quantity){
					$new[] = $user;
				}
				$i++;
			}
		}
			
		$users = (count($new) != 0) ? $new : $users;
		
		$table_match = new create_table('dashboard','home');
		$table_match->columns = array('place' => 'Place', 'fullname' => 'Full name', 'points' => 'Points');
		$table_match->data = $users;
		$table_match->prepare_items();


		include_once POULE_PATH . 'poule-admin/template/dashboard/home.php';
	}
       
	/**
	 * Config function 
	 * 
	 * And the config and save the data
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $file the filename
	 * @param array $message replace text
	 */
	public function config() {
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$data = array(
				'subpoule' => ($_POST['subpoule']),
			);
			
			update_option('poule_widget_settings', $data, TRUE);
		}
		
		include_once POULE_PATH_2 . 'poule-admin/template/dashboard/config.php';
		
		
//
//		if (!$settings = get_option('poule_widget_settings')) {
//			$settings = array(
//				'hours' => 12,
//				'no_score' => 1,
//			);
//
//			update_option('poule_widget_settings', $settings);
//		}
//
//		if ('POST' == $_SERVER['REQUEST_METHOD']) {
//			$data = array(
//				'hours' => $_POST['hours'],
//				'no_score' => $_POST['score'],
//			);
//
//			update_option('poule_widget_settings', $data);
//		}
//
//		$hours = $settings['hours'];
//		if ($settings['no_score'] == 1) {
//			$score = 'checked="checked"';
//		} else {
//			$score = "";
//		}




		//include_once POULE_PATH . 'poule-admin/templates/dashboard/config.php';
	}
	
	/**
	 * add a column
	 * 
	 * Add the place column content
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $item row content
	 */
	public function column_place($item){
        return $item['place'];
    }
	
	/**
	 * add a column
	 * 
	 * Add the fullname column content
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $item row content
	 */
	public function column_fullname($item){
        return $item['fullname'];
    }
	
	/**
	 * add a column
	 * 
	 * Add the points column content
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $item row content
	 */
	public function column_points($item){
        return $item['points'];
    }
}

new dashboard();