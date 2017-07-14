<?php

class update{
	public function __construct() {
		$this->poule_score();
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