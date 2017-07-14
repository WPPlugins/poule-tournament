<?php

class poule_install{
	public function __construct($version) {
		global $wpdb;
		//check if country table exist
		if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}poule_countries'") ==  $wpdb->prefix.'poule_countries') {
			//run update script
			require_once(POULE_PATH . 'update/update.php');
			new poule_update($version);
		}else{
			//install script
			self::table_poule_matches();
			self::poule_matches_groups();
			self::poule_phase_meta();
			self::poule_score();
			self::poule_subpoule_users();
			self::poule_subpoule_accounts();
		}
	}
	
	public static function table_poule_matches(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_matches` (
		`id` BIGINT( 20 ) AUTO_INCREMENT ,
		`group_id` BIGINT( 20 ) ,
		`country_1` BIGINT( 20 ) ,
		`country_2` BIGINT( 20 ) ,
		`start_date` datetime ,
		`score` varchar(255) ,
		PRIMARY KEY ( id, group_id ));";
		dbDelta($sql);
	}
	
	public static function poule_matches_groups(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_matches_groups` (
		`id` BIGINT( 20 ) AUTO_INCREMENT ,
		`phase` BIGINT( 20 ) ,
		`group_name` varchar( 100 ) ,
		PRIMARY KEY ( id ));";
		dbDelta($sql);
	}
	
	public static function poule_phase_meta(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_phase_meta` (
		`id` INT( 11 ) AUTO_INCREMENT ,
		`phase_id` INT( 11 ) ,
		`meta_key` VARCHAR( 255 ) ,
		`meta_value` TEXT,
		PRIMARY KEY ( id, phase_id ));";
		dbDelta($sql);
	}
	
	public static function poule_score(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_score` (
		`user_id` BIGINT( 20 ) ,
		`phase` BIGINT( 20 ) ,
		`score` text ,
		PRIMARY KEY ( user_id, phase ));";
		dbDelta($sql);
	}
	
	public static function poule_subpoule_accounts(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_subpoule_accounts` (
		`id` BIGINT( 20 ) AUTO_INCREMENT ,
		`phase_id` BIGINT( 20 ) ,
		`email` VARCHAR( 200 ) ,
		PRIMARY KEY ( id, phase_id ));";
		dbDelta($sql);
	}
	
	public static function poule_subpoule_users(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_subpoule_users` (
		`id` BIGINT( 20 ) AUTO_INCREMENT ,
		`phase_id` BIGINT( 20 ) ,
		`user_id` BIGINT( 20 ) ,
		`token` VARCHAR( 50 ) ,
		`invitation_time` datetime ,
		`update_time` datetime ,
		PRIMARY KEY ( id, phase_id, user_id ));";
		dbDelta($sql);
	}
}