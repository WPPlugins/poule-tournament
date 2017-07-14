<?php
/**
 * File with all template functions
 * 
 * Contains all the template function for the custom post type to view their files
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * matches pages class
 * 
 * Class with all the function to show the pages for matches
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class template {
	
	/**
	 * constructor
	 * 
	 * The constructor for the class. Its add the hooks and filters for wordpress
	 * 
	 * @package poule_tournament
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 1
	 * @version 2.2
	 * @access public
	 */
    public function __construct() {
        add_filter( "template_include", array($this,"template_include"));
    }
    
	/**
	 * Choise another template file
	 * 
	 * It check the post type and select then the template file
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $default_template Old template path
	 * @return string New or old template path
	 */
    public function template_include($default_template){
        global $post;
		
        return $default_template;
        if ($post->post_type == 'subpoule') {
            if (file_exists(Poule_Tournament::$template_path . "podium/podium-post.php")) {
                return Poule_Tournament::$template_path . "podium/podium-post.php";
            }else{
                return POULE_PATH . 'template/podium/podium-post.php';
            }
            return Poule_Tournament::$template_path . '/single-event.php';
        }
        return $default_template;
    }
}