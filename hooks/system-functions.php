<?php
/**
 * All the open function
 * 
 * Contains all the open functions
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get poule meta
 * 
 * Get the phase meta by a sql query with a meta_key
 * 
 * @access public
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @package poule_tournament
 * @since version 2.2
 * @version 1
 * @global database $wpdb
 * @param int $phase_id 
 * @param string|FALSE $meta_key default FALSE return all the meta's or a selected meta key
 * @return string|null|array the meta value or null
 */
function Poule_Get_Phase_Meta($phase_id, $meta_key = FALSE){
    global $wpdb;
    
    $data = array();
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_phase_meta WHERE phase_id='%d'",$phase_id), OBJECT);
//	var_dump($rows);
    foreach($rows as $row){
        if($meta_key){
            if($row->meta_key == $meta_key) $data[$row->meta_key] = $row->meta_value;
        }else{
            $data[$row->meta_key] = $row->meta_value;
        }
    }
    if(count($data) == 0) return null;
    
    return (object) $data;
}

/**
 * Add or update phase meta
 * 
 * Add or update a phase meta by a query
 * 
 * @access public
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @package poule_tournament
 * @since version 2.2
 * @version 1
 * @global database $wpdb 
 * @param int $phase_id the id of the pase
 * @param sting $meta_key the key of the meta
 * @param string $meta_value the meta value for save
 * @return boolean check if updates is correct
 */
function Poule_Update_Phase_Meta($phase_id, $meta_key, $meta_value){
    global $wpdb;
    
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_phase_meta WHERE phase_id='%s' AND meta_key='%s'",$phase_id,$meta_key));
    if($row == null){
        $check = $wpdb->insert($wpdb->prefix.'poule_phase_meta',array('phase_id' => $phase_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value));
        //insert
    }else{
        $check = $wpdb->update($wpdb->prefix.'poule_phase_meta',array('meta_value' => $meta_value),array('phase_id' => $phase_id, 'meta_key' => $meta_key));
        //update
    }
        
    return $check;
}

function poule_delete_phase_meta($id){
	global $wpdb;
	
	$wpdb->delete( $wpdb->prefix.'poule_phase_meta', array('phase_id' => $id));
}

/**
 * Create a token
 * 
 * Create the token with the current time 
 * 
 * @access public
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @package poule_tournament
 * @since version 2.2
 * @version 1
 * @param int $length the length of the token default 20
 * @return string the created token
 */
function poule_create_token($length = 20){
    $d=date ("d");
    $m=date ("m");
    $y=date ("Y");
    $t=time();
    $dmt=$d+$m+$y+$t;    
    $ran= rand(0,10000000);
    $dmtran= $dmt+$ran;
    $un=  uniqid();
    $dmtun = $dmt.$un;
    $mdun = md5($dmtran.$un);
    $sort=substr($mdun, $length); 

    return $sort;
}

/**
 * get the phases
 * 
 * get all the items in taxonomie phase
 * 
 * @access public
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @package poule_tournament
 * @since version 1
 * @version 2.2
 * @return array all the phases
 */
function poule_get_phases(){
	$taxonomyname = "phase";
	$args = array(
		'hide_empty' => '0',
		'hierarchical' => '0',
		'parent' => '0',
		'orderby' => 'id',
		'order' => 'ASC'
	);
	$terms = get_terms($taxonomyname, $args);
	return $terms;
}

/**
 * get the countries
 * 
 * get all the countries as array 
 * 
 * @access public
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @package poule_tournament
 * @since version 1
 * @version 2.2
 * @return array the countries
 */
function poule_get_countries(){
	$args = array(
		'post_type' => 'country',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);

	$countries = get_posts($args);
	return $countries;
}

function poule_create_correct_url_OK($get, $valueget, $admin = FALSE){
	$url = $_SERVER['REQUEST_URI'];
	unset($_GET[$get]);
	$start = TRUE;
	foreach($_GET as $key => $value){
		$url .= ($start) ? '?' : '&';
		$start = FALSE;
		$url .= $key . '=' . $value;
	}
	$url .= ($start) ? '?' : '&';
	$url .= $get . '=' . $valueget;
	return $url;
}

function poule_create_correct_url($gets, $unset = array(), $admin = FALSE){
	
	$split = explode('?', $_SERVER['REQUEST_URI']);
	$url = (count($split) != 0) ? $split[0] : $split;
	foreach($gets as $key => $value){
		unset($_GET[$key]);
	}
	
	$start = TRUE;
	foreach($_GET as $key => $value){
		$url .= ($start) ? '?' : '&';
		$start = FALSE;
		$url .= $key . '=' . $value;
	}
	
	foreach($gets as $key => $value){
		$url .= ($start) ? '?' : '&';
		$start = FALSE;
		$url .= $key . '=' . $value;
	}
	
	return $url;
}