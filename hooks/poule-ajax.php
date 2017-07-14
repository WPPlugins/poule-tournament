<?php 
/**
 * File with all the ajax functions
 * 
 * Contains all the functions
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Functions class
 * 
 * Class with all the ajax fucntions
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class poule_ajax{
	
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
	public function __construct(){
		add_action('wp_ajax_poule_delete_group',array($this,'poule_delete_group'));
        
        add_action('wp_ajax_poule_subpoule_add',array($this,'poule_subpoule_add'));
        add_action('wp_ajax_nopriv_poule_subpoule_add',array($this,'poule_subpoule_add'));
        
        add_action('wp_ajax_poule_subpoule_edit',array($this,'poule_subpoule_edit'));
        add_action('wp_ajax_nopriv_poule_subpoule_edit',array($this,'poule_subpoule_edit'));
        
        add_action('wp_ajax_poule_add_user',array($this,'poule_add_user'));
        add_action('wp_ajax_nopriv_poule_add_user',array($this,'poule_add_user'));
        
        add_action('wp_ajax_poule_add_user_to_subpoule',array($this,'poule_add_user_to_subpoule'));
        add_action('wp_ajax_nopriv_poule_add_user_to_subpoule',array($this,'poule_add_user_to_subpoule'));
                
        add_action('wp_ajax_nopriv_poule_get_subpoule_info',array($this,'poule_get_subpoule_info'));
        add_action('wp_ajax_poule_get_subpoule_info',array($this,'poule_get_subpoule_info'));
        
        add_action('wp_ajax_poule_subpoule_invitations', array($this, 'poule_subpoule_invitations'));
        add_action('wp_ajax_nonpriv_poule_subpoule_invitations', array($this, 'poule_subpoule_invitations'));
		
		add_action('wp_ajax_poule_subpoule_reorder', array($this, 'poule_subpoule_reorder'));
        add_action('wp_ajax_nonpriv_poule_subpoule_reorder', array($this, 'poule_subpoule_reorder'));
		
		add_action('wp_ajax_poule_subpoule_delete', array($this, 'poule_subpoule_delete'));
        add_action('wp_ajax_nonpriv_poule_subpoule_delete', array($this, 'poule_subpoule_delete'));
		
		//poule_subpoule_delete
		add_action('wp_ajax_poule_reset_phase_delete', array($this, 'delete_phases'));
		add_action('poule_reset_add_phases_wk', array($this, 'add_wk_phases'));
		add_action('poule_reset_add_phases_ek', array($this, 'add_ek_phases'));
		add_action('poule_reset_delete_official_result', array($this, 'delete_official_rsult'));
	}
	
	/**
	 * ajax function unscripe from subpoule
	 * 
	 * function is called by a ajax request. it's remoce the subpoule from the list of subpoule for that user only
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
	public function poule_subpoule_delete(){
		global $wpdb, $poule_validation;
		$return = array('code' => 0);
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$poule_validation->validate_input("pouleid",array('pouleid' => array('required' => TRUE, 'type' => 'int')),$this);
			$poule_validation->validate_input("type",array('type' => array('required' => TRUE, 'type' => 'string')),$this);
			$poule_validation->validate_input("hash",array('hash' => array('required' => TRUE, 'type' => 'string')),$this);
			if($poule_validation->counterrors['pouleid'] == 0 && $poule_validation->counterrors['type'] == 0 && $poule_validation->counterrors['hash'] == 0){
				$hash = str_replace(md5($poule_validation->input['type']),"",$poule_validation->input['hash']);
				
				$info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE user_id='%s' AND token='%s'",  get_current_user_id(), $hash));
				
				if($info != null){
					$return['code'] = 1;
					$wpdb->delete( $wpdb->prefix.'poule_subpoule_users', array('id' => $info->id));
				}
			}
		}
		echo json_encode($return);
		die();
	}
	
	/**
	 * Reorder the subpoule list
	 * 
	 * the user can reorder the subpoule list. for the dropdown to select a specific poule
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function poule_subpoule_reorder(){
		global $wpdb, $poule_validation;
		$return = array('code' => 0);
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$poule_validation->validate_input("order",array('order' => array('required' => TRUE, 'type' => 'string')),$this);
			
			if($poule_validation->counterrors['order'] == 0){
				$poules = array();
				foreach(explode(',', $poule_validation->input['order']) as $poule){
					if(get_post($poule) != null){
						$poules[] = $poule;
					}
				}
				
				update_user_meta(get_current_user_id(), '_poule_order_subpoules', serialize($poules));
				$return['code'] = 1;
			}else{
				$return['code'] = $poule_validation;
			}
			
		}else{
			$return['code'] = 15;
		}
		
		echo json_encode($return);
		die();
	}
	
	/**
	 * ajax function invate a user to create an account
	 * 
	 * function is called by a ajax request. send the user a email to create an account
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
    public function poule_add_user(){
        global $wpdb, $poule_validation;
		
        $return = array();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $return['code'] = 1;
            
            $poule_validation->validate_input("poule_id",array('poule_id' => array('required' => TRUE, 'type' => 'int')),$this);
            $input_poule_id = $poule_validation->input['poule_id'];
            $count_error = $poule_validation->counterrors;

            $poule_validation->reset();
			
            $poule_info = ($count_error != 0) ? get_post($input_poule_id) : null;
            
			if($poule_info != null){
                $poule_validation->validate_input("full_name",array('full_name' => array('required' => TRUE, 'type' => 'string')),$this);
//                $errors_user = $poule_validation->errors;
//                $input_user = $poule_validation->input['full_name'];
//                $count_error_user = $poule_validation->counterrors;

//                $poule_validation->reset();
                
                $poule_validation->validate_input("user_email",array('user_email' => array('required' => TRUE, 'type' => 'email')),$this);
//                $errors_email = $poule_validation->errors;
//                $input_email = $poule_validation->input['user_email'];
//                $count_error_email = $poule_validation->counterrors;
                
//                $poule_validation->reset();
                
                $poule_validation->validate_input("email_message",array('email_message' => array('required' => TRUE, 'type' => 'string')),$this);
//                $errors_description = $poule_validation->errors;
//                $input_description = $poule_validation->input['email_message'];
//                $count_error_description = $poule_validation->counterrors;
                
                if($poule_validation->counterrors['full_name'] == 0 && $poule_validation->counterrors['user_email'] == 0 && $poule_validation->counterrors['email_message'] == 0){
                    $return['code'] = 2;
					
					$wpdb->insert(
						$wpdb->prefix.'poule_subpoule_accounts',
						array(
							'poule_id' => $input_poule_id, 
							'email' => $poule_validation->input['user_email']
						)
					);
                    
					$message = array(
                        'registration_link' => site_url('wp-login.php?action=register'),
                        'name' => esc_html($poule_validation->input['full_name']),
                        'message_text' => ($poule_validation->input['email_message']),
                    );
					
					$email = new email();
					$email->to = $poule_validation->input['user_email'];
					$email->subject = "Uitnodiging";
					$email->from = 'From: name <'.get_option( 'admin_email' ).'>' . "\r\n";
					
					$email->content('invitation-user', $message);
					$check = $email->send();
					
					$return['email'] = $check;
					
                }else{
					$return['code'] = 0;
				}
            }
        }else{
            $return['code'] = 0;
        }
        
        echo json_encode($return);
        die();
    }
   
    /**
	 * ajax function get the information about a subpoule
	 * 
	 * function is called by a ajax request. return the information about a subpoule for edit. also all the users that are connected to the subpoule
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
    public function poule_get_subpoule_info(){
        global $wpdb, $poule_validation;
        
		$poule_validation->validate_input("pouleid",array('pouleid' => array('required' => TRUE, 'type' => 'int')),$this);
		$input_poule_id = $poule_validation->input['pouleid'];
		$count_error = $poule_validation->counterrors;
		$poule_validation->reset();

		$poule_info = ($count_error != 0) ? get_post($input_poule_id) : null;

		if($poule_info != null){
			$return = array('code' => 1,'name' => $poule_info->post_title, 'description' => $poule_info->post_content, 'poule_id' => $input_poule_id);

			$users = array();
			foreach($wpdb->get_results($wpdb->prepare("SELECT user_id, status FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%d'",$poule_info->ID)) as $user){
				$userinfo = get_user_by("id", $user->user_id);
				switch ($user->status) {
					case 0:
						$status = __('Open', 'poule-tournament');
						break;
					case 1:
						$status = __('Accepted', 'poule-tournament');
						break;
					case 2:
						$status = __('Remove', 'poule-tournament');
						break;
					default:
						break;
				}
				$users[] = array('name' => $userinfo->first_name . ' ' . $userinfo->last_name, 'status' => $status);
			}

			$return['users'] = $users;
		}else{
			$return = array('code' => 0, 'post' => $_POST, 'poule' => $poule_info);
		}
			
        echo json_encode($return);
        die();
    }
    
    /**
	 * ajax function connect user to subpoule
	 * 
	 * function is called by a ajax request. its connect a user to a aubpoule. after a check of the user exist.and send the invitation email.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
    public function poule_add_user_to_subpoule(){
        global $wpdb, $poule_validation;
        
        $message = array();
        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            
            $poule_validation->validate_input("user",array('user' => array('required' => TRUE, 'type' => 'string')),$this);
            $poule_validation->validate_input("pouleid",array('pouleid' => array('required' => TRUE, 'type' => 'int')),$this);
            
            if($poule_validation->counterrors['user'] == 0 && $poule_validation->counterrors['pouleid'] == 0){
            	
            	if (filter_var($poule_validation->input['user'], FILTER_VALIDATE_EMAIL)) {
                	$user = get_user_by("email", $poule_validation->input['user']);
            	}else{
                	$user = get_user_by("slug", $poule_validation->input['user']);
                	if($user === FALSE){
                    	$user = get_user_by("login", $poule_validation->input['user']);
                	}
            	}
            	
            	if($user !== FALSE){
                	$token = poule_create_token();
                	$wpdb->insert(
						$wpdb->prefix.'poule_subpoule_users',
						array(
							'poule_id' => $poule_validation->input['pouleid'], 
							'user_id' => $user->ID, 
							'token' => $token, 
							'invitation_time' => current_time( 'mysql' ),
							'update_time' => current_time( 'mysql' )
						)
					);
					
                	$options = "sub-poules";
					
					$poule_info = get_post($poule_validation->input['pouleid']);
					
					$name = $user->first_name . ' ' . $user->last_name;
					
					if(trim($name) == ""){
						$name = $user->display_name;
					}
					
					$messagetext = array(
						'full_name' => $name,
						'subpoule_title' => $poule_info->post_title,
						'subpoule_description' => $poule_info->post_content,
						'accept_link' => site_url($options .'/?from=email&type=accept&token='.$token.md5('accept').'&poule_id='.$poule_validation->input['pouleid']),
						'delete_link' => site_url($options .'/?from=email&type=delete&token='.$token.md5('delete').'&poule_id='.$poule_validation->input['pouleid'])
					);

					$email = new email();
					$email->to = array('stefan.de.bruin@hotmail.com','stefandebruinitunes@gmail.com');
					$email->subject = __('Invitation','poule-tournament');
					$email->from = 'From: name <'.get_option( 'admin_email' ).'>' . "\r\n";

					$email->content('invitation-join', $messagetext);
					$check = $email->send();
					wp_die($check);
					
					
                	$message = array('code' => 1, 'user' => $user->first_name . ' ' . $user->last_name, 'status' => __('Open', 'poule-tournament'));
            	}else{
                	$message = array('code' => 2, 'message' => __('User do not exist', 'poule-tournament'));
            	}
            	
            }
        }else{
            $message['code'] = 0;
        }
        
        echo json_encode($message);
        die();
    }
    
    /**
	 * check if user exist
	 * 
	 * check if the user exist by input
	 * 
	 * @access private
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $input the input value
	 * @param string $type the type to check
	 * @return array with the user data or 0
	 */
    private function check_user_exist($input,$type){
        $user = get_user_by($type, $input);
        if($user === FALSE){
            return array('code' => 0);
        }
        return array('code' => 1, 'user' => $user->first_name . ' ' . $user->last_name, 'id' => $user->ID, 'status' => __('Open', 'poule-tournament'));
    }
    
    /**
	 * ajax function send a invitation for a subpoule
	 * 
	 * function is called by a ajax request.check the input ans save it into the database also send a email with the invitation.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
	public function poule_subpoule_invitations(){
		global $wpdb, $poule_validation;
		$user_info = wp_get_current_user();
		
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
		
        	$poule_validation->validate_input("key",array('key' => array('required' => TRUE, 'type' => 'string')),$this);
        	$poule_validation->validate_input("type",array('type' => array('required' => TRUE, 'type' => 'string')),$this);
        	$poule_validation->validate_input("pouleid",array('pouleid' => array('required' => TRUE, 'type' => 'int')),$this);
        
    		if($poule_validation->counterrors['key'] == 0 || $poule_validation->counterrors['type'] == 0 || $poule_validation->counterrors['pouleid'] == 0){
    		
				$token = $poule_validation->input['key'];
		
				$type = md5($poule_validation->input['type']);
		
				$token = str_replace($type, "", $token);
		
				$subpoule = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%d' AND user_id='%d' and token='%s' AND status='0'",$poule_validation->input['pouleid'],$user_info->ID,$token));
		
				$return = array('type' => $poule_validation->input['type'],'token' => $token,'user_id' => $user_info->ID, 'deleteurl' => md5('delete').$token, 'deletetitle' => __('Delete', 'poule-tournament'), 'name' => get_post($poule_validation->input['pouleid'])->title);
		
				if($subpoule != null){
					$return['code'] = '1';
					if($poule_validation->input['type'] == "accept"){
						$update = array(
							'token' => poule_create_token(),
							'status' => "1",
						);
					}else{
						$update = array(
							'token' => poule_create_token(),
							'status' => "2"
						);
					}
					$where = array(
						'poule_id' => $poule_validation->input['pouleid'],
						'user_id' => $user_info->ID,
					);
			
					$wpdb->update($wpdb->prefix.'poule_subpoule_users',$update,$where);
				}else{
					$return['code'] = '0';
				}
			}
		
		}else{
			$return['code'] = '0';
		}
		echo json_encode($return);
		die();
	}
	
	/**
	 * ajax function update the eddeted subpoule
	 * 
	 * function is called by a ajax request. save the edited subpoule after a check
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
    public function poule_subpoule_edit(){
        global $wpdb, $poule_validation;
        
        $message = array('code' => 1);
        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
        	
        	$poule_validation->validate_input("name",array('name' => array('required' => TRUE, 'type' => 'string')),$this);
        	$poule_validation->validate_input("description",array('description' => array('required' => TRUE, 'type' => 'string')),$this);
        	$poule_validation->validate_input("poule_id",array('poule_id' => array('required' => TRUE, 'type' => 'int')),$this);
        	
        	if($poule_validation->counterrors['name'] == 1){
        		$message['code'] = 0;
                $message['fields'][] = 'name';
        	}
        	
        	if($poule_validation->counterrors['description'] == 1){
        		$message['code'] = 0;
                $message['fields'][] = 'description';
        	}
        	
            if($poule_validation->counterrors['description'] == 0 && $poule_validation->counterrors['name'] == 0){
                $poule_info = get_post($poule_validation->input['poule_id']);
                
                if($poule_info != null){
                    $update = array();
                    $update['ID'] = $poule_validation->input['poule_id'];
                    $update['post_status'] = "publish";
                    $update['post_title'] = esc_html($poule_validation->input['name']);
                    $update['post_content'] = esc_textarea($poule_validation->input['description']);
                    wp_update_post($update);
					
					$message['code'] = 1;
                }
				
            }
        }
        
        echo json_encode($message);
        die();
    }
    
    /**
	 * ajax function add a subpoule
	 * 
	 * function is called by a ajax request. insert the new subpoule after checking the input data.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
    public function poule_subpoule_add(){
        global $wpdb, $poule_validation;
        ob_clean();
        $message = array();
        //header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
        	
        	$poule_validation->validate_input("name",array('name' => array('required' => TRUE, 'type' => 'string')),$this);
        	$poule_validation->validate_input("description",array('description' => array('required' => TRUE, 'type' => 'string')),$this);
        	//$poule_validation->validate_input("pouleid",array('pouleid' => array('required' => TRUE, 'type' => 'int')),$this);
        	
        	if($poule_validation->counterrors['name'] == 1){
        		$message['code'] = 0;
                $message['fields'][] = 'name';
        	}
        	
        	if($poule_validation->counterrors['description']){
        		$message['code'] = 0;
                $message['fields'][] = 'description';
        	}
        	
            if($poule_validation->counterrors['description'] == 0 && $poule_validation->counterrors['name'] == 0){
                $user_info = wp_get_current_user();
                $args = array(
                    'post_status'           => 'publish', 
                    'post_type'             => 'subpoule',
                    'post_title'            => esc_html($poule_validation->input['name']),
                    'post_content'          => esc_textarea($poule_validation->input['description']),
                    'post_author'           => $user_info->ID,
                    'ping_status'           => 'closed',
                    'post_parent'           => 0,
                    'menu_order'            => 0,
                );
                $poule_id = wp_insert_post($args);
                
                $message['code'] = 1;
                $message['pouleid'] = $poule_id;
                
                $token = poule_create_token();
                $wpdb->insert(
					$wpdb->prefix.'poule_subpoule_users',
					array(
						'poule_id' => $poule_id, 
						'user_id' => get_current_user_id(), 
						'token' => $token, 
						'status' => 1,
						'Invitation_time'=>current_time( 'mysql' )
					)
				);
            }
        }
        
        echo json_encode($message);
        die();
    }
    
	/**
	 * ajax function Delete a group
	 * 
	 * function is called by a ajax request. Its delete a group. Only admin
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
	public function poule_delete_group(){
		global $wpdb, $poule_validation;
		
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$poule_validation->validate_input("phase",array('phase' => array('required' => TRUE, 'type' => 'string')),$this);
       		$poule_validation->validate_input("group",array('group' => array('required' => TRUE, 'type' => 'int')),$this);
			
			if($poule_validation->counterrors['phase'] == 0 && $poule_validation->counterrors['group'] == 0){
        		$phaseinfo = get_term_by('slug', $poule_validation->input['phase'], 'phase');
        	
        		$groupsid = $poule_validation->input['group'];
        	
				$check = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}poule_matches_groups WHERE id='%d' AND phase='%d'", $poule_validation->input['group'], $phaseinfo->term_id));
			
				if($check != null){
					$deleted = $wpdb->delete($wpdb->prefix.'poule_matches_groups',array('id' => $poule_validation->input['group']));
        	    	$wpdb->delete($wpdb->prefix.'poule_matches',array('group_id' => $poule_validation->input['group']));
					$data = array('result' => $deleted);
				}else{
					$data = array('result' => 0);
				}
			}else{
				$data = array('result' => 0);
			}
		}else{
			$data = array('result' => 0);
		}
		
		echo json_encode($data);
		die();
	}
	
	public function delete_phases(){
		foreach(poule_get_phases() as $phase){
			wp_delete_term($phase->term_id, 'phase');
			poule_delete_phase_meta($phase->term_id);
		}
		echo json_encode(array('code' => 1));
	}
	
	public function add_wk_phases(){
		
		$phases = array(
			array(
				'slug' => __('group','poule-tournament'),
				'title' => __('Group','poule-tournament'),
				'groups' => 8,
				'matches' => 6,
				'penalties' => 0
			),
			array(
				'slug' => __('18e-final','poule-tournament'),
				'title' => __('18e-Final','poule-tournament'),
				'groups' => 4,
				'matches' => 2,
				'penalties' => 0
			),
			array(
				'slug' => __('quarterfinals','poule-tournament'),
				'title' => __('Quarterfinals','poule-tournament'),
				'groups' => 4,
				'matches' => 2,
				'penalties' => 0
			),
			array(
				'slug' => __('semifinal','poule-tournament'),
				'title' => __('Semifinal','poule-tournament'),
				'groups' => 2,
				'matches' => 1,
				'penalties' => 0
			),
			array(
				'slug' => __('3rd-4th-place','poule-tournament'),
				'title' => __('3rd 4th Place','poule-tournament'),
				'groups' => 1,
				'matches' => 1,
				'penalties' => 0
			),
			array(
				'slug' => __('final','poule-tournament'),
				'title' => __('Final','poule-tournament'),
				'groups' => 1,
				'matches' => 1,
				'penalties' => 1
			),
		);
		
		foreach($phases as $phase){
			$input = wp_insert_term(
				$phase['title'], // the term 
				'phase', // the taxonomy
				array(
					'description'=> '',
					'slug' => $phase['slug']
				)
			);
			
			Poule_Update_Phase_Meta($input['term_id'], 'groups', $phase['groups']);
			Poule_Update_Phase_Meta($input['term_id'], 'matches_per_group', $phase['matches']);
			Poule_Update_Phase_Meta($input['term_id'], 'penalties', $phase['penalties']);
		}
	}
	
	public function add_ek_phases(){
		$phaseslug = array(
			0 => __('group','poule-tournament'),
			1 => __('quarterfinals','poule-tournament'),
			2 => __('semifinal','poule-tournament'),
			3 => __('3rd-4th-place','poule-tournament'),
			4 => __('final','poule-tournament')
		);
		
		$phases = array(
			0 => __('Group','poule-tournament'),
			1 => __('Quarterfinals','poule-tournament'),
			2 => __('Semifinal','poule-tournament'),
			3 => __('3rd 4th Place','poule-tournament'),
			4 => __('Final','poule-tournament')
		);
		
		$groups = array(4,4,2,1,1);
		$matches = array(6,2,1,1,1);
		$penalties = array(0,1,1,1,1);
		
		$phases__ = array(
			array(
//				'slug' => __('group','poule-tournament'),
//				'title' => __('Group','poule-tournament'),
//				'groups' => 4,
//				'matches' => 6,
//				'penalties' => 0
			),
			array(
//				'slug' => __('quarterfinals','poule-tournament'),
//				'title' => __('Quarterfinals','poule-tournament'),
//				'groups' => 4,
//				'matches' => 2,
//				'penalties' => 1
			),
			array(
//				'slug' => __('semifinal','poule-tournament'),
//				'title' => __('Semifinal','poule-tournament'),
//				'groups' => 2,
//				'matches' => 1,
//				'penalties' => 1
			),
			array(
//				'slug' => __('3rd-4th-place','poule-tournament'),
//				'title' => __('3rd 4th Place','poule-tournament'),
//				'groups' => 1,
//				'matches' => 1,
//				'penalties' => 1
			),
			array(
//				'slug' => __('final','poule-tournament'),
//				'title' => __('Final','poule-tournament'),
//				'groups' => 1,
//				'matches' => 1,
//				'penalties' => 1
			),
		);
		
		foreach($phases as $id => $phase){
			$input = wp_insert_term(
				$phase, // the term 
				'phase', // the taxonomy
				array(
					'description'=> '',
					'slug' => $phaseslug[$id]
				)
			);
			
			Poule_Update_Phase_Meta($input['term_id'], 'groups', $groups[$id]);
			Poule_Update_Phase_Meta($input['term_id'], 'matches_per_group', $matches[$id]);
			Poule_Update_Phase_Meta($input['term_id'], 'penalties', $penalties[$id]);
		}
	}
	
	public function delete_official_rsult(){
		global $wpdb;
		
		$wpdb->delete( $wpdb->prefix.poule_matches_groups );
		$wpdb->delete( $wpdb->prefix.poule_matches );
	}
	
	public function delete_my_prediction(){
		global $wpdb;
		
		$wpdb->delete( $wpdb->prefix.'poule_score' );
	}
}

new poule_ajax();



