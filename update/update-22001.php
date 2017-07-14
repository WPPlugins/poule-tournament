<?php

class update{
	public function __construct() {
		$this->poule_matches_groups();
		$this->table_poule_matches();
		$this->poule_score();
	}
	
	public function table_poule_matches(){
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
	
	public function poule_matches_groups(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_matches_groups` (
		`id` BIGINT( 20 ) AUTO_INCREMENT ,
		`phase` BIGINT( 20 ) ,
		`group_name` varchar( 100 ) ,
		PRIMARY KEY ( id ));";
		dbDelta($sql);
	}
	
	public function poule_score(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}poule_score` (
		`user_id` BIGINT( 20 ) ,
		`phase` BIGINT( 20 ) ,
		`score` text ,
		PRIMARY KEY ( user_id, phase ));";
		dbDelta($sql);
	}
}

new update();