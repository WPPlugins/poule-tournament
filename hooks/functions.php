<?php
/**
 * File with all hooks and filters functions
 * 
 * Contains all this functions
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Functions class
 * 
 * Class with all the hooks
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class poule_functions {
    
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
		
        add_action('poule_create_pagination', array($this,'create_pagination'));
        add_action('poule_create_pagination_admin', array($this,'create_pagination'));
        add_action('poule_phase_message', array($this,'phase_message'),10,1);
        add_action('poule_get_phases', array($this,'get_phases'));
        add_action('poule_add_badge',array($this,'add_badge'));
        
        add_filter('poule_get_sub_poules',array($this,'get_sub_poules'));
        
        add_action('poule_before_official_score', array($this,'before_official_score'));
        add_action('poule_before_own_score', array($this,'before_own_score'));
        add_action('poule_before_own_score', array($this,'check_matches_count'));
        add_action('poule_before_own_score', array($this,'count_set_score'));
		
		add_action('poule_before_official_score', array($this,'check_matches_count'));
		
        add_filter('poule_get_matches_official', array($this,'get_matches_official'));
        add_filter('poule_get_matches_own', array($this,'get_matches_own'),10,2);
        add_filter('poule_get_podium', array($this,'get_podium'));
        
        //subpoules
        add_filter('poule_subpoules_own_subpoules', array($this,'subpoules_own'));
        add_filter('poule_subpoules_invitations', array($this,'subpoules_invitations'));
        add_filter('poule_subpoules_subpoules', array($this,'subpoules_subpoules'));
        add_filter('poule_subpoule_invitations_poules', array($this,'subpoule_invitations_poules'));
        
        //email
        add_filter('wp_mail_content_type', array($this,'set_content_type'));
        add_action('poule_email_footer', array($this,'email_footer'));
        add_action('poule_email_header', array($this,'email_header'));
        
        //user registration
        add_action('user_register', array($this,'connect_user_to_subpoule'), 10, 1 );
        add_action('user_register', array($this,'create_account_add_usermeta', 10, 1 ));
		
        add_action('poule_subpoule_url_action',array($this,'subpoule_url_action'));
        
        add_filter('poule_get_subpoules', array($this,'get_subpoules'));
		add_action('poule_before_podium', array($this, 'sub_poules'));
		
		//delete
		add_action( 'before_delete_post', 'delete_a_subpoule' );
		
		add_action('poule_before_own_score', array($this,'thank_you_message'));
		
		add_filter("poule_get_user_score", array($this, "get_user_score"));
		
		//add_filter("poule_get_user_info", array($this,'get_user_info'));
    }
	
	/**
	 * Get a user personal score per phase
	 * 
	 * Get the user score by the url slug. the prediction only
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @return array with the prediction of the user
	 */
	public function get_user_score(){
		global $wpdb;
		
		$scores = array();
		$user = get_user_by( 'slug', $_GET['user'] );
		$phases = poule_get_phases();
		
		$phaseurl = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
		
		foreach($phases as $key => $phase){
			if($phase->slug == $phaseurl){
				$score = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_score WHERE user_id='%s' AND phase='%s'", $user->ID, $phase->term_id));
				
				$scores[$key] = array('name' => $phase->name);
				if($score != null){

					$matches = array();
					foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_matches",ARRAY_A) as $id => $match) {
						$matches[$match['id']] = $match;
					}

					$scores[$key]['points'] = $this->calculate_points(unserialize($score->score),$matches,0);
				}else{
					$scores[$key]['points'] = 0;
				}
			}
		}
		
		return $scores;
	}
	
	/**
	 * Add a thank you message
	 * 
	 * Add the thank you message. Message is a setting.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function thank_you_message(){
		global $poule_validation;
		if($_SERVER['REQUEST_METHOD'] == "POST"){
			if($poule_validation->counterrors['score'] == 0){
				$settings = get_option( 'poule_settings' , array());
				
				$content = array_key_exists('message_after_save', $settings) ? $settings['message_after_save'] : "";
				echo '<div class="alert alert-success">'.apply_filters('the_content',$content).'</div>';
			}
		}
	}
	
	/**
	 * Delete a subpoule
	 * 
	 * Update the ussers that are connect to the subpoule
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global int $post_type String with the post type of the current post
	 * @global database $wpdb Object for sql queries
	 * @param int $postid The post id
	 */
	public function delete_a_subpoule($postid){
		global $post_type, $wpdb;   
		if ( $post_type != 'subpoule' ) return;
		
		$wpdb->update(
			$wpdb->prefix.'poule_subpoule_users',
			array('status' => 4),
			array('poule_id' => $postid)
		);
	}
	
	/**
	 * Add the poule user meta
	 * 
	 * Add the user meta while the users created their account
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param int $user_id The user id
	 */
	public function create_account_add_usermeta( $user_id ) {
		add_user_meta($user_id, "_poule_order_subpoules", serialize(array()), TRUE);
	}
	
	/**
	 * Add message set my prediction
	 * 
	 * Add message to my prediction for the user with that you only enter once time you prediction
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function count_set_score(){
		$settings = get_option("poule_settings",array());
		
		$timeto = 30;
		if(array_key_exists("score_time", $settings)){
			if(is_numeric($settings['score_time'])){
				$timeto = $settings['score_time'];
			}
		}
		
		if(array_key_exists('set_amount',$settings) && $settings['set_amount'] == 0 || !array_key_exists('set_amount',$settings)){
			$message = __('You can change your prediction %s before the match', 'poule-tournament');
		}else if(array_key_exists('set_amount',$settings) && $settings['set_amount'] == 1){
			$message = __('You can change your prediction %s before the first match', 'poule-tournament');
		}else if(array_key_exists('set_amount',$settings) && $settings['set_amount'] == 2){
			$message = __('You can only enter one time your prediction', 'poule-tournament');
		}
		
		$message = sprintf($message, $timeto);
		
		if (file_exists(Poule_Tournament::$template_path . "my-prediction/message.php")) {
            include_once Poule_Tournament::$template_path . "my-prediction/message.php";
        }else if (file_exists(Poule_Tournament::$template_path . "message.php")) {
            include_once Poule_Tournament::$template_path . "message.php";
        }else{
            include_once POULE_PATH . 'template/message.php';
        }
		
	}
	
	/**
	 * Add message no matches
	 * 
	 * Check if there are matches available. If not show an 
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $groups array with all the groups
	 */
	public function check_matches_count($groups){
		if(count($groups) == 0){
			echo '<div class="alert alert-danger">'.__('No matches in this tournament phase', 'poule-tournament').'</div>';
		}
	}

	/**
	 * return the subpoules
	 * 
	 * Get the subpoules that are connected to the current user for the podium
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @return object The subpoules in the new order
	 */
	public function get_subpoules(){
        global $wpdb;
        
		$poules = array();
		$subpoules = array();
		$subpoulesmeta = FALSE;
		
		if(get_user_meta(get_current_user_id(), '_poule_order_subpoules', TRUE) == '' || count(unserialize(get_user_meta(get_current_user_id(), '_poule_order_subpoules', TRUE))) == 0 ){
			//bestaad niet
			foreach($wpdb->get_results($wpdb->prepare("SELECT poule_id FROM {$wpdb->prefix}poule_subpoule_users WHERE user_id='%d' AND status='1'", get_current_user_id())) as $poule){
				$subpoules[] = $poule->poule_id;
			}
		}else{
			//bestaad
			$order = array();
			
			foreach(unserialize(get_user_meta(get_current_user_id(), '_poule_order_subpoules', TRUE)) as $poule){
				$check = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE user_id='%s' AND status='1' AND poule_id='%s'", get_current_user_id(),$poule));
				if($check != null){
					$subpoules[] = $poule;
				}
			}
			update_user_meta(get_current_user_id(), '_poule_order_subpoules', serialize($subpoules));
			$subpoulesmeta = $subpoules;
		}
				
		foreach($subpoules as $subpouleid){
			$subpoule = get_post($subpouleid);
			if($subpoule != null && $subpoule->post_status != "trash"){
				$author = get_userdata($subpoule->post_author);
				$info = $wpdb->get_row($wpdb->prepare("SELECT token FROM {$wpdb->prefix}poule_subpoule_users WHERE user_id='%s' AND poule_id='%s'", get_current_user_id(), $subpoule->ID));
				$poules[] = (object) array('title' => $subpoule->post_title,'from' => $author->nickname, 'delete_link' => md5('delete').$info->token, 'poule_id' => $subpoule->ID);
			}else if($subpoule != null && $subpoule->post_status != "trash"){
				//verwijdert trash
				if($subpoulesmeta !== FALSE){
					unset($subpoulesmeta[$subpouleid]);
				}
				$wpdb->update($wpdb->prefix.'poule_subpoule_users', array('status' => 2), array('user_id' => get_current_user_id(), 'poule_id' => $subpouleid));
			}else{
				//permanent
				if($subpoulesmeta !== FALSE){
					unset($subpoulesmeta[$subpouleid]);
				}
				$wpdb->delete($wpdb->prefix.'poule_subpoule_users',array('user_id' => get_current_user_id(), 'poule_id' => $subpouleid));
			}
		}
		
		if($subpoulesmeta !== FALSE){
			update_user_meta(get_current_user_id(), '_poule_order_subpoules', serialize($subpoulesmeta));
		}
		
        return (object) $poules;
    }
    
	/**
	 * Connect a user
	 * 
	 * Connect the user to the subpouel after create a account
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param int $user_id user id of the current user
	 */
    function connect_user_to_subpoule( $user_id ) {
        global $wpdb;
        $user_info = get_userdata($user_id);
        $info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_accounts WHERE email='%s'", $user_info->user_email));
        if($info != null){
            $wpdb->insert($wpdb->prefix.'poule_subpoule_users',array('poule_id' => $info->poule_id, 'token' => poule_create_token(), 'user_id' => $user_id, 'status' => 1));
			
            $wpdb->delete($wpdb->prefix.'poule_subpoule_accounts',array('id' => $info->id));
        }
    }
    
    /**
	 * Add a badge to the invitaion tab in subpoules
     * 
	 * Àdd the badge in the invitaion tab with a count of invitations
	 * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @global database $wpdb
     * @return nothing echo the badge
     * @since version
     * @version string
	 */
    public function add_badge(){
        global $wpdb;
        
        $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) as count FROM {$wpdb->prefix}poule_subpoule_users WHERE user_id='%s' AND status='0'",get_current_user_id()) );
            
        if($count != 0){
            echo '<span class="badge">'.$count.'</span>';
        }
    }
    
	/**
	 * accept/remoce subpoule 
	 * 
	 * add/remove through the email links. show a messge by an error
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 */
	public function subpoule_url_action(){
		global $wpdb;
		
        $messagetest = null;
        $messageclass= null;
        if(isset($_GET['from']) && $_GET['from'] == "email"){
            $types = array('accept', 'delete');
			
            if(isset($_GET['type']) && isset($_GET['token']) && isset($_GET['poule_id']) && in_array($_GET['type'], $types) && is_user_logged_in()){
                
                $token = str_replace(md5($_GET['type']),'',$_GET['token']);

                $subpoule = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%d' AND user_id='%d' and token='%s' AND status='0'",$_GET['poule_id'],get_current_user_id(),$token));

                if($subpoule != null){

                    if($_GET['type'] == "accept"){
                        $update = array('status' => 1, 'update_time' => current_time( 'mysql' ));
                    }else{
                        $update = array('status' => 2, 'update_time' => current_time( 'mysql' ));
                    }

                    $wpdb->update($wpdb->prefix.'poule_subpoule_users',$update,array('id' => $subpoule->id));

                    $messageclass = 'alert-success';
                    $messagetest = __('You are now added to the subpoule', 'poule-tournament');
					
                }else{
                    $subpoule = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%d' AND status='1' AND user_id='%d'",$_GET['poule_id'],get_current_user_id()));

                    if($subpoule != null){

                        if($subpoule->status != 0){

                            $messageclass = 'alert-info';
                            $messagetest = __('You have already accepted/remove this subpoule', 'poule-tournament');

                        }

                    }

                }
            }else if(isset($_GET['type']) && isset($_GET['token']) && isset($_GET['poule_id']) && in_array($_GET['type'], $types) && !is_user_logged_in()){
                $messageclass = 'alert-danger';
                $messagetest = __('For accept/remove this subpoule is a login required','poule-tournament');
            }else{
                $messageclass = 'alert-danger';
                $messagetest = __('Wrong url parameters', 'poule-tournament');
            }
            
        }
        
        if($messagetest != null){
            printf(
                '<div class="alert %s">%s</div>',$messageclass,$messagetest);
        }
        
	}
    
    /**
	 * Set the email content type
     * 
	 * Set the ermail content type to html
	 * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since version 2.2
     * @version 1
	 * @param string $content_type old type
	 * @return new type
	 */
    public function set_content_type( $content_type ){
        return 'text/html';
    }
    
    /**
	 * include template
	 * 
	 * Open the email header
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since version 2.2
     * @version 1
	 */
    public function email_header($settings){
        if (file_exists(Poule_Tournament::$template_path . "email/header.php")) {
            include (Poule_Tournament::$template_path . "email/header.php");
        }else{
            include (POULE_PATH . 'template/email/header.php');
        }
    }
    
    /**
	 * include template
	 * 
	 * Open the email footer
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since version 2.2
     * @version string 1
	 */
    public function email_footer($settings){
        if (file_exists(Poule_Tournament::$template_path . "email/footer.php")) {
            include( Poule_Tournament::$template_path . "email/footer.php");
        }else{
            include( POULE_PATH . 'template/email/footer.php');
        }
    }
    
    /**
	 * Get my invitations
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @global database $wpdb
     * @return array the invitations
     * @since version 2.2
     * @version 1
	 */
    public function subpoule_invitations_poules(){
    	global $wpdb;
    	
    	$invitations = array();
    	foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE user_id='%s' AND status='0'",get_current_user_id())) as $invitation){
    		
    		$key_accept = $invitation->token.md5('accept');
    		$key_delete = $invitation->token.md5('delete');
    		
    		$poule_info = get_post($invitation->poule_id);
    		
    		$user_info = get_userdata($poule_info->post_author);
    		
    		$invitations[] = array('name' => $poule_info->post_title, 'inviter' => $user_info->display_name, 'id' => $invitation->poule_id,'key_accept' => $key_accept, 'key_delete' => $key_delete);
    	}
        return $invitations;
    }
    
    /**
	 * include template
	 * 
	 * Include subpoule tab own subpoules template
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since version 2.2
     * @version 1
	 */
    public function subpoules_own(){
        if (file_exists(Poule_Tournament::$template_path . "subpoules/own.php")) {
            include_once Poule_Tournament::$template_path . "subpoules/own.php";
        }else{
            include_once POULE_PATH . 'template/subpoules/own.php';
        }
    }
    
    /**
	 * include template
	 * 
	 * Include subpoule tab invitations template
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since version 2.2
     * @version 1
	 */
    public function subpoules_invitations(){
        if (file_exists(Poule_Tournament::$template_path . "subpoules/invitations.php")) {
            include_once Poule_Tournament::$template_path . "subpoules/invitations.php";
        }else{
            include_once POULE_PATH . 'template/subpoules/invitations.php';
        }
    }
    
    /**
	 * include template
	 * 
	 * Include subpoule tab subpoules template
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since 2.2
     * @version 1
	 */
    public function subpoules_subpoules(){
        if (file_exists(Poule_Tournament::$template_path . "subpoules/subpoules.php")) {
            include_once Poule_Tournament::$template_path . "subpoules/subpoules.php";
        }else{
            include_once POULE_PATH . 'template/subpoules/subpoules.php';
        }
    }
    
    /**
	 * Create array for the podium
     * 
     * @access public
     * @author Stefan de Bruin info@stefandebruin.eu
     * @global database $wpdb
     * @global query $wp_query
     * @return array with the podium
     * @since version 1
     * @version string 2.2
	 */
    public function get_podium(){
        global $wpdb,$wp_query;
    
		$phases = poule_get_phases();
		
        $phase = (isset($wp_query->query_vars['poulephase'])) ? $wp_query->query_vars['poulephase'] : $phases[0]->slug;

        $users = array();
        /*
         * Check for specific subpoule
         */
		$subpoule = null;
		
		$settings = get_option("poule_settings",array());
		
		if(array_key_exists('subpoule_default', $settings) && $settings['subpoule_default'] != 1 && is_user_logged_in()){
			$subpoules = $this->get_sub_poules();
			$subpoule = $subpoules[0];
		}
		
        if(isset($_GET['poule-url']) || $subpoule != null){
			
			$poule_name = (isset($_GET['poule-url'])) ? $_GET['poule-url'] : $subpoule['slug'];
			
            $args=array(
              'name' => $poule_name,
              'post_type' => 'subpoule',
            );
            $poule = get_posts($args);
            
			if( $poule ) {
				$check = FALSE;
                foreach ($wpdb->get_results($wpdb->prepare("SELECT distinct user_id FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%s' AND status='1'",$poule[0]->ID), ARRAY_A) as $user){
					$check = get_user_meta($user['user_id'], '_poule_podium', TRUE);
					
					if($check == null || $check == 0 ){
						continue;
					}
					
					if(get_current_user_id() == $user['user_id']) $check = true;
					
                    $users[$user['user_id']] = array('id' => $user['user_id']);
				}
				
				if(!$check){
					$users = array();
				}
				
            }
        }
		
        if(count($users) == 0 && $subpoule == null){
			
			foreach(get_users(array()) as $user){
				
				$check = get_user_meta($user->ID, '_poule_podium', TRUE);
				
				if($check == null || $check == 0 ){
					continue;
				}
				$users[$user->ID] = array('id' => $user->ID);
			}
			
//			foreach (get_users($args) as $user) {
//				$check = $wpdb->get_reults($wpdb->prepare("SELECT distinct user_id FROM {$wpdb->prefix}poule_score WHERE user_id='%s'",$user->ID));
//				if(){
//					
//				}
//			}
//			
//            foreach ($wpdb->get_results("SELECT distinct user_id FROM {$wpdb->prefix}poule_score", ARRAY_A) as $user)
//                $users[$user['user_id']] = array('id' => $user['user_id']);
        }
        
        $matches = array();
        foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_matches",ARRAY_A) as $id => $match) {
            $matches[$match['id']] = $match;
        }
        
        /*
         * Get the points
         */
        foreach($users as $userid => $user){
            $points = 0;
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_score WHERE user_id='%s'",$userid),ARRAY_A) as $id => $phase) {
				$points += $this->calculate_points(unserialize($phase['score']), $matches, $points);
//                $scores = unserialize($phase['score']);
//                foreach($scores as $key => $score){
//                    if($score['score_1'] != '' && $score['score_2'] != ''){
//                    
//                        $match_info = unserialize($matches[$key]['score']);
//                        if($match_info['score_1'] == $score['score_1'] && $score['score_1'] == $score['score_2']){
//                            $points += 2;
//                        }else if($match_info['score_1'] > $match_info['score_2'] && $score['score_1'] > $score['score_2']){
//                            $points++;
//                        }else if($match_info['score_1'] < $match_info['score_2'] && $score['score_1'] < $score['score_2']){
//                            $points++;
//                        }
//                    }
//                }
            }
            $users[$userid]['score'] = $points;
        }
        
        foreach($users as $id => $user){
            $user_info = get_userdata( $id );
			
            $users[$id]['fullname'] = $user_info->first_name . ' ' . $user_info->last_name;
			
			if(trim($users[$id]['fullname']) == ""){
				$users[$id]['fullname'] = $user_info->display_name;
			}
			$users[$id]['url'] = $user_info->user_nicename;
            
        }
		
        $users = $this->array_sort_by_column($users,'score',SORT_DESC);
		$place = 1;
		foreach($users as $id => $user){
			$users[$id]['place'] = $place;
			$place++;
		}
		
        return $users;
    }
    
	/**
	 * calculate the points
	 * 
	 * Caluclate it for the podium. By loop throw the matches and check it with the official result.
	 * 
	 * @access private
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $data The data to calculate the points
	 * @param array $matches array of the match information
	 * @param int $points
	 * @return int The points for this phase
	 */
	private function calculate_points($data,$matches,$points = 0){
		foreach($data as $key => $score){
			$match_info = unserialize($matches[$key]['score']);
			
			if($score['score_1'] != '' && $score['score_2'] != '' && $match_info['score_1'] != "" && $match_info['score_2'] != ''){
				//echo $match_info['score_1'] .' '. $score['score_1'].'<br />';
				if($match_info['score_1'] == $score['score_1'] && $match_info['score_2'] == $score['score_2']){
					$points = $points + 2;
				}
				if($match_info['score_1'] > $match_info['score_2'] && $score['score_1'] > $score['score_2']){
					$points++;
				}else if($match_info['score_1'] < $match_info['score_2'] && $score['score_1'] < $score['score_2']){
					$points++;
				}else if($match_info['score_1'] == $match_info['score_2'] && $score['score_1'] == $score['score_2']){
					$points++;
				}
				
				if($match_info['score_1'] == $match_info['score_2'] && $match_info['penalty_1'] != '' && $match_info['penalty_2'] != ''){
					if($score['score_1'] == $score['score_2']){
						if($match_info['penalty_1'] < $match_info['penalty_2'] && $score['penalty'] == $matches[$key]['country_2']){
							$points++;
						}else if($match_info['penalty_1'] > $match_info['penalty_2'] && $score['penalty'] == $matches[$key]['country_1']){
							$points++;
						}
					}
				}
			}
		}
		return $points;
	}
	
    /**
	 * get the subpoule for select at the podium page
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @global database $wpdb
     * @return array all the subpoules
     * @since version 2.2
     * @version string 1
	 */
    public function get_sub_poules(){
		global $wpdb;
		
		$poules = array();
		
		//if(get_user_meta(get_current_user_id(), '_poule_order_subpoules', TRUE) == FALSE || count(get_user_meta(get_current_user_id(), '_poule_order_subpoules', TRUE)) == 0 ){
		$subpoulesmeta = unserialize(get_user_meta(get_current_user_id(), '_poule_order_subpoules', TRUE));
		$subpoules = $subpoulesmeta;
		
		if($subpoules == "" || $subpoules == false || count($subpoules) == 0){
			$subpoules = array();
			
			foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE user_id='%d'",get_current_user_id())) as $poule){
				$subpoules[] = $poule->poule_id;
			}
		}
		
		$edit = FALSE;
		foreach($subpoules as $poule){
			$poule_info = get_post($poule);
			
			if($poule_info == null || $poule_info->post_status == "trash"){
				$edit = TRUE;
				if(count($subpoules) != 0 && array_key_exists($poule, $subpoulesmeta)){
					unset($subpoulesmeta[array_search($poule, $subpoulesmeta)]);
				}
				//sql update
				if($poule_info == null){
					$wpdb->delete($wpdb->prefix.'poule_subpoule_users',array('user_id' => get_current_user_id(), 'poule_id' => $poule));
				}else{
					$wpdb->update($wpdb->prefix.'poule_subpoule_users', array('status' => 2), array('user_id' => get_current_user_id(), 'poule_id' => $poule));
				}
					
			}else{
				$select = '';
				if(isset($_GET['poule']) && $_GET['poule'] == $poule_info->post_name){
					$select = 'selected="selected"';
				}
				$poules[] = array('name'=> $poule_info->post_title, 'slug' => $poule_info->post_name,'selected' => $select);
			}
		}
		
		if($edit){
			update_user_meta(get_current_user_id(), '_poule_order_subpoules', serialize($subpoulesmeta));
		}
        return $poules;
    }
    
    /**
	 * Select correct template
	 * 
	 * Theck if the user is logged in and show the subpoule template. of the login template
     * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since version 2.2
     * @version 1
	 */
    public function sub_poules(){
        if(is_user_logged_in()){
            if (file_exists(get_template_directory() . "/poule-tournament/" . "podium/subpoules.php")) {
                include_once get_template_directory() . "/poule-tournament/" . "podium/subpoules.php";
            }else{
                include_once POULE_PATH . 'template/podium/subpoules.php';
            }
        }
    }
    
    /**
	 * Get the official result
	 * 
     * calculate and create the official result array. throuw looping all the matches in a phase
	 * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @global database $wpdb
     * @return array the matches with scores
     * @since version 2.2
     * @version 1
	 */
    public function get_matches_official(){
        global $wpdb, $wp_query;
		
		$phases = poule_get_phases();
		
		$phase = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
        //$phase = (isset($wp_query->query_vars['poulephase'])) ? $wp_query->query_vars['poulephase'] : poule_get_phases()[0]->slug;
        $phaseinfo = get_term_by('slug', $phase, 'phase');
        
        $groups = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $phaseinfo->term_id),ARRAY_A) as $rowid => $group){
            $matches = array();
            
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $id => $match) {
                $starttime = date(get_option( 'date_format' )." ".get_option( 'time_format' ),  strtotime($match['start_date']));
                $country1 = get_post($match['country_1']);
                $country2 = get_post($match['country_2']);
                
                $score = unserialize($match['score']);
                
                $hidden = 'hidden="hidden"';
                
            	$panaltycountry = "";
                if($score['penalty_1'] != "" || $score['penalty_1'] != 0){
                    $hidden = '';
                    $panaltycountry = ($score['penalty_1'] > $score['penalty_2']) ? $country1->post_title : $country2->post_title;
                }
                
                $matchrow = array(
            		'start_time' => $starttime,
            		'country1' => $country1->post_title,
            		'country2' => $country2->post_title,
                    'score1' => $score['score_1'],
                    'score2' => $score['score_2'],
                    'penalty1' => $score['penalty_1'],
                    'penalty2' => $score['penalty_2'],
                    'penalty_country' => $panaltycountry,
                    'hidden' => $hidden,
                    'row' => $match['id'],
            	);
            	
                $matches[$match['id']] = (object) $matchrow;
            }
            $groups[$group['id']] = (object) array('group_name' => $group['group_name'],'matches' => (object) $matches);
        }
        return $groups;
    }
    
	/**
	 * Return my prediction score
	 * 
     * Create the my prediction array with all the variables. Also create the variables in the array. This function return also the score for a personal page
	 * 
     * @access public
     * @author Stefan de Bruin <info@stefandebruin.eu>
     * @since version 2.2
     * @version 1.1
	 * @global database $wpdb Object for sql queries
	 * @global query $wp_query url parameters
	 * @global validation $poule_validation validate the forms
	 * @param int $user_id the user id 
	 * @param string $phaseurl url phase parameter
	 * @return array the matches with scores
	 */
    public function get_matches_own($user_id = 0, $phaseurl = null){
        global $wpdb,$wp_query, $poule_validation;
		
		$settings = get_option("poule_settings",array());
		$phases = poule_get_phases();
		
		if($_SERVER['REQUEST_METHOD'] == "POST" && count($poule_validation->errors['score']) != 0){
			$errors = $poule_validation->errors['score'];
		}else{
			$errors = array();
		}
		
		if($phaseurl != null){
			$phase = (isset($_GET[$phaseurl])) ? $_GET[$phaseurl] : $phases[0]->slug;
		}else{
			$phase = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
		}
		
        $phaseinfo = get_term_by('slug', $phase, 'phase');

		$current_user = ($user_id != 0) ? get_user_by('id', $user_id) : wp_get_current_user();
		
		
		
        $sql = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_score WHERE user_id='%s' AND phase='%s'",$current_user->ID, $phaseinfo->term_id),ARRAY_A);
		
		$user_score = (is_array($sql)) ? unserialize($sql['score']) : array();
        
		$term_meta = get_option( "taxonomy_".$phaseinfo->term_id );
        
        $flags = array();
        
        $i = 0;
        $groups = array();
		
		date_default_timezone_set(get_option('timezone_string'));
		
		//query om te controleren of er al een wedstrijd begonnen is.
		$checkstartmatches = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE start_date<='%s'", current_time( 'mysql' )));
		
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $phaseinfo->term_id),ARRAY_A) as $rowid => $group){
            $matches = array();
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $id => $match) {
                $starttime = date("d-m-Y H:i:s",  strtotime($match['start_date']));
                $country1 = get_post($match['country_1']);
                $country2 = get_post($match['country_2']);
                
                if(!array_key_exists($match['country_1'], $flags)){
                    $flags[$match['country_1']] = $this->add_flag($match['country_1']);
                }
                
                if(!array_key_exists($match['country_2'], $flags)){
                    $flags[$match['country_2']] = $this->add_flag($match['country_2']);
                }
                
                $score = (array_key_exists($match['id'], $user_score)) ? $user_score[$match['id']] : array();
            	
                $hidden = '';
                if(array_key_exists('score_1', $score) && array_key_exists('score_2', $score)){
					if($score['penalty'] == '' && !is_numeric($score['penalty'])){
						$hidden = 'hidden="hidden"';
					}
                }else{
                    $hidden = 'hidden="hidden"';
                }
                
				$timeto = 30;
				if(array_key_exists("score_time", $settings)){
					if(is_numeric($settings['score_time'])){
						$timeto = $settings['score_time'];
					}
				}
				
                $time = strtotime($match['start_date']) - ($timeto * 60);
                $readonly = '';
                if(time() > $time) {
					$readonly = 'readonly="readonly"';
				}
				
				
				if(array_key_exists('set_amount',$settings) && $settings['set_amount'] == 0 || !array_key_exists('set_amount',$settings)){
					
				}else if(array_key_exists('set_amount',$settings) && $settings['set_amount'] == 1 && $checkstartmatches >= 1 && count($score) != 0){
					$readonly = 'readonly="readonly"';
				}else if(array_key_exists('set_amount',$settings) && $settings['set_amount'] == 2 && count($score) != 0){
					$readonly = 'readonly="readonly"';
				}
				
//				
//				if(count($score) != 0 && array_key_exists('set_predi_1',$settings)){
//					if($settings['set_predi_1'] == 1) $readonly = 'readonly="readonly"';
//				}
                
                $penalty = array(
                    0 => array('id' => $match['country_1'], 'name' => $country1->post_title, 'select' => ''),
                    1 => array('id' => $match['country_2'], 'name' => $country2->post_title, 'select' => ''),
                );
                
				$scorecheck1 = (array_key_exists('score_1', $score)) ? $score['score_1'] : 0;
				$scorecheck2 = (array_key_exists('score_2', $score)) ? $score['score_2'] : 0;
				$penaltyWinar = '';
                if(array_key_exists('penalty', $score) && $score['penalty'] != '' && $scorecheck1 == $scorecheck2 && $scorecheck1 != null){
                    if($score['penalty'] == $match['country_1']){
                        $penalty[0]['select'] = 'selected="selected"';
						$penaltyWinar = $country1->post_title;
                    }else{
                        $penalty[1]['select'] = 'selected="selected"';
						$penaltyWinar = $country2->post_title;
                    }
                }
                
                $matchrow = array(
            		'start_time' => $starttime,
                    'flag_country1' => $flags[$match['country_1']],
            		'country1' => $country1->post_title,
                    'flag_country2' => $flags[$match['country_2']],
            		'country2' => $country2->post_title,
                    'score1' => (array_key_exists('score_1', $score)) ? $score['score_1'] : 0,
                    'score2' => (array_key_exists('score_2', $score)) ? $score['score_2'] : 0,
                    'penalty1' => (array_key_exists('penalty_1', $score)) ? $score['penalty_1'] : "",
                    'penalty2' => (array_key_exists('penalty_2', $score)) ? $score['penalty_2'] : "",
                    'penalty' => $penalty,
					'penaltywinnar' => $penaltyWinar,
                    'row' => $match['id'],
                    'readonly' => $readonly,
                    'hidden' => $hidden
            	);
            	
				if(isset($errors[$match['id']])){
					if($readonly){
						$matchrow['error_score1'] = 'has-warning';
						$matchrow['error_score2'] = 'has-warning';
						$matchrow['error_penalty'] = 'has-warning';
						
					}else{
						$matchrow['error_score1'] = (isset($errors[$match['id']]['score1'])) ? 'has-error': '';
						$matchrow['error_score2'] = (isset($errors[$match['id']]['score2'])) ? 'has-error': '';
						$matchrow['error_penalty'] = (isset($errors[$match['id']]['penalty'])) ? 'has-error': '';
					}
				}else{
					$matchrow['error_score1'] = '';
					$matchrow['error_score2'] = '';
					$matchrow['error_penalty'] = '';
				}
				
                $matches[$match['id']] = $matchrow;
            }
            
            $groups[$group['id']] = array('group_name' => $group['group_name'],'matches' => $matches);
        }
        return $groups;
        
    }
    
	/**
	 * Add the flag
	 * 
	 * Add the flag to the correct country
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param int $id The id of the thumbnail
	 * @return string flag url
	 */
    private function add_flag($id){
        return wp_get_attachment_url( get_post_thumbnail_id($id) );
    }
    
	/**
	 * Add a message
	 * 
	 * Check if there matches are available and then echo the message or not.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global query $wp_query object with url parameters
	 * @global database $wpdb Object for sql queries
	 * @param Boolean $admin True by admin
	 */
	public function phase_message($admin = FALSE){
		global $wp_query,$wpdb;
        $phases = poule_get_phases();
        
        $exist = FALSE;
		$id = 0;
        foreach($phases as $phase){
            $term = get_term_by('id', $phase->term_id, "phase");
            if( is_admin() ){
                $tab = (isset($_GET['phase-url']))?$_GET['phase-url']:$phases[0]->slug;
                if($tab == $term->slug){
                    $exist = TRUE;
                    $id = $term->term_id;
                    break;
                }
            }else{
                $tab = (isset($wp_query->query_vars['poulephase']))?$wp_query->query_vars['poulephase']:$phases[0]->slug;
                if($tab == $term->slug){
                    $exist = TRUE;
                    $id = $term->term_id;
                    break;
                }
            }
        }
        
        if(!$exist){
			if($admin){
				echo'<div id="message" class="error"><p>'. __('No phase', 'poule-tournament') . '</p></div>';
			}else{
				echo '<div class="alert alert-danger">'. __('No phase', 'poule-tournament') .'</div>';
			}
        }else{
			$count_matches = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) as count FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC",$id) );
            
			if($count_matches == 0){
				if(is_admin()){
					echo'<div id="message" class="error"><p>'. __('No matches', 'poule-tournament') . '</p></div>';
				}else{
                    echo '<div class="alert alert-danger">'. __('No matches', 'poule-tournament') .'</div>';
				}
			}
		}
	}
	
	/**
	 * Login check
	 * 
	 * Echo a message for non logged in users
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function before_own_score(){
		if(!is_user_logged_in()){
			echo '<div class="alert alert-danger">'. __('Login is required', 'poule-tournament') .'</div>';
		}
	}

	/**
	 * Add a message
	 * 
	 * Add the message if the phase does't exist
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global query $wpdb Object with the url parameters
	 */
    public function before_official_score(){
        global $wp_query;
        $phases = poule_get_phases();
        
        $phaseurl = (isset($wp_query->query_vars['poulephase'])) ? $wp_query->query_vars['poulephase'] : $phases[0]->slug;
        
        $exist = FALSE;
        foreach($phases as $phase){
            $term = get_term_by('id', $phase->term_id, "phase");
            if($phaseurl == $term->slug){
                $exist = TRUE;
                break;
            }
        }
        
        if(!$exist){
            echo '<div class="alert alert-danger">'. __('No phase', 'poule-tournament') .'</div>';
        }
    }
	
	/**
	 * Create the pagination
	 * 
	 * Create the paginations for the tournament phases admin and frontend
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1.1
	 * @global database $wpdb Object for sql queries
	 * @global query $wp_query object with the url parameters
	 * @param string $get_link GET parameter
	 */
	public function create_pagination($get_link = null){
        global $wp_query;
        $taxonomyname = "phase";
        
        $phases = poule_get_phases();
		
        if(is_admin()){
            $current = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
        }else if($get_link != null){
            $current = (isset($_GET[$get_link])) ? $_GET[$get_link] : $phases[0]->slug;
        }else{
			$current = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
		}
        
        $pagination = (is_admin()) ? '<h3 class="nav-tab-wrapper">' : '<ul class="pagination" id="pagination">';
        
		foreach ($phases as $toplevelterm) {
            $term = get_term_by('id', $toplevelterm->term_id, $taxonomyname);
            if(is_admin()){
                $active = ($current == $term->slug)?"nav-tab-active":"";
            }else{
                $active = ( $current == $term->slug ) ? "active" : "";
            }
            
            if(is_admin()){
                $link = 'edit.php?';
                $get = $_GET;
                unset($get['phase-url']);
                foreach($get as $key => $value){
                    $link .= $key . '=' . $value . '&';
                }
                $link .= 'phase-url=' . $term->slug;
            }else if($get_link != null){
				unset($_GET[$get_link]);
                $link = site_url($wp_query->post->post_name);
				$start = TRUE;
				foreach($_GET as $key => $var){
					$link .= ($start) ? '?' : '&';
					$start = FALSE;
					$link .= $key . '=' . $var;
				}
				$link .= '&'.$get_link.'='.$term->slug;
            }else if(get_option('permalink_structure') != ''){
				$link = poule_create_correct_url(array('phase-url' => $term->slug));
			}else{
				$link = poule_create_correct_url(array('p'=>$wp_query->post->ID,'phase-url' => $term->slug));
			}
			
            if(is_admin()){
                $pagination .= '<a class="nav-tab ' . $active . '" href="'.$link.'">' . $term->name . '</a>';
            }else{
                $pagination .= '<li class=" '.$active.'">';
                $pagination .= '<a href="'.$link.'">' . $term->name . '</a>';
                $pagination .= '</li>';
            }
            
		}
        $pagination .= (is_admin()) ? '</h3>' : '</ul>';
        
        echo $pagination;
    }
	
	/**
	 * sort a multi array 
	 * 
	 * sort a multi array
	 * 
	 * @access private
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * @global database $wpdb
	 * @global form_validation $poule_validation
	 * @param array $multiArray the array 
	 * @param string $col the column to sort
	 * @param string $dir sort type
	 * @return array the sorted array
	 */
    private function array_sort_by_column($multiArray, $col, $dir = SORT_ASC) {
		$keys = array();
		$sort = array();
		foreach ($multiArray as $key => $row) {
			$keys[$key] = $key;
			$sort[$key] = $row[$col];
		}

		array_multisort($sort, $dir, $keys, SORT_ASC, $multiArray);
		return $multiArray;
	}
}


/**
 * 
 */
new poule_functions();