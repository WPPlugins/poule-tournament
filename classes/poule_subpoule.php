<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Description of poule_subpoules
 *
 * @author Stefan
 */
class poule_subpoule {
    
    private $own = FALSE;
    
    public $poule_id;
    
    public $name;
    
    public $create_date;
    
    public $user_count;
    
    public function __construct($poule_id = FALSE) {
        if($poule_id !== FALSE){
            $this->poule_id = $poule_id;
        }
    }
    
    public function poule(){
        $poule_info = get_post($this->poule_id);
    }
    
    public function poules(){
    	global $wpdb;
    	
        $args = array(
            'author'           => get_current_user_id(),
            'posts_per_page'   => -1,
            'offset'           => 0,
            'category'         => '',
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'include'          => '',
            'exclude'          => '',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'subpoule',
            'post_mime_type'   => '',
            'post_parent'      => '',
            'post_status'      => 'publish',
            'suppress_filters' => true 
        );

        $poules = get_posts( $args );
        $return = array();
        if($poules){
            foreach ($poules as $poule){
            	
            	$users = $wpdb->get_row($wpdb->prepare("SELECT COUNT(id) as users FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%d'",$poule->ID));
                $poule->users = $users->users;
                $return[] = $poule;
            }
        }
        return $return;
    }
}