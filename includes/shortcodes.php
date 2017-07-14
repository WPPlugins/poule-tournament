<?php
/**
 * File contains all the shortcodes
 * 
 * Countains all the shortcodes only
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * shortcode class
 * 
 * Class with the shortcodes
 * 
 * @since version 1
 * @version 2.2
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class shortcodes {
    
	/**
	 * error
	 * 
	 * true if there is an error
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @var boolean
	 */
	public $error = FALSE;
	
	/**
	 * All the matches
	 * 
	 * Array of the matches in a phase
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @var array
	 */
	public $matches = array();
	
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
        add_shortcode( 'poule_official_result', array($this,'Official_Result') );
        add_shortcode( 'poule_my_prediction', array($this,'My_Prediction') );
        add_shortcode( 'poule_podium', array($this,'Podium') );
        add_shortcode( 'poule_subpoules_home', array($this,'subpoules') );
		add_shortcode( 'poule_thank_you', array($this,'thank_you') );
		
		add_action('poule_before_own_score' , array($this, 'message_error'));
		
		
//        add_shortcode( 'poule_own_score_2', array($this,'Own_Score') );
//        add_shortcode( 'poule_own_poule_2', array($this,'Own_Poule') );
        
        //add_action( 'wp_ajax_exist_check', array($this,'my_action_callback') );
        //add_action( 'wp_ajax_nopriv_exist_check', array($this,'my_action_callback') );
    }
	
	/**
	 * shortcode thank you
	 * 
	 * Include the template file
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	public function thank_you(){
		if (file_exists(Poule_Tournament::$template_path . "my-prediction/thank-you.php")) {
            include_once Poule_Tournament::$template_path . "my-prediction/thank-you.php";
        }else{
            include_once POULE_PATH . 'template/my-prediction/thank-you.php';
        }
	}
	
	/**
	 * shortcode Podium
	 * 
	 * Include the template file
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 */
    public function Podium(){
		if(isset($_GET['user'])){
			if (file_exists(Poule_Tournament::$template_path . "podium/user.php")) {
				include_once Poule_Tournament::$template_path . "podium/user.php";
			}else{
				include_once POULE_PATH . 'template/podium/user.php';
			}
		}else{
			if (file_exists(Poule_Tournament::$template_path . "podium/podium.php")) {
				include_once Poule_Tournament::$template_path . "podium/podium.php";
			}else{
				include_once POULE_PATH . 'template/podium/podium.php';
			}
		}
			
    }
    
	/**
	 * shortcode Podium
	 * 
	 * Include the template file
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 */
    public function Official_Result(){
        if (file_exists(Poule_Tournament::$template_path . "official-result/score.php")) {
            include_once Poule_Tournament::$template_path . "official-result/score.php";
        }else{
            include_once POULE_PATH . 'template/official-result/score.php';
        }
    }
    
	/**
	 * shortcode Podium
	 * 
	 * Include the template file. Save the predictions in to the database after a validation
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validate the forms
	 */
    public function My_Prediction(){
        global $wpdb, $poule_validation, $wp_query;
		
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
			
            $taxonomyname = "phase";
            $args = array(
                'hide_empty' => '0',
                'hierarchical' => '0',
                'parent' => '0',
                'orderby' => 'id',
                'order' => 'ASC'
            );
            $terms = get_terms($taxonomyname, $args);
            
			$phase = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $terms[0]->slug;
			
            $phaseinfo = get_term_by('slug', $phase, 'phase');
            
            $current_user = wp_get_current_user();

            $args = array(
                'score1' => array('required' => true, 'type'=>'match_score'),
                'score2' => array('required' => true, 'type'=>'match_score'),
                'penalty' => array('required' => true, 'type'=>'match_penalty'),
            );

            $poule_validation->validate_input('score', $args, $this);
			
			$error_message = FALSE;
			
            if($poule_validation->counterrors['score'] == 0){
                $newscore = array();
                $check = $wpdb->get_row($wpdb->prepare("SELECT score FROM {$wpdb->prefix}poule_score WHERE user_id='%d' AND phase='%d'",$current_user->ID,$phaseinfo->term_id),ARRAY_A);
                
				if($check == null){
					$oldscore = array();
				}else{
					$oldscore = unserialize($check['score']);
				}
                
				$settings = get_option( 'poule_settings', array() );
				
                foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $phaseinfo->term_id),ARRAY_A) as $rowid => $group){
                    foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $id => $match) {
                        $time = time() + (30 * 60);
                        if(date("Y-m-d H:i:s", $time) < $match['start_date']){
							
                            $newscore[$match['id']] = array(
                                'score_1' => $poule_validation->input['score'][$match['id']]['score1'],
                                'score_2' => $poule_validation->input['score'][$match['id']]['score2'],
                                'penalty' => '');
							
							$penalties = FALSE;

							$Phase_Penalties = array();
							foreach(poule_get_phases() as $phasesql){
								$meta = Poule_Get_Phase_Meta($phasesql->term_id, 'penalties');
								if($meta != null && $meta != 0 && $phase == $phasesql->slug){
									$penalties = TRUE;
//									var_dump($meta);
								}
							}
							
                            if($penalties){
								if($poule_validation->input['score'][$match['id']]['score1'] == $poule_validation->input['score'][$match['id']]['score2'] && $poule_validation->input['score'][$match['id']]['score1'] != ''){
                                    $newscore[$match['id']]['penalty'] = $poule_validation->input['score'][$match['id']]['penalty'];
                                }
                            }
                        }else{
							
                            if(array_key_exists($match['id'], $oldscore)){
                                $newscore[$match['id']] = $oldscore[$match['id']];
                            }else{
                                $newscore[$match['id']] = array('score_1' => '','score_2' => '','penalty' => '');
                            }
                        }
                    }
                }
                
                $serscore = serialize($newscore);

                if($check == null){
                    $wpdb->insert(
                        $wpdb->prefix.'poule_score',
                        array(
							'user_id' => $current_user->ID, 
							'phase' => $phaseinfo->term_id,
							'score' => $serscore
						)
					);
                }else{
                    $wpdb->update(
                        $wpdb->prefix.'poule_score',
                        array(
							'score' => $serscore
						),
                        array(
							'user_id' => $current_user->ID, 
							'phase' => $phaseinfo->term_id
						)
					);
                }
				
				update_user_meta(get_current_user_id(), '_poule_podium', 1);
				
				
				if(array_key_exists('redirect', $settings)){
					if(is_numeric($settings['redirect']) && get_page($settings['redirect']) ){
						echo'<script> window.location.href="'.poule_create_correct_url(array('p'=>$settings['redirect'] )).'"; </script> ';
					}
				}
            }else{
				$this->error = TRUE;
				
			}
        }
        if(is_user_logged_in()){
            if (file_exists(Poule_Tournament::$template_path . "my-prediction/score.php")) {
                include_once Poule_Tournament::$template_path . "my-prediction/score.php";
            }else{
                include_once POULE_PATH . 'template/my-prediction/score.php';
            }
        }else{
            if (file_exists(Poule_Tournament::$template_path . "login.php")) {
                include_once Poule_Tournament::$template_path . "login.php";
            }else{
                include_once POULE_PATH . 'template/login.php';
            }
        }
    }
    
	/**
	 * shortcode subpoules
	 * 
	 * Include the template file, also accept|delete de subpoule from the email link
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2
	 * @version 2
	 */
    public function subpoules(){
    	do_action('poule_subpoule_url_action');
        if(is_user_logged_in()){
        	
        	if(isset($_GET['type']) && isset($_GET['token']) && isset($_GET['user_id']) && isset($_GET['poule_id']) && current_user_id() == $_GET['user_id']){
				if($_GET['type'] == "accept" || $_GET['type'] == "delete"){
					$action = str_replace('_subpoule','',$_GET['action']);

					$token = str_replace(md5($action),'',$_GET['token']);

					$subpoule = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%d' AND user_id='%d' and token='%s' AND status='0'",$_POST['poule_id'],get_current_user_id(),$token));

					if($subpoule != null){

						if($action == "accept"){
							$update = array('status' => 2);
						}else{
							$update = array('status' => 1);
						}

						$wpdb->update($wpdb->prefix.'poule_subpoule_users',$update,array('id' => $subpoule->id));

					}
				}
			}
        	
            if (file_exists(Poule_Tournament::$template_path . "subpoules/home.php")) {
                include_once Poule_Tournament::$template_path . "subpoules/home.php";
            }else{
                include_once POULE_PATH . 'template/subpoules/home.php';
            }
        }else{
            if (file_exists(Poule_Tournament::$template_path . "login.php")) {
                include_once Poule_Tournament::$template_path . "login.php";
            }else{
                include_once POULE_PATH . 'template/login.php';
            }
        }
    }
    
	/**
	 * Validate penalty
	 * 
	 * $validate the penalty for my preidiction
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global object $wp_query
	 * @param string $value
	 * @param string $input 
	 * @param int $row 
	 * @param int $field 
	 * @return boolean 
	 */
    public function validate_penalty($value, $input, $row = '', $field = ''){
        global $wp_query;
        
        $taxonomyname = "phase";
        $args = array(
            'hide_empty' => '0',
            'hierarchical' => '0',
            'parent' => '0',
            'orderby' => 'id',
            'order' => 'ASC'
        );
        $terms = get_terms($taxonomyname, $args);
        
        $phase = (isset($wp_query->query_vars['poulephase'])) ? $wp_query->query_vars['poulephase'] : $terms[0]->slug;
        
        if($phase != $terms[0]->slug){
            if(!is_numeric($value)){
                return true;
            }
        }
    }
	
	/**
	 * Validate score
	 * 
	 * Validate the score to check for number and if the match is read only. This is for my prediction
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $value THe input value
	 * @param int|string|null $key Name if the input value field
	 * @param int|null $row Number of the row in a multi array
	 * @return boolean Validate result
	 */
	public function validate_match_score($value, $key = null, $row = null){
		if(count($this->matches) == 0){
			foreach(apply_filters("poule_get_matches_own","") as $group){
				
				foreach($group['matches'] as $match){
					$this->matches[$match['row']] = $match;
				}
			}
		}
		 
		$match = $this->matches[$row];
		if($match['readonly'] != ''){
			return FALSE;
		}else{
			if(!is_numeric($value) || $value == "" || $value == null){
				return true;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Validate penalty
	 * 
	 * Validate the penalty. it reatuns deaful FALSE. Its check for a number and for possible.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $value 
	 * @param string $inputname 
	 * @param int $row 
	 * @param int $key 
	 * @return boolean 
	 */
	public function validate_match_penalty($value, $inputname = null, $row = null, $key = null){
		if(count($this->matches) == 0){
			$this->matches = apply_filters("poule_get_matches_own","");
		}
		
		$match = $this->matches[$row];
		
		$phases = poule_get_phases();
		
		$phaseurl = (isset($wp_query->query_vars['poulephase'])) ? $wp_query->query_vars['poulephase'] : $phases[0]->slug;
		
		$penalties = FALSE;
		
		$Phase_Penalties = array();
		foreach($phases as $phase){
			$meta = Poule_Get_Phase_Meta($phase->term_id, 'penalties');
//			if($meta != null && $meta != 0 && $phaseurl == $phase){
			if(is_object($meta) && $meta->penalties != null && $meta->penalties != '0'){
				$penalties = TRUE;
			}
		}
		
		if($penalties){
			if($match['readonly'] == ''){
				if($_POST['score'][$row]['score1'] == $_POST['score'][$row]['score2'] && $_POST['score'][$row]['score1'] != "" && $_POST['score'][$row]['score1'] != 0){
					if(!is_numeric($value) || $value == "" || $value == null){
						return true;
					}
					
				}else if($_POST['score'][$row]['score1'] == $_POST['score'][$row]['score2'] && $_POST['score'][$row]['score1'] == 0){
					return FALSE;
				}
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Show a error message
	 * 
	 * Show the erros message in my-prediction by a error
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global form_validation $poule_validation the validation class
	 */
	public function message_error(){
		global $poule_validation;
		
		if(isset($poule_validation->counterrors['score'])){
			if($poule_validation->counterrors['score'] != 0){
				echo '<div class="alert alert-danger">'.__('Please, Check your prediction again.', 'poule-tournament').'</div>';
			}
		}
	}
}

new shortcodes();