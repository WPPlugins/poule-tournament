<?php
/**
 * Add url rewrite.
 * 
 * Add a get for url rewite and friendly url's
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
class url_rewrite {
	
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
//        add_action('init', array($this,'rewrite_url'),1);
//        add_filter('query_vars', array($this,'add_poule_vars'),1);
        
//        add_action('init', 'flush_rewrite_rules');

//        add_action('generate_rewrite_rules', array($this, 'add_rewrite_rules'));
//        add_filter('query_vars', array($this, 'query_vars'));
        
    }
    
	/**
	 * Add the phase variable
	 * 
	 * Add the phae variable to the vars
	 * 
	 * @param array $vars al the $vars
	 * @return array
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function add_poule_vars($vars) {    
//        $vars[] = 'poulephase';
//        return $vars;
    }

	/**
	 * add the rewrite rule
	 * 
	 * Add the rewrite rules for currect/working urls
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function rewrite_url() {
        global $wp;
        $wp->add_query_var( 'poulephase' );

        add_rewrite_rule('official-score/([^/]+)/?$', 'index.php?pagename=official-score&poulephase=$matches[1]', 'top');
        
        add_rewrite_rule('eigen-score/([^/]+)/?$', 'index.php?pagename=eigen-score&poulephase=$matches[1]', 'top');
            
//        global $wp_rewrite;  
//        $new_rules = array(  
//          "poule/official-score/([^/]+)/?" => "index.php?pagename=official-score&poulephase=".$wp_rewrite->preg_index(1)  
//        );  
//        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
//        
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
new url_rewrite();