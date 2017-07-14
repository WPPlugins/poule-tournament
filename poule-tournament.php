<?php
/**
 * Wordpress plugin to create your online poule tournament system
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */
/*
Plugin Name: Poule tournament
Plugin URI: http://stefandebruin.eu/plugins/poule-tournament/
Description: Wordpress plugin to create your online poule tournament system
Version: 2.2.0.5.1
Author: Stefan de Bruin
Author URI: http://stefandebruin.eu
*/

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

/**
 * The mail class of the plugin
 * 
 * Open all the files and call all the required function to show content on the page
 * 
 * @since version 1
 * @version 2.2
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class Poule_Tournament {
	
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's the folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @static
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public static $template_path;
    
	/**
	 * constructor
	 * 
	 * The constructor of the main class for the plugin. Its load all the files and add the main hooks and filters for wordpress
	 * 
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 1
	 * @package poule_tournament
	 * @version 2.2
	 * @access public
	 */
    public function __construct(){
        define('POULE_PATH', plugin_dir_path(__FILE__));
        
		load_plugin_textdomain('poule-tournament', null, basename(dirname(__FILE__)) . "/languages/");
		
		$this->open_files();
		
		add_action('admin_init', array($this,'poule_init'));
		
		add_action('admin_menu', array($this, 'add_menu_pages'));
		add_action( 'wp_enqueue_scripts', array($this,'add_javascript'),99 );
        add_action( 'wp_enqueue_scripts', array($this,'add_styles'),99 );
		add_action( 'admin_enqueue_scripts', array($this,'add_javascript'),99 );
        add_action( 'admin_enqueue_scripts', array($this,'add_styles'),99 );
        
        self::$template_path = get_template_directory() . "/poule-tournament/";
    }
    
	public function poule_init(){
		$plugin_data = get_plugin_data( __FILE__ );
		
		if(get_option("poule_version", null) != null){
			if(get_option("poule_version", 1) != $plugin_data['Version']){
				$this->activation();
			}
		}else{
			$this->activation();
		}
	}
	
    /**
	 * add the subpages to custom posttype country
	 * 
	 * Add all the menu and subpages for the admin accounts 
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * @todo 'import-export' toevoegen en maken
	 */
    public function add_menu_pages(){
    	$pages = array('Matches', 'Official-Result');
		$pagenames = array(__('Matches','poule-tournament'), __('Official Result','poule-tournament'),__('Import-Export','poule-tournament'));
		
		foreach($pages as $key =>$page){
			include_once(POULE_PATH . 'poule-admin/' . $page.'.php');
			$classname = strtolower(str_replace('-','_',$page)); //kleine letters
			
			$class = new $classname();
			add_submenu_page( 'edit.php?post_type=country', $pagenames[$key], $pagenames[$key], 'manage_options', $classname, array($class, 'init') );
		}
		
		$settings = new poule_settings();
		add_submenu_page( 'edit.php?post_type=country', __('Poule settings', 'poule-tournament'), __('Poule settings','poule-tournament'), 'manage_options', 'poulesettings', array( $settings, 'create_admin_page' ));
    }
    
    /**
	 * open the files
	 * 
	 * Open the required files for the plugin
	 * 
	 * @access private
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 1
	 */
    private function open_files(){
        require_once(POULE_PATH . 'classes/post-types.php');
        require_once(POULE_PATH . 'classes/add-meta-boxes.php');
        require_once(POULE_PATH . 'classes/url-rewrite.php');
        require_once(POULE_PATH . 'classes/form-validation.php');
        require_once(POULE_PATH . 'classes/template.php');
        require_once(POULE_PATH . 'classes/poule_subpoule.php');
        require_once(POULE_PATH . 'classes/dashboard.php');
        require_once(POULE_PATH . 'classes/email.php');
		require_once(POULE_PATH . 'classes/profile_option.php');
		
        require_once(POULE_PATH . 'includes/shortcodes.php');
        
        require_once(POULE_PATH . 'hooks/functions.php');
        require_once(POULE_PATH . 'hooks/system-functions.php');
		require_once(POULE_PATH . 'hooks/poule-ajax.php');
		require_once(POULE_PATH . 'poule-admin/settings.php');
    }
    
    /**
	 * add all the js files
	 * 
	 * Add all the files for the plugin. Admin css files only for wp-admin pages and frondent files 
	 * only add frontend
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * 
	 */
    public function add_javascript(){
    	if(is_admin()){
        	wp_enqueue_script( 'poule-admin', plugins_url( '/assets/admin/poule-tournament-admin.js' , __FILE__ ), array( 'jquery' ), TRUE);
            
            wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
			
			wp_enqueue_script('poule-official-result', plugins_url( '/assets/admin/official-result.js', __FILE__ ), array( 'jquery' ), TRUE);
			
			wp_enqueue_script( 'jquery-ui-autocomplete');
			
			wp_enqueue_script('autocomplete-poule', plugins_url( '/assets/admin/subpoules.js' , __FILE__ ), array( 'jquery', 'jquery-ui-autocomplete'), TRUE);
			$phases = poule_get_phases();
			$Phase_Penalties = array();
			foreach(poule_get_phases() as $phase){
				$meta = Poule_Get_Phase_Meta($phase->term_id, 'penalties');
				if(is_object($meta) && $meta->penalties != null && $meta->penalties != '0'){
					$Phase_Penalties[] = array('slug' => $phase->slug);
				}
			}
			$phase = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
			$javascript = array('penalties' => $Phase_Penalties, 'current_phase' => $phase);
			
            wp_localize_script( 'poule-official-result', 'poule', $javascript);
		}else{
			wp_enqueue_script('poule-subpoules',plugins_url( '/assets/frontend/subpoules.js' , __FILE__ ),array( 'jquery' ),TRUE);
            
            wp_localize_script( 'poule-subpoules', 'Ajax', array( 'url' => admin_url( 'admin-ajax.php' )));
            
			wp_enqueue_script('poule-tournament',plugins_url( '/assets/frontend/poule-tournament.js' , __FILE__ ),array( 'jquery' ),TRUE);
			
			$Phase_Penalties = array();
//			var_dump(poule_get_phases());
			$phases = poule_get_phases();
			foreach($phases as $phase){
				
				$meta = Poule_Get_Phase_Meta($phase->term_id, 'penalties');
				//var_dump($meta->penalties);
				if(is_object($meta) && $meta->penalties != null && $meta->penalties != '0'){
					$Phase_Penalties[] = array('slug' => $phase->slug);
				}
			}
			
			$phase = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
			
			$javascript = array('ajax_url' => admin_url( 'admin-ajax.php' ),'penalties' => $Phase_Penalties,'current_phase' => $phase);
			
            wp_localize_script( 'poule-tournament', 'poule', $javascript);
            
			wp_enqueue_script('poule-my-prediction',plugins_url( '/assets/frontend/my-prediction.js' , __FILE__ ),array( 'jquery' ),TRUE);
			
			wp_enqueue_script( 'jquery-ui-sortable' );
			
            wp_enqueue_script('poule-bootstrap-modalmanagerjs',plugins_url( '/assets/frontend/js/bootstrap-modalmanager.js' , __FILE__ ),array( 'jquery' ),TRUE);
            
            wp_enqueue_script('poule-bootstrap-modaljs',plugins_url( '/assets/frontend/js/bootstrap-modal.js' , __FILE__ ),array( 'jquery', 'poule-bootstrap-modalmanagerjs' ),TRUE);
		}
    }
    
	/**
	 * Add all the css files
	 * 
	 * Add all the css files for the plugin. Admin css files only for wp-admin pages and frondent files 
	 * only add frontend
	 * 
	 * @access plubic
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 */
    public function add_styles(){
        if(is_admin()){
            wp_enqueue_style('form-style-admin',plugins_url( '/assets/admin/css/form-style.css' , __FILE__ ));
		}else{
            wp_enqueue_style('poule-style',plugins_url( '/assets/frontend/css/style.css' , __FILE__ ));
            
//			wp_enqueue_style(
//				'poule-bootstrap-modalcss',
//				plugins_url( '/assets/frontend/css/bootstrap-modal.css' , __FILE__ ),
//                array()
//			);
//            
            wp_enqueue_style('poule-bootstrap-modal-css-patch',plugins_url( '/assets/frontend/css/bootstrap-modal-bs3patch.css' , __FILE__ ),array('poule-bootstrap-modalcss'));
		}
    }
	
	/**
	 * Function for the activation of the plugin
	 * 
	 * Add the usermeta to the al exists users
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 */
	public function activation(){
		require_once(POULE_PATH . 'classes/post-types.php');
		$taxonomy = new poule_post_types();
		$taxonomy->add_custom_taxonomy();
		
		$args = array('role' => 'subsciper');
		foreach(get_users( $args ) as $user){
			$check = get_user_meta($user->ID, '_poule_order_subpoules', TRUE);
			if($check != null ){
				add_user_meta($user->ID, "_poule_order_subpoules", serialize(array()), TRUE);
			}
			
			$check = get_user_meta($user->ID, '_poule_podium', TRUE);
			if($check != null ){
				add_user_meta($user->ID, "_poule_podium", '1', TRUE);
			}
		}
		
		$plugin_data = get_plugin_data( __FILE__ );
		
		if(get_option("poule_version", null) == null){
			//install
			require_once(POULE_PATH . 'update/install.php');
			new poule_install(1);
		}else if(get_option("poule_version", null) < $plugin_data['Version']){
			//update
			require_once(POULE_PATH . 'update/update.php');
			new poule_update(get_option("poule_version"));
		}
		
		update_option("poule_version", $plugin_data['Version']);
	}
}

/**
 * initialization of the main class
 * 
 * initialize of the class for the plugin
 * 
 * @see class Poule_Tournament
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @see Poule_Tournament_2
 * @package poule_tournament
 * @since version 1
 * @version 2.2
 */
$poule = new Poule_Tournament();

register_activation_hook(__FILE__, array($poule, 'activation'));

/**
 * Create global variable
 * 
 * Create the gloabel variable for the form valivaidations
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource classes/form-valividation.php
 * @see classes/form-valividation.php
 * @package poule_tournament
 * @since version 2.2
 * @version 1
 */
$poule_validation = new form_validation();