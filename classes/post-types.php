<?php
/**
 * This file add custom post type and taxonomy
 * 
 * It's add the post type subpoule and country. Aso it's at the custom taxonomy phase
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * class that add it
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class poule_post_types {
	
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
//        add_action('admin_init',array($this,'register_post_types'));
//        add_action('admin_init',array($this,'add_custom_taxonomy'), 0 );    
		
		add_action('init',array($this,'register_post_types'));
        add_action('init',array($this,'add_custom_taxonomy'), 0 );    
    }
    
	/**
	 * Add custom post type
	 * 
	 * Add the custom post types subpoule and country
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function register_post_types(){
        $labels = array(
            'name'               => __( 'Countries', 'poule-tournament' ),
            'singular_name'      => __( 'Country', 'poule-tournament' ),
            'add_new'            => __( 'Add Country', 'poule-tournament' ),
            'add_new_item'       => __( 'Add New Country','poule-tournament' ),
            'edit' 				 => __( 'Edit', 'poule-tournament' ),
            'edit_item'          => __( 'Edit Country','poule-tournament' ),
            'new_item'           => __( 'New Country','poule-tournament' ),
            'all_items'          => __( 'All Countries', 'poule-tournament' ),
            'view'               => __( 'View Country' , 'poule-tournament'),
            'view_item'          => __( 'View Country', 'poule-tournament' ),
            'search_items'       => __( 'Search Countries', 'poule-tournament' ),
            'not_found'          => __( 'No Countries found', 'poule-tournament' ),
            'not_found_in_trash' => __( 'No Countries found in the Trash', 'poule-tournament' ), 
            'parent_item_colon'  => '',
            'menu_name'          => __('Countries','poule-tournament')
        );
        
        $arguments = array(
            'labels'        => $labels,
            'description'   => __('Here can you add new tournament phases', 'poule-tournament' ), 
            'public'        => true,
			'publicly_queryable' => FALSE,
            'menu_position' => 50,
            'supports'      => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'   => true,
        );
        
        register_post_type( 'country', $arguments );
        flush_rewrite_rules();
        
        $labels = array(
            'name'               => __( 'Subpoules', 'poule-tournament' ),
            'singular_name'      => __( 'Subpoule', 'poule-tournament' ),
            'add_new'            => __( 'Add Subpoule', 'poule-tournament' ),
            'add_new_item'       => __( 'Add New Subpoule','poule-tournament' ),
            'edit' 				 => __( 'Edit', 'poule-tournament' ),
            'edit_item'          => __( 'Edit Subpoule','poule-tournament' ),
            'new_item'           => __( 'New Subpoule','poule-tournament' ),
            'all_items'          => __( 'All Subpoules', 'poule-tournament' ),
            'view'               => __( 'View Subpoule' , 'poule-tournament'),
            'view_item'          => __( 'View Subpoule', 'poule-tournament' ),
            'search_items'       => __( 'Search Subpoule', 'poule-tournament' ),
            'not_found'          => __( 'No Subpoules found', 'poule-tournament' ),
            'not_found_in_trash' => __( 'No Subpoules found in the Trash', 'poule-tournament' ), 
            'parent_item_colon'  => '',
            'menu_name'          => __('Subpoules','poule-tournament')
        );
        
        $arguments = array(
            'labels'        => $labels,
            'description'   => __('Here can you add new subpoules', 'poule-tournament' ), 
            'public'        => true,
			'publicly_queryable' => FALSE,
            'menu_position' => 51,
            'supports'      => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'   => true,
        );
        
        register_post_type( 'subpoule', $arguments );
        flush_rewrite_rules();
    }
    
	/**
	 * Add the custom taxonomy
	 * 
	 * Add phase
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function add_custom_taxonomy(){
        $labels = array(
		'name'              => __( 'Phases', 'poule-tournament' ),
		'singular_name'     => __( 'Phase', 'poule-tournament' ),
		'search_items'      => __( 'Search Phases', 'poule-tournament' ),
		'all_items'         => __( 'All Phases', 'poule-tournament' ),
		'parent_item'       => __( 'Parent Phase', 'poule-tournament' ),
		'parent_item_colon' => __( 'Parent Phase:', 'poule-tournament' ),
		'edit_item'         => __( 'Edit Phase', 'poule-tournament' ),
		'update_item'       => __( 'Update Phase', 'poule-tournament' ),
		'add_new_item'      => __( 'Add New Phase', 'poule-tournament' ),
		'new_item_name'     => __( 'New Phase Name', 'poule-tournament' ),
		'menu_name'         => __( 'Phase' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'public'			=> true,
		'rewrite'           => array( 'slug' => 'phase' ),
	);

	register_taxonomy( 'phase', array( 'country' ), $args );
    flush_rewrite_rules();
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
new poule_post_types();