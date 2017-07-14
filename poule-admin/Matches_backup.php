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
 * matches pages class
 * 
 * Class with all the function to show the pages for matches
 * 
 * @since version 1
 * @version 2.2
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class matches {
    
	/**
	 * Template path
	 * 
	 * Path to the template in the admin directory
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $template;
    
	/**
	 * Object of the countries
	 * 
	 * Object with all the countries. and the propeties for the countries
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var object
	 */
    public $countries;
    
	/**
	 * all the groups
	 * 
	 * only the name and id for auto create matches
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var array
	 */
    public $group_names;
    
	/**
	 * Object of current phase
	 * 
	 * Object of the current phase found in url or the first phase
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var object
	 */
    public $phaseinfo;
    
	/**
	 * Object of the countries
	 * 
	 * Onject with all the countries. and the propeties for the countries
	 * 
	 * @access public
	 * @since version
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var object
	 * @todo verwijderen
	 */
    public $OLD_validation;
    
	/**
	 * Boolean for penalties
	 * 
	 * if true 
	 * 
	 * @access public
	 * @since version
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var bool
	 * @todo verwijderen
	 */
    public $penalty = FALSE;
    
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
        $function = (isset($_GET['function']))?$_GET['function']:"home";
        $this->template = POULE_PATH . 'poule-admin/template/matches/' . $function . ".php";
                
        add_filter('poule_table_matches_column_date', array($this,'column_date'),10,1);
        add_filter('poule_table_matches_column_time', array($this,'column_time'),10,1);
        add_filter('poule_table_matches_column_start_time', array($this,'column_start_time'),10,1);
        add_filter('poule_table_matches_add_column_country1', array($this,'column_country1'),10,1);
        add_filter('poule_table_matches_add_column_country2', array($this,'column_country2'),10,1);
        add_filter('poule_table_matches_edit_column_country1', array($this,'column_country1'),10,1);
        add_filter('poule_table_matches_edit_column_country2', array($this,'column_country2'),10,1);
        
        add_filter('poule_table_matches_home_column_country1', array($this,'home_column_country1'),10,1);
        add_filter('poule_table_matches_home_column_country2', array($this,'home_column_country2'),10,1);

		add_filter('poule_table_matches_auto_column_country1', array($this,'auto_column_country1'),10,1);
        add_filter('poule_table_matches_auto_column_country2', array($this,'auto_column_country2'),10,1);

		add_filter('poule_table_matches_auto_column_time', array($this,'auto_column_time'),10,1);
        add_filter('poule_table_matches_auto_column_date', array($this,'auto_column_date'),10,1);
        
		add_action('poule_validate_country', array($this,'validate_country'),10,4);
        
        $this->countries = poule_get_countries();
        
        require_once(POULE_PATH . 'classes/create-table.php');
    }
    
	/**
	 * function to show page content
	 * 
	 * Function that is called afther a menu click. and it's check wich functions are affailable
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function init(){
        $possible = array('home','add','edit','auto');
        $functionname = (isset($_GET['function'])) ? $_GET['function'] : 'home';
        if(in_array($functionname, $possible)){
        	$this->$functionname(); 
        }
    }
    
	/**
	 * Main page for matches
	 * 
	 * Show the matches order by group and phase
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * @global database $wpdb Object for sql queries
	 */
    public function home(){
        global $wpdb;
		
        $taxonomyname = "phase";
        
        $terms = poule_get_phases();
        
		$phase = (isset($_GET['phase'])) ? $_GET['phase'] : $terms[0]->slug;
        
        $term = get_term_by('slug', $phase, $taxonomyname);
        
        $groups = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $term->term_id)) as $rowid => $group){
            $matches = array();
            
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group->id)) as $id => $match) {
            	$starttime = date(get_option( 'date_format' )." ".get_option( 'time_format' ),  strtotime($match->start_date));
                $country1 = get_post($match->country_1);
                $country2 = get_post($match->country_2);
            	$matchrow = array(
            		'start_time' => $starttime,
            		'country1' => $country1->post_title,
            		'country2' => $country2->post_title,
            	);
            	
                $matches[$match->id] = $matchrow;
            }
            
            $table_match = new create_table('matches','home');
            $table_match->columns = array('start_time' => 'start_time', 'country1' => 'country1', '-' => '-' ,'country2' => 'country2');
            $table_match->data = $matches;
            $table_match->prepare_items();
            
            $groups[$group->id] = array('group_name' => $group->group_name,'matches' => $table_match);
        }
        
        include_once $this->template;
    }
    
	/**
	 * Add a grou and the matches
	 * 
	 * Add the group and the matches. create the table and show the validation. after saving redirect to the edit page
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
	public function add(){
        global $wpdb, $poule_validation;
        $error = array();
        $input_name = '';
        $table_match = new create_table('matches','add');
        
        $phase = (isset($_GET['phase'])) ? $_GET['phase'] : 'groep';
        $this->phaseinfo = get_term_by('slug', $phase, 'phase');
        $term_meta = Poule_Get_Phase_Meta($this->phaseinfo->term_id);
        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$insertresult = $this->SaveAdd();
			if(is_numeric($insertresult)){
				echo'<script> window.location="?post_type=country&page=matches&function=edit&phase=groep&groupid='.$insertresult.'"; </script> ';
				exit;
			}
        }else{
			$error_group_name = '';
            $errors_match = array('1' => array(),'2' => array(),'3' => array(),'4' => array(),'5' => array(),'6' => array(),);
            $input_match = array('1' => array(),'2' => array(),'3' => array(),'4' => array(),'5' => array(),'6' => array(),);
        }
        
        $data = array();
        
        for($i = 1; $i <= $term_meta->matches_per_group; $i++){
			$data[$i] = array('date' => '', 'time' => '', 'country1' => '' , 'country2' => '','row' => $i);
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $data[$i]['error'] = $poule_validation->errors['match'][$i];
				$data[$i]['input'] = $poule_validation->input['match'][$i];
            }else{
                $data[$i]['error'] = $errors_match[$i];
				$data[$i]['input'] = $input_match[$i];
            }        	
        }
        $table_match->data = $data;
        $table_match->columns = array('date' => 'date', 'time' => 'time', 'country1' => 'country1', '-' => '-' ,'country2' => 'country2');
		$table_match->prepare_items();
        
        include_once $this->template;
    }
    
	private function SaveAdd(){
		global $wpdb, $poule_validation;
		
		$args = array('group_name' => array('required' => TRUE, 'type' => 'string'));

		$poule_validation->validate_input("group_name",$args,$this);

		$args = array(
			'date' => array('required' => TRUE, 'type'=>'date'),
			'time' => array('required' => TRUE, 'type'=>'time'),
			'country1' => array('required' => TRUE, 'type'=>'country'),
			'country2' => array('required' => TRUE, 'type'=>'country'),
		);

		$poule_validation->validate_input('match', $args, $this);

		if($poule_validation->counterrors['match'] == 0 && $poule_validation->counterrors['group_name'] == 0){
			$error_group_name = '';
			//groeps naam
			$wpdb->insert(
				$wpdb->prefix."poule_matches_groups",
				array(
					'phase'=>$this->phaseinfo->term_id,
					'group_name'=>$poule_validation->input['group_name']
				)
			);
			$groupsid = $wpdb->insert_id;

			foreach($poule_validation->input['match'] as $key => $value){
				$date_sec = strtotime($value['date']);

				$wpdb->insert(
					$wpdb->prefix."poule_matches",
					array(
						'group_id'=>$groupsid,
						'start_date' => date("Y-m-d", $date_sec) . ' ' . $value['time'],
//                            'start_time' => strtotime($starttime),
						'country_1' => $value['country1'],
						'country_2' => $value['country2'],
						'score' => 'a:4:{s:7:"score_1";s:0:"";s:7:"score_2";s:0:"";s:9:"penalty_1";s:0:"";s:9:"penalty_2";s:0:"";}'));
			}
//                wp_safe_redirect( '?post_type=country&page=matches&function=edit&phase=groep&groupid='.$groupsid,200); 
			return $groupsid;
		}else{ 
			$error_group_name = '';
			if($poule_validation->counterrors['group_name'] == 1){
				$error_group_name = 'form-error';
			}
		}
		return FALSE;
	}
	
	/**
	 * Page to edit
	 * 
	 * Show the edit page. and call the validation if validation is good update the group
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
    public function edit(){
    	global $wpdb, $poule_validation;
    	
    	$phase = (isset($_GET['phase'])) ? $_GET['phase'] : '';
        $this->phaseinfo = get_term_by('slug', $phase, 'phase');

		$groupsid = (isset($_GET['groupid'])) ? $_GET['groupid'] : '';

		$groupsinfo = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE id='%s' AND phase='%s'", $groupsid, $this->phaseinfo->term_id),ARRAY_A);
		
		if($groupsinfo != null){
			$groupsnaam = $groupsinfo['group_name'];
            
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $check = $this->SaveEdit($groupsid);
            }else{
                $error_match = array();
                $input_name = array();
            }
            
			$matches = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$groupsinfo['id']),ARRAY_A);
			
			$data = array();
			foreach($matches as $id => $match){
                $date_time = explode(" ", $match['start_date']);
				$date = $date_time[0];
				$time = $date_time[1];
                
                $error = (isset($error_match[$match['id']])) ? $error_match[$match['id']] : array();
                $input = (isset($input_name[$match['id']])) ? $input_name[$match['id']] : array();
				$data[$id] = array('date'=>$date,'time'=>$time,'country1'=>$match['country_1'],'country2'=>$match['country_2'],'row'=> $match['id'],'error'=>$error,'input'=>$input);
			}
			
			$table_match = new create_table('matches','edit');
			$table_match->data = $data;
			$table_match->columns = array('date' => 'date', 'time' => 'time', 'country1' => 'country1', '-' => '-' ,'country2' => 'country2');
            $table_match->prepare_items();
            
            include_once $this->template;
		}else{
            include_once POULE_PATH . 'poule-admin/template/error.php';
        }
    }
  
	private function SaveEdit($groupsid){
		global $wpdb, $poule_validation;
		
		$args = array('group_name' => array('required' => TRUE, 'type' => 'string'));

		$poule_validation->validate_input("group_name", $args, $this);

		$args = array(
			'date' => array('required' => TRUE, 'type'=>'date'),
			'time' => array('required' => TRUE, 'type'=>'time'),
			'country1' => array('required' => TRUE, 'type'=>'country'),
			'country2' => array('required' => TRUE, 'type'=>'country'),
		);

		$poule_validation->validate_input('match', $args, $this);

		$group_name = '';                
		if($poule_validation->counterrors['match'] == 0 && $poule_validation->counterrors['group_name'] == 0){

			$groupsnaam = $poule_validation->input['group_name'];

			//groeps naam
			$wpdb->update($wpdb->prefix."poule_matches_groups",array('phase'=>$this->phaseinfo->term_id,'group_name'=>$poule_validation->input['group_name']),array('id' => $groupsid));
			$groupsid = $wpdb->insert_id;

			foreach($poule_validation->input['match'] as $key => $value){
				$sec = strtotime($value['date']);

				$date_sec = strtotime($value['date']);

				$wpdb->update(
					$wpdb->prefix."poule_matches",
					array(
						'start_date' => date("Y-m-d", $date_sec) . ' ' . $value['time'],
						//'start_time' => strtotime($starttime),
						'country_1' => $value['country1'],
						'country_2' => $value['country2'],
					),
					array(
						'id' => $key
					)
				);
			}
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Create automatic the groups and matches
	 * 
	 * The admin select in the match the winnar and loser from a another match
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 */
    public function auto(){
    	global $wpdb,$poule_validation;
		        
        $phase = (isset($_GET['phase'])) ? $_GET['phase'] : 'groep';
        $this->phaseinfo = get_term_by('slug', $phase, 'phase');
        
        $term_meta = Poule_Get_Phase_Meta($this->phaseinfo->term_id);
        $groups = array();
        
        $input = array();
        $errors = array();
        
        $phases = poule_get_phases();
        
        $select_groups = array();
        $phase_id = $this->phaseinfo->term_id - 1;
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s'", $phase_id),ARRAY_A) as $info){
            $select_groups[] = array('name' => $info['group_name'],'id' => $info['id'],'matches' => $term_meta->matches_per_group);
        }
        
        $this->group_names = $select_groups;
        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            
            $args = array(
            	'name' => array('required' => TRUE, 'type'=>'string'),
            	'matches' => array(
            		'date' => array('required' => TRUE, 'type'=>'date'),
                	'time' => array('required' => TRUE, 'type'=>'time'),
                	'country1' => array('required' => TRUE, 'type'=>'auto_country'),
            		'country2' => array('required' => TRUE, 'type'=>'auto_country'),
            	),
            );

            $poule_validation->validate_input('group', $args, $this);
            $errors = $poule_validation->errors['group'];
            $input = $poule_validation->input['group'];
            if($poule_validation->counterrors['group'] == 0){
                
                $place = array();
                $groups_new = array();

                foreach ($wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s'",$phase_id),ARRAY_A) as $group_number => $group) {
                    $groups_new[$group['id']] = array('row' => $group_number);							
                    $place[$group['id']] = array();

                    foreach ($wpdb->get_results($wpdb->prepare("SELECT id,country_1,country_2,score FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $value) {
                        $match_score = unserialize($value['score']);
                        $countries = array('country_1' => $value['country_1'], 'country_2' => $value['country_2']);
                        $groups_new[$group['id']][$value['id']] = array_merge($countries, $match_score);
                    }
                }
                
                foreach ($groups_new as $group_id => $value) {
                    foreach ($value as $match) {
                        if ($phase_id == $phases[0]->term_id) {
                            $won = 0;
                            $equal = 0;
                            $lost = 0;
                            $positive = 0;
                            $negative = 0;

                            if ($match['score_1'] > $match['score_2']) {
                                $won++;
                            } else if ($match['score_1'] == $match['score_2']) {
                                $equal++;
                            } else {
                                $lost++;
                            }
                            $points = $won * 3 + $equal;
                            if (array_key_exists($match['country_1'], $place[$group_id])) {
                                $place[$group_id][$match['country_1']]['played'] ++;
                                $place[$group_id][$match['country_1']]['won'] += $won;
                                $place[$group_id][$match['country_1']]['equal'] += $equal;
                                $place[$group_id][$match['country_1']]['lost'] += $lost;
                                $place[$group_id][$match['country_1']]['points'] += $points;

                                $place[$group_id][$match['country_1']]['positive'] += $match['score_1'];
                                $place[$group_id][$match['country_1']]['negative'] += $match['score_2'];

                                $goal_difference = $place[$group_id][$match['country_1']]['positive'] - $place[$group_id][$match['country_1']]['negative'];
                                $place[$group_id][$match['country_1']]['goal_difference'] = $goal_difference;

                                $points2 = $place[$group_id][$match['country_1']]['points'] + $goal_difference / 100 + $place[$group_id][$match['country_1']]['positive'] / 1000;
                                $place[$group_id][$match['country_1']]['points2'] = $points2;
                            } else {
                                $place[$group_id][$match['country_1']] = array('played' => 1, 'won' => $won, 'equal' => $equal, 'lost' => $lost, 'points' => $points, 'positive' => $match['score_1'], 'negative' => $match['score_2']);
                            }

                            $won = 0;
                            $equal = 0;
                            $lost = 0;
                            $positive = 0;
                            $negative = 0;

                            if ($match['score_2'] > $match['score_1']) {
                                $won++;
                            } else if ($match['score_1'] == $match['score_2']) {
                                $equal++;
                            } else {
                                $lost++;
                            }
                            $points = $won * 3 + $equal;
                            if (array_key_exists($match['country_2'], $place[$group_id])) {
                                $place[$group_id][$match['country_2']]['played'] ++;
                                $place[$group_id][$match['country_2']]['won'] += $won;
                                $place[$group_id][$match['country_2']]['equal'] += $equal;
                                $place[$group_id][$match['country_2']]['lost'] += $lost;
                                $place[$group_id][$match['country_2']]['points'] += $points;

                                $place[$group_id][$match['country_2']]['positive'] += $match['score_2'];
                                $place[$group_id][$match['country_2']]['negative'] += $match['score_1'];

                                $goal_difference = $place[$group_id][$match['country_2']]['positive'] - $place[$group_id][$match['country_2']]['negative'];
                                $place[$group_id][$match['country_2']]['goal_difference'] = $goal_difference;

                                $points2 = $place[$group_id][$match['country_2']]['points'] + $goal_difference / 100 + $place[$group_id][$match['country_2']]['positive'] / 1000;
                                $place[$group_id][$match['country_2']]['points2'] = $points2;
                            } else {
                                $place[$group_id][$match['country_2']] = array('played' => 1, 'won' => $won, 'equal' => $equal, 'lost' => $lost, 'points' => $points, 'positive' => $match['score_2'], 'negative' => $match['score_1']);
                            }
                        } else {
                            if ($match['score_1'] > $match['score_2']) {
                                $place[$group_id][$match['country_1']]['points2'] = 20;
                                $place[$group_id][$match['country_2']]['points2'] = 10;
                            } else if ($match['score_2'] > $match['score_1']) {
                                $place[$group_id][$match['country_1']]['points2'] = 10;
                                $place[$group_id][$match['country_2']]['points2'] = 20;
                            } else if ($match['score_2'] == $match['score_1']) {
                                if ($match['penalty_1'] == $match['penalty_2']) {
                                    $place[$group_id][$match['country_1']]['points2'] = 20;
                                    $place[$group_id][$match['country_2']]['points2'] = 20;
                                } else if ($match['penalty_1'] > $match['penalty_2']) {
                                    $place[$group_id][$match['country_1']]['points2'] = 20;
                                    $place[$group_id][$match['country_2']]['points2'] = 10;
                                } else if ($match['penalty_1'] < $match['penalty_2']) {
                                    $place[$group_id][$match['country_1']]['points2'] = 10;
                                    $place[$group_id][$match['country_2']]['points2'] = 20;
                                }
                            }
                        }
                    }
                }
                
                foreach ($poule_validation->input['group'] as $key => $group) {
                    $insert_group = array();
                    $insert_group['group_name'] = $group['name'];
                    $insert_group['phase'] = $this->phaseinfo->term_id;

                    $group_id = $wpdb->insert($wpdb->prefix."poule_matches_groups", $insert_group);
					
					$group_id = $wpdb->insert_id;
					
					foreach($group['matches'] as $id => $match){
						
						$date_sec = strtotime($match['date']);
						
						$insert_matches = array(
							'group_id' => $group_id,
							'start_date' => date("Y-m-d", $date_sec) . ' ' . $match['time'],
							'country_1' => $this->Get_correct_country_id($place,$match['country1']),
							'country_2' => $this->Get_correct_country_id($place,$match['country2']),
							'score' => 'a:4:{s:7:"score_1";s:0:"";s:7:"score_2";s:0:"";s:9:"penalty_1";s:0:"";s:9:"penalty_2";s:0:"";}',
						);
						
						$old_group = explode('_', $match['country1']);
						
						$wpdb->insert($wpdb->prefix."poule_matches", $insert_matches);
					}
                }
            }
        }
        
        for($g = 1; $g <= $term_meta->groups; $g++){
        	$matches = new create_table('matches','auto');
        	
        	$data = array();
        	for($i = 1; $i <= $term_meta->matches_per_group; $i++){
        		if(!isset($errors[$g]['matches'][$i])){
        			$data_error = array();
        		}else{
					$data_error = $errors[$g]['matches'][$i];
				}
        		if(!isset($input[$g]['matches'][$i])){
        			$data_input = array();
        		}else{
					$data_input = $input[$g]['matches'][$i];
				}
            	$data[$i] = array('date' => '', 'time' => '', 'country1' => '' , 'country2' => '','row' => $i, 'group_id' => $g,'error' => $data_error, 'input' => $data_input);
        	}
        	
        	$matches->data = $data;
			$matches->columns = array('date' => 'date', 'time' => 'time', 'country1' => 'country1', '-' => '-' ,'country2' => 'country2');
            $matches->prepare_items();
            
			if(!isset($errors[$g]['name'])){
				$data_error = "";
			}else{
				$data_error = $errors[$g]['name'];
			}
			if(!isset($input[$g]['name'])){
				$data_input = "";
			}else{
				$data_input = $input[$g]['name'];
			}
            $groups[$g] = array('matches' => $matches, 'name' => array('group_id' => $g, 'error' => $data_error, 'input' => $data_input));
        }
        
        include_once $this->template;
	}
	    
    /**
	 * Get country id
	 * 
	 * check if the county exist for automatc matches page
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validation class
	 * @param array $place the groups
	 * @param string $input the input string
	 * @return string with the country id
	 */
    private function Get_correct_country_id($place,$input){
        $old_group = explode('_', $input);

        foreach ($place[$old_group[0]] as $key => $value) {
            $place[$old_group[0]][$key]['country'] = $key;
        }
        $podium = $this->array_sort_by_column($place[$old_group[0]], 'points2', SORT_DESC);
		
        return $podium[$old_group[1]]['country'];
    }
    
	/**
	 * sort a multi array 
	 * 
	 * sort a multi array
	 * 
	 * @access public
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
    
	/**
	 * get table column start time
	 * 
	 * get the starttime from the array
	 *  
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements for the match
	 * @return string the starttime
	 */
    public function column_start_time($item){
        return $item['start_time'];
    }
	
	/**
	 * create column date
	 * 
	 * Create the column date
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements for the match to create the field
	 * @return string input field
	 */
    public function column_date($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['date'] == 1)? "form-error" : "";
        }
        
        $input = (isset($item['input']['date']) && $item['input']['date'] != "") ? $item['input']['date'] : $item['date'];
        
		return '<input type="date" class="'.$error.'" id="match_" name="match['.$item['row'].'][date]" value="'.$input.'"/>';
    }
    
	/**
	 * create column time
	 * 
	 * Create the column time
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements for the match to create the field
	 * @return string input field
	 */
    public function column_time($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['time'] == 1)? "form-error" : "";
        }
        
        $input = (isset($item['input']['time']) && $item['input']['time'] != "") ? $item['input']['time'] : $item['time'];
        
        return '<input type="time" class="'.$error.'" id="match_" name="match['.$item['row'].'][time]" value="'.$input.'"/>';
        
    }
    
	/**
	 * Create input country 1
	 * 
	 * Create the drop down to select the country for the match
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements for the match to create the field
	 * @return string input field
	 */
    public function column_country1($item){
        $error = '';
        if(isset($item['error']['country1'])){
            $error = ($item['error']['country1'] == 1)? "form-error" : "";
        }
        
        $input = (isset($item['input']['country1']) && $item['input']['country1'] != "") ? $item['input']['country1'] : $item['country1'];
        
        $return = '<select name="match['.$item['row'].'][country1]" class="'.$error.'">';
		$return .= '<option value=""></option>';
        foreach($this->countries as $country){
            $select = ($country->ID == $input) ? 'selected="selected"' : "";
            $return .= '<option value="'. $country->ID .'" '.$select.'>'. $country->post_title .'</option>';
        }
        $return .='</select>';
		return $return;
    }
    
	/**
	 * Create input country 2
	 * 
	 * Create the drop down to select the country for the match
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements of the match to create the field
	 * @return string input field
	 */
    public function column_country2($item){
        $error = '';
        if(isset($item['error']['country2'])){
            $error = ($item['error']['country2'] == 1)? "form-error" : "";
        }
        
        $input = (isset($item['input']['country2']) && $item['input']['country2'] != "") ? $item['input']['country2'] : $item['country2'];
        
        $return = '<select name="match['.$item['row'].'][country2]" class="'.$error.'">';
		$return .= '<option value=""></option>';
        foreach($this->countries as $country){
            $select = ($country->ID == $input) ? 'selected="selected"' : "";
            $return .= '<option value="'. $country->ID .'" '.$select.'>'. $country->post_title .'</option>';
        }
        $return .='</select>';
		return $return;
    }
    
	/**
	 * create column country 1
	 * 
	 * gett he country for column 1
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements of the match
	 * @return string column value
	 */
    public function home_column_country1($item){
        return $item['country1'];
    }
    
	/**
	 * Return the country 2 column
	 * 
	 * Get and return the country 2 column
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array of available items for a match
	 * @return string the country name
	 */
    public function home_column_country2($item){
        return $item['country2'];
    }
    
	/**
	 * Create input time
	 * 
	 * Create the time filed for create automatic the matches page
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements of the match to create the field
	 * @return string input field
	 */
    public function auto_column_time($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['time'] == 1)? "form-error" : "";
        }
        
        $input = (isset($item['input']['time']) && $item['input']['time'] != "") ? $item['input']['time'] : $item['time'];
        
        return '<input type="time" class="'.$error.'" id="match_" name="group['.$item['group_id'].'][matches]['.$item['row'].'][time]" value="'.$input.'"/>';
       
    }
	
	/**
	 * Create input time
	 * 
	 * Create the date filed for create automatic the matches page
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements of the match to create the field
	 * @return string input field
	 */
    public function auto_column_date($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['date'] == 1) ? "form-error" : "";
        }
        
        $input = (isset($item['input']['date']) && $item['input']['date'] != "") ? $item['input']['date'] : $item['date'];
        
		return '<input type="date" class="'.$error.'" id="match_" name="group['.$item['group_id'].'][matches]['.$item['row'].'][date]" value="'.$input.'"/>';
    }
	
	/**
	 * Create input country 1
	 * 
	 * Create the drop down to select the country for the match. it's groupt by the group
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements of the match to create the field
	 * @return string input field
	 */
    public function auto_column_country1($item){
        $error = '';
        if(isset($item['error']['country1'])){
            $error = ($item['error']['country1'] == 1)? "form-error" : "";
        }
        
        $input = (isset($item['input']['country1']) && $item['input']['country1'] != "") ? $item['input']['country1'] : $item['country1'];
        
        $return = '<select name="group['.$item['group_id'].'][matches]['.$item['row'].'][country1]" class="'.$error.'">';
		$return .= '<option value=""></option>';
        
		foreach($this->group_names as $group){
            $return .= '<optgroup label="'.$group['name'].'">';
            $select_1 = '';
            $select_2 = '';

			$phase_id = $this->phaseinfo->term_id - 1;
			
			$first = (Poule_Get_Phase_Meta($phase_id,'matches_per_group') != 1) ? __('First', 'poule-tournament') : __('Winnar', 'poule-tournament');
            $Second = (Poule_Get_Phase_Meta($phase_id,'matches_per_group') != 1) ? __('Second', 'poule-tournament') : __('Loser', 'poule-tournament');
            
            $return .= '<option value="'. $group['id'] .'_0" '.$select_1.'>'. $first . ' ' . $group['name'] .'</option>';
            $return .= '<option value="'. $group['id'] .'_1" '.$select_2.'>'. $Second . ' ' . $group['name'] .'</option>';

            $return .= '</optgroup>';
		}
		$return .='</select>';
		return $return;
        
    }
    
	/**
	 * Create input country 1
	 * 
	 * Create the drop down to select the country for the match. it's groupt by the group
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with the elements of the match to create the field
	 * @return string input field
	 */
    public function auto_column_country2($item){
        $error = '';
        if(isset($item['error']['country1'])){
            $error = ($item['error']['country1'] == 1)? "form-error" : "";
        }
        
        $input = (isset($item['input']['country1']) && $item['input']['country1'] != "") ? $item['input']['country1'] : $item['country1'];
        
        $return = '<select name="group['.$item['group_id'].'][matches]['.$item['row'].'][country2]" class="'.$error.'">';
		$return .= '<option value=""></option>';
        
		foreach($this->group_names as $group){
            $return .= '<optgroup label="'.$group['name'].'">';
            $select_1 = '';
            $select_2 = '';
            
			$phase_id = $this->phaseinfo->term_id - 1;
			
			$first = (Poule_Get_Phase_Meta($phase_id,'matches_per_group') != 1) ? __('First', 'poule-tournament') : __('Winnar', 'poule-tournament');
            $Second = (Poule_Get_Phase_Meta($phase_id,'matches_per_group') != 1) ? __('Second', 'poule-tournament') : __('Loser', 'poule-tournament');
            
            $return .= '<option value="'. $group['id'] .'_0" '.$select_1.'>'. $first . ' ' . $group['name'] .'</option>';
            $return .= '<option value="'. $group['id'] .'_1" '.$select_2.'>'. $Second . ' ' . $group['name'] .'</option>';
			
            $return .= '</optgroup>';
			
		}
		$return .='</select>';
		return $return;
    }
    
	/**
	 * validate country auto page
	 * 
	 * check if the country exist on the create auto match page
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $value the input value
	 * @return bool validation results
	 */
    public function validate_auto_country($value){
        $check = explode('_', $value);

		if($check[1] != 0 || $check[1] != 1){
			return FALSE;
		}
		foreach($this->group_names as $group){
			if($check[0] == $group['id']){
				return FALSE;
			}
		}
        
        return FALSE;
    }
    
	/**
	 * validate countrie name
	 * 
	 * check if the country exist
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $value input value
	 * @return bool validation result
	 */
    public function validate_country($value){
        if(get_post($value) == null){
            return true;
        }
    }
} 

?>