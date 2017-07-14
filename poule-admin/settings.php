<?php 
/**
 * File with all the admin page matches functions
 * 
 * Contains all the vieuw functions and the system function for validation and tables
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The class with all the functions
 * 
 * LONG
 * 
 * @since version 1
 * @version 2.2
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class poule_settings{
	
	/**
	 * Template path
	 * 
	 * Path to the template in the admin directory
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
	private $options;
	
	/**
	 * Template path
	 * 
	 * Path to the template in the admin directory
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    private $setting;
    
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
	public function __construct(){
		add_action( 'admin_init', array( $this, 'page_init' ) );
		
	}
	
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function create_admin_page(){
		$this->options = get_option( 'poule_settings',array() );
		
		echo '<div class="wrap">';
		screen_icon();
		echo '<h2>My Settings</h2>';
		$this->create_pagination();
		echo '<form method="post" action="options.php">';
        
        settings_fields('poule_settings');
//        settings_fields('poule_pages');
		do_settings_sections( 'poule-setting-admin' );
        
        echo '<table class="form-table">';
        
        $tab = (isset($_GET['tab'])) ? $_GET['tab'] : "General";
        foreach($this->get_settings($tab) as $id => $setting){
			if($id != 'before'){
				if(method_exists($this, 'field_'.$setting['type'])){
					$function = 'field_'.$setting['type'];
					$this->$function($setting);
				}
			}
        } 
        echo '</table>';
        
        echo '<input type="hidden" hidden="hidden" name="tab" value="'.$tab.'"/>';
        
		submit_button();
		echo '</form>';
		echo '</div>';
	}
	
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $tab tab name
	 * @return array the correct tab settings
	 */
	public function get_settings($tab = ""){
        
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ); 
        
        $select = get_pages($args);
        
		$podiumproviders = apply_filters('poule_podium_provider', array('Manual'));
		if(is_object($podiumproviders)){
			$podiumproviders = (array)$podiumproviders;
		}
		
		$settings = array(
			'General' => array(
				'podium_provider' => array(
					'name' => 'podium data',
					'text' => __('poduim data', 'poule-tournament'),
					'type' => 'select',
					'select' => $podiumproviders,
					'sort' => 'other'
				)
			),
			'Email' => array(
				'email_from_email' => array(
					'name' => 'email_from_email',
					'text' => __('Email from:', 'poule-tournament', 'poule-tournament'),
					'type' => 'email',
                    'default' => '{admin_email}'
				),
				'email_from_name' => array(
					'name' => 'email_from_name',
					'text' => __('Email from name:', 'poule-tournament', 'poule-tournament'),
					'type' => 'text',
                    'default' => '{website_title}'
				), 
				'invitation-join_title' => array(
					'name' => 'invitation-join_title',
					'text' => __('Email title for invitation a user for a subpoule:', 'poule-tournament'),
					'type' => 'text',
                    'default' => 'new invitation'
				),
				'invitation-join_content' => array(
					'name' => 'invitation-join_title',
					'text' => __('Invitation subpoule email content', 'poule-tournament'),
					'type' => 'textarea',
					'default' => ''
				),
				'invitation-user_title' => array(
					'name' => 'invitation-user_title',
					'text' => __('Email title for invitation a user for a account:', 'poule-tournament'),
					'type' => 'text',
                    'default' => 'new invitation'
				),
				'invitation-user_content' => array(
					'name' => 'invitation-user_content',
					'text' => __('Invitation user email content', 'poule-tournament'),
					'type' => 'textarea',
					'default' => ''
				),
			),
            'Sub-poules' => array(
				'subpoules' => array(
					'name' => 'subpoules',
					'text' => __('Activate Sub-poules', 'poule-tournament'),
					'type' => 'checkbox'
				),
                'subpoule_mail' => array(
					'name' => 'subpoule_mail_from',
					'text' => __('Subpoule email from'),
					'type' => 'email'
				),
                'subpoules_new_users' => array(
					'name' => 'subpoules_new_users',
					'text' => __('Invite people to also start', 'poule-tournament'),
					'type' => 'checkbox'
				),
				'subpoule_default' => array(
					'name' => 'subpoule_default',
					'text' => __('Add subpoule default poule', 'poule-tournament'),
					'type' => 'checkbox'
				),
			),
			'My-Prediction' => array(
				'message_after_save' => array(
					'name' => 'message_after_save',
					'text' => __('Thank you message', 'poule-tournament'),
					'type' => 'textarea'
				),'redirect' => array(
					'name' => 'redirect',
					'text' => __('Redirect to the thank you page', 'poule-tournament'),
					'type' => 'select',
					'sort' => 'pages',
                    'select' => $select,
				),'score_time' => array(
					'name' => 'score_time',
					'text' => __('Time that the users have to change their predicition', 'poule-tournament'),
					'type' => 'number'
				),'set_amount' => array(
					'name' => 'set_amount',
					'text' => __('Users can 1 times set their prediction', 'poule-tournament'),
					'type' => 'select',
					'sort' => 'other',
					'select' => array(
						0 => 'default',
						1 => 'to start first match',
						2 => '1 time'
					),
				)
			),'Pages' => array(
				'official score' => array(
					'name' => 'page_official_score',
					'text' => __('Official score', 'poule-tournament'),
					'type' => 'select',
					'sort' => 'pages',
                    'select' => $select,
                    //'option' => 'poule_pages',
				),'My prediction' => array(
					'name' => 'page_own_score',
					'text' => __('My prediction', 'poule-tournament'),
					'type' => 'select',
					'sort' => 'pages',
                    'select' => $select,
                    //'option' => 'poule_pages',
				),'podium' => array(
					'name' => 'page_podium',
					'text' => __('Podium', 'poule-tournament'),
					'type' => 'select',
					'sort' => 'pages',
                    'select' => $select,
                    //'option' => 'poule_pages',
				),'subpoules' => array(
					'name' => 'page_subpoules',
					'text' => __('Subpoules', 'poule-tournament'),
					'type' => 'select',
					'sort' => 'pages',
                    'select' => $select,
                    //'option' => 'poule_pages',
				),
                
			),'Reset' => array(
				'phases_delete' => array(
					'name' => 'phases',
					'text' => __('Delete the phases', 'poule-tournament'),
					'value' => __('Delete'),
					'type' => 'button',
					'id' => 'reset_delete_phases'
				),
				'phases_add_wk' => array(
					'name' => 'phases_add_wk',
					'text' => __('Add wk phases', 'poule-tournament'),
					'value' => __('Add phases'),
					'type' => 'button',
					'id' => 'reset_add_wk_phases'
				),
				'phases_add_ek' => array(
					'name' => 'phases_add_ek',
					'text' => __('Add ek phases', 'poule-tournament'),
					'value' => __('Add phases'),
					'type' => 'button',
					'id' => 'reset_add_ek_phases'
				),
				'phases_delete_official_result' => array(
					'name' => 'phases_delete_official_result',
					'text' => __('Delete official result', 'poule-tournament'),
					'value' => __('Delete'),
					'type' => 'button',
					'id' => 'reset_delete_official_result'
				),
				'phases_delete_my_prediction' => array(
					'name' => 'phases_delete_my_prediction',
					'text' => __('Delete user predictions', 'poule-tournament'),
					'value' => __('Delete'),
					'type' => 'button',
					'id' => 'reset_delete_user_prediction'
				),
			)
		);
		if($tab == ''){
            return $settings;
        }
		return $settings[$tab];
	}
	
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function create_pagination(){
		echo '<h3 class="nav-tab-wrapper">';
        $settings = $this->get_settings();
		$current = (isset($_GET['tab'])) ? $_GET['tab'] : 'General';
		foreach($settings as $key => $settings){;
            $link = 'edit.php?';
            $get = $_GET;
            unset($get['tab']);
            foreach($get as $id => $value){
                $link .= $id . '=' . $value . '&';
            }
            $link .= 'tab=' . $key;
            
			$active = ($current == $key)?"nav-tab-active":"";
			
			echo '<a class="nav-tab '.$active.'" href="'.$link.'">'. __($key, 'poule-tournament').'</a>';
		}
		echo '</h3>';
	}
	
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function page_init()
    {        
        register_setting(
            'poule_settings', // Option group
            'poule_settings', // Option name\
            array( $this, 'sanitize_settings' ) // Sanitize
        );
//
//        add_settings_section(
//            'poule_section', // ID
//            'Poule tournament settings', // Title
//            array( $this, 'print_section_info' ), // Callback
//            'my-setting-admin' // Page
//        );          
    }
	
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $input Old input
	 */
    public function sanitize_settings( $input )
    {
        if(is_array(get_option("poule_settings",array()))){
            $new_input = get_option("poule_settings",array());
        }else{
            $new_input = array();
        }
        
        $tab = $_POST['tab'];
		
        foreach($this->get_settings($tab) as $id => $setting){
            if(isset( $input[$setting['name']] ) ){
                $new_input[$setting['name']] = stripslashes($input[$setting['name']]);
            }else{
                $new_input[$setting['name']] = '';
            }
        }
        return $new_input;
    }
    
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function print_section_info()
    {
        echo 'Enter your settings below:';
    }

	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $setting The settings options and variables
	 */
    private function field_text($setting){
        $default = (isset($setting['default'])) ? $setting['default'] : "";
        $value = isset( $this->options[$setting['name']] ) ? esc_attr( $this->options[$setting['name']]) : $default;
		
        echo '<tr valign="top">';
        echo '<th scope="row">'.$setting['text'].'</th>';
        echo '<td><input type="text" name="poule_settings['.$setting['name'].']" value="' .$value. '"/></td>';
        echo '</tr>';
    }
	
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $setting The settings options and variables
	 */
    private function field_checkbox($setting){
        $check = isset( $this->options[$setting['name']] ) ? checked($this->options[$setting['name']], 1, FALSE ):"";
        echo '<tr valign="top">';
        echo '<th scope="row">'.$setting['text'].'</th>';
        echo '<td><input type="checkbox" name="poule_settings['.$setting['name'].']" value="1" '.$check.'/></td>';
        echo '</tr>';
    }
    
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $setting The settings options and variables
	 */
    private function field_number($setting){
        $value = isset( $this->options[$setting['name']] ) ? esc_attr( $this->options[$setting['name']]) : '';
        echo '<tr valign="top">';
        echo '<th scope="row">'.$setting['text'].'</th>';
        echo '<td><input type="number" min="0" name="poule_settings['.$setting['name'].']" value="' .$value. '"/></td>';
        echo '</tr>';
    }
    
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $setting The settings options and variables
	 */
    private function field_email($setting){
        $default = (isset($setting['default'])) ? $setting['default'] : "";
        $value = isset( $this->options[$setting['name']] ) ? esc_attr( $this->options[$setting['name']]) : $default;
        
        echo '<tr valign="top">';
        echo '<th scope="row">'.$setting['text'].'</th>';
        echo '<td><input type="email" min="0" name="poule_settings['.$setting['name'].']" value="' .$value. '"/></td>';
        echo '</tr>';
    }
	
	/**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $setting The settings options and variables
	 */
    private function field_textarea($setting){
        $default = (isset($setting['default'])) ? $setting['default'] : "";
        $value = isset( $this->options[$setting['name']] ) ? esc_attr( $this->options[$setting['name']]) : $default;
        
        echo '<tr valign="top">';
        echo '<th scope="row">'.$setting['text'].'</th>';
		echo '<td>';
		wp_editor( $value, 'poule_settings['.$setting['name'].']', $settings = array() );
		echo'</td>';
        echo '</tr>';
    }
    
    /**
	 * function to show page content
	 * 
	 * Function thast is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $setting The settings options and variables
	 */
    private function field_select($setting){
        $value = isset( $this->options[$setting['name']] ) ? esc_attr( $this->options[$setting['name']]) : '';
		
        if(isset($setting['option'])){
            $name = $setting['option'];
        }else{
            $name = 'poule_settings';
        }
        
        echo '<tr valign="top">';
        echo '<th scope="row">'.$setting['text'].'</th>';
        echo '<td><select name="poule_settings['.$setting['name'].']">';
        echo '<option></option>';
		if($setting['sort'] == "pages"){
			foreach($setting['select'] as $id => $page){
				echo ($page->ID);
				$select = ($value == $page->ID) ? 'selected="selected"' : "";
				echo '<option value="'.$page->ID.'" '.$select.' >' . $page->post_title . '</option>';
			}
		}else{
			
			foreach($setting['select'] as $id => $value){
				$select = ($value == $id) ? 'selected="selected"' : "";
				echo '<option value="'.$id.'" '.$select.' >' . $value . '</option>';
			}
		}
        
        echo '</select></td>';
        echo '</tr>';
    }
	
	public function field_button($setting){
        echo '<tr valign="top">';
        echo '<th scope="row">'.$setting['text'].'</th>';
		
        echo '<td>' . '<input type="button" id="'.$setting['id'].'" value="'.$setting['value'].'"/>';
        
        echo '</td>';
        echo '</tr>';
	}
}

if(is_admin()){
	/**
	 * initialization of the main class
	 * 
	 * initialize of the class for the plugin
	 * 
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 */
	new poule_settings();
}