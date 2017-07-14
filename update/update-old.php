<?php

class update_22{
	public function __construct() {
		
		$this->Table_podium();
		$this->Table_Subpoules();
		$this->Table_Countries();
		$this->Table_phases();
		$this->Update_table_matches();
	}
	
	private function Table_podium(){
		global $wpdb;
		
		if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}poule_podium'") ==  $wpdb->prefix.'poule_podium') {
			$wpdb->query("TRUNCATE " . $wpdb->prefix.'poule_podium');
			$wpdb->query("DROP TABLE " . $wpdb->prefix.'poule_podium');
		}
	}
	
	private function Table_Subpoules(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_subpoule_accounts` (
		`id` BIGINT( 20 ) AUTO_INCREMENT ,
		`phase_id` BIGINT( 20 ) ,
		`email` VARCHAR( 200 ) ,
		PRIMARY KEY ( id, phase_id ));";
		dbDelta($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_subpoule_users` (
		`id` BIGINT( 20 ) AUTO_INCREMENT ,
		`phase_id` BIGINT( 20 ) ,
		`user_id` BIGINT( 20 ) ,
		`token` VARCHAR( 50 ) ,
		`invitation_time` datetime ,
		`update_time` datetime ,
		PRIMARY KEY ( id, phase_id, user_id ));";
		dbDelta($sql);
		
		if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}poule_poules'") !=  $wpdb->prefix.'poule_poules') {
			return false;
			//bestaat niet
		}
		
		foreach($wpdb->get_results("SELECT * FROM {$wpdb->prefix}wp_poule_poules") as $subpoule){
			$post = array(
  				'post_name' => sanitize_title($subpoule->name),
  				'post_title' => $subpoule->name,
  				'post_status' => 'publish',
  				'post_type' => 'country',
  				'ping_status' => 'closed',
  				'comment_status' => 'closed',
				'post_author' => $subpoule->creator
			);  
			
			$subpoule_id = wp_insert_post($post);
			
			foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_poules_users WHERE group_id='%d'",$subpoule->id)) as $subpoule_user){
				
				if($subpoule_user->token == "" || $subpoule_user->token == null){
					$token = poule_create_token();
				}else{
					$token = $subpoule_user->token;
				}
				
				$wpdb->insert(
					$wpdb->prefix.'poule_subpoule_users' ,
					array(
						'poule_id' => $subpoule_id,
						'user_id' => $subpoule_user->user_id,
						'token' => $token,
						'status' => '1',
						'Invitation_time' => current_time( 'mysql' ),
						'update_time' => current_time( 'mysql' )
					)
				);
				
				$wpdb->delete(
					$wpdb->prefix.'poule_poules_users',
					array('id' => $subpoule_user->id)
				);
				
			}
			
			$wpdb->delete(
				$wpdb->prefix.'poule_poules',
				array('id' => $subpoule->id)
			);
		}
		
		$wpdb->query("DROP TABLE " . $wpdb->prefix.'poule_poules_users');
		$wpdb->query("DROP TABLE " . $wpdb->prefix.'poule_poules');
	}
	
	private function Table_phases(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		//tabellen toevoegen
		//-poule_phase_meta
			
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_phase_meta` (
		`id` INT( 11 ) AUTO_INCREMENT ,
		`phase_id` INT( 11 ) ,
		`meta_key` VARCHAR( 255 ) ,
		`meta_value` TEXT,
		PRIMARY KEY ( id, phase_id ));";
		dbDelta($sql);
		
		//update phases phases
		foreach($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_phases") as $id => $phase){
			$id = wp_insert_term(
  				$phase->name, 
  				'phase',
  				array(
    				'slug' => sanitize_title($phase->name)
  				)
			);
			
			//custom data toevoegen
			Poule_Update_Phase_Meta($id['term_id'], 'groups', $phase->count);
			Poule_Update_Phase_Meta($id['term_id'], 'matches_per_group', $phase->match_group);
			Poule_Update_Phase_Meta($id['term_id'], 'penalties', 1);
			
			$wpdb->update(
				$wpdb->prefix. 'poule_matches_groups',
				array('phase' => $id['term_id']),
				array('phase'=>$phase->id)
			);
			
			$wpdb->delete(
				$wpdb->prefix."poule_phases",
				array('id' => $phase->id)
			);
		}
		
		$wpdb->query("DROP TABLE " . $wpdb->prefix.'poule_phases');
	}
	
	private function Update_table_matches(){
		global $wpdb;
		
		$wpdb->query("ALTER TABLE {$wpdb->prefix}poule_matches ADD start_date datetime");
		
		foreach($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_matches") as $match){
			$wpdb->update(
				$wpdb->prefix.'poule_matches',
				array('start_date' => date("Y-m-d H:i:s", $match->start_time)),
				array('id' => $match-id)
			);
		}
		
		$wpdb->query("ALTER TABLE {$wpdb->prefix}poule_matches DROP COLUMN start_time");
	}
	
	private function Table_Countries(){
		global $wpdb;
		//landen
		$countries = array();
		foreach($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_countries") as $country){
			$post = array(
  				'post_name' => sanitize_title($country->name),
  				'post_title' => $country->name,
  				'post_status' => 'publish',
  				'post_type' => 'country',
  				'ping_status' => 'closed',
  				'comment_status' => 'closed'
			);  
			
			$country_id = wp_insert_post($post);
			
			$countries[$country->id] = $country_id;
			
			$wpdb->delete(
				$wpdb->prefix.'poule_countries',
				array('id' => $country->id)
			);
		}
		
		$wpdb->query("DROP TABLE " . $wpdb->prefix.'poule_countries');
		
		foreach($countries as $key => $country){
			
			$wpdb->update(
				$wpdb->prefix. 'poule_matches',
				array('country_1' => $country),
				array('country_1'=> $key)
			);
			
			$wpdb->update(
				$wpdb->prefix. 'poule_matches',
				array('country_2' => $country),
				array('country_2'=> $key)
			);
		}
	}
	
}

new update_22();