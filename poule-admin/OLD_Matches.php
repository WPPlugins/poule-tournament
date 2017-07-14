<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Description of matches
 *
 * @author Stefan
 */

class matches {
    
    public $template;
    
    public $countries;
    
    public $phaseinfo;
    
    public $validation;
    
    public $penalty = FALSE;
    
    public function __construct() {
        $function = (isset($_GET['function']))?$_GET['function']:"home";
        $this->template = POULE_PATH_2 . 'poule-admin/template/matches/' . $function . ".php";
                
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
        
        $this->countries = $this->get_countries();
        
        require_once(POULE_PATH_2 . 'classes/create-table.php');
    }
    
    public function init(){
        //check function
        $possible = array('home','add','edit','auto');
        $functionname = (isset($_GET['function'])) ? $_GET['function'] : 'home';
        if(in_array($functionname, $possible)){
        	$this->$functionname(); 
        }
        
        add_action('admin_head', array($this,'add_help'));
        //help tab toevoegen
        
        $screen = get_current_screen();
    	
    	$help = array(
                0 => array(
                    'title' => __('Add','poule-tournament'),
                    'content' => 
                        '<h3>'.__('Green','poule-tournament').'</h3><p>'.__('The group and matches are saved.','poule-tournament').'<?p>'
                        .'<h3>'.__('Red','poule-tournament').'</h3><p>'.__('There is a error.<ul><li>Wrong or no group name</li><li>Wrong date or time</li></ul>','poule-tournament').'</p>'
                ),
                1 => array(
                    'title' => __('Edit','poule-tournament'),
                    'content' => 
                        '<h3>'.__('Green','poule-tournament').'</h3><p>'.__('The group and matches are saved.','poule-tournament').'<?p>'
                        .'<h3>'.__('Red','poule-tournament').'</h3><p>'.__('There is a error. And the saved date will be show.<ul><li>Wrong or no group name</li><li>Wrong date or time</li></ul>','poule-tournament').'</p>'
                ),
                2 => array(
                    'title' => __('Automatic','poule-tournament'),
                    'content' => 
                        '<h3>'.__('Information','poule-tournament').'</h3><p>'.__('Create fast all the matches for the next phase.','poule-tournament').'<?p>'
                        .'<h3>'.__('Green','poule-tournament').'</h3><p>'.__('The changes are saved.','poule-tournament').'<?p>'
                        .'<h3>'.__('Red','poule-tournament').'</h3><p>'.__('There is a error. And the saved date will be show.<ul><li>Wrong or no group name</li><li>Wrong date or time</li></ul>','poule-tournament').'</p>'
                )
    	);
    	
        foreach($help as $key => $line){
            $screen->add_help_tab( array(
                'id' => $_GET['page'] . '_id_' . $key,
                'title' => $line['title'],
            	'content' => $line['content'],
        	));
		}
    }
    
    public function add_help(){
    	global $post_ID;
        
        
    	
    }
    
    public function home(){
        global $wpdb;
        
        $taxonomyname = "phase";
        $args = array(
            'hide_empty' => '0',
            'hierarchical' => '0',
            'parent' => '0',
            'orderby' => 'id',
            'order' => 'ASC'
        );
        $terms = get_terms($taxonomyname, $args);
        
        $phase = (isset($_GET['phase'])) ? $_GET['phase'] : $terms[0]->slug;
        
        $term = get_term_by('slug', $phase, $taxonomyname);
        
        $groups = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $term->term_id),ARRAY_A) as $rowid => $group){
            $matches = array();
            
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $id => $match) {
            	$starttime = date("d-m-Y H:i:s",$match['start_time']);
                $country1 = get_post($match['country_1']);
                $country2 = get_post($match['country_2']);
            	$matchrow = array(
            		'start_time' => $starttime,
            		'country1' => $country1->post_title,
            		'country2' => $country2->post_title,
            	);
            	
                $matches[$match['id']] = $matchrow;
            }
            //var_dump($matches);
            $table_match = new create_table('matches','home');
            $table_match->columns = array('start_time' => 'start_time', 'country1' => 'country1', '-' => '-' ,'country2' => 'country2');
            $table_match->data = $matches;
            $table_match->prepare_items();
            
            $groups[$group['id']] = array('group_name' => $group['group_name'],'matches' => $table_match);
        }
        
        include_once $this->template;
    }
    
     public function add(){
        global $wpdb;
        
        $table_match = new create_table('matches','add');
        
        $phase = (isset($_GET['phase'])) ? $_GET['phase'] : 'groep';
        $this->phaseinfo = get_term_by('slug', $phase, 'phase');
        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $validation = new form_validation($_POST);
            
            $args = array(
            	'group_name' => array('required' => TRUE, 'type' => 'string')
            );
            
            $validation->validate_input("group_name",$args,$this);
            $errors_name = $validation->errors['group_name'];
            $input_name = $validation->input['group_name'];
            $count_error_name = $validation->counterrors;
            
            
            $args = array(
                'date' => array('required' => TRUE, 'type'=>'date'),
                'time' => array('required' => TRUE, 'type'=>'time'),
                'country1' => array('required' => TRUE, 'type'=>'country'),
                'country2' => array('required' => TRUE, 'type'=>'country'),
            );

            $validation->validate_input('match', $args, $this);
            $errors_match = $validation->errors['match'];
            $input_match = $validation->input['match'];
            $count_error_match = $validation->counterrors;
            //data opslaan
            
            //var_dump($errors_match);
            if($count_error_match == 0 && $count_error_name == 0){
                //groeps naam
				$wpdb->insert($wpdb->prefix."poule_matches_groups",array('phase'=>$this->phaseinfo->term_id,'group_name'=>$input_name));
            	$groupsid = $wpdb->insert_id;
				
                foreach($input_match as $key => $value){
                    $sec = strtotime($value['date']);
                    $starttime = date('d-m-Y', $sec) . ' ' . $value['time'];
                    
                    $wpdb->insert(
                        $wpdb->prefix."poule_matches",
                        array(
                            'group_id'=>$groupsid,
                            'start_time' => strtotime($starttime),
                            'country_1' => $value['country1'],
                            'country_2' => $value['country2'],
                            'score' => 'a:4:{s:7:"score_1";s:0:"";s:7:"score_2";s:0:"";s:9:"penalty_1";s:0:"";s:9:"penalty_2";s:0:"";}'));
                }
                
//                wp_safe_redirect( '?post_type=country&page=matches&function=edit&phase=groep&groupid='.$groupsid,200); 
                echo'<script> window.location="?post_type=country&page=matches&function=edit&phase=groep&groupid='.$groupsid.'"; </script> ';
                exit;
            }
        }else{
            $errors_match = array('1' => array(),'2' => array(),'3' => array(),'4' => array(),'5' => array(),'6' => array(),);
            $input_match = array('1' => array(),'2' => array(),'3' => array(),'4' => array(),'5' => array(),'6' => array(),);
        }
        
        $data = array();
        $term_meta = get_option( "taxonomy_".$this->phaseinfo->term_id );
        
        for($i = 1; $i <= $term_meta['matches_one_group']; $i++){
        	$data[$i] = array('date' => '', 'time' => '', 'country1' => '' , 'country2' => '','row' => $i, 'error' => $errors_match[$i], 'input' => $input_match[$i]);
        }
        //stefan_dev356
        //qBWecGij35up
        
        $table_match->data = $data;
        $table_match->columns = array('date' => 'date', 'time' => 'time', 'country1' => 'country1', '-' => '-' ,'country2' => 'country2');
		$table_match->prepare_items();
        
        include_once $this->template;
    }
    
    public function edit(){
    	global $wpdb;
    	
    	$phase = (isset($_GET['phase'])) ? $_GET['phase'] : '';
        $this->phaseinfo = get_term_by('slug', $phase, 'phase');

		$groupsid = (isset($_GET['groupid'])) ? $_GET['groupid'] : '';

		$groupsinfo = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE id='%s' AND phase='%s'", $groupsid, $this->phaseinfo->term_id),ARRAY_A);
		
		if($groupsinfo != null){
			$groupsnaam = $groupsinfo['group_name'];
            
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $validation = new form_validation($_POST);
                
                $args = array(
                    'group_name' => array('required' => TRUE, 'type' => 'string')
                );

                $validation->validate_input("group_name",$args,$this);
                $errors_name = $validation->errors['group_name'];
                $input_name = $validation->input['group_name'];
                $count_error_name = $validation->counterrors;


                $args = array(
                    'date' => array('required' => TRUE, 'type'=>'date'),
                    'time' => array('required' => TRUE, 'type'=>'time'),
                    'country1' => array('required' => TRUE, 'type'=>'country'),
                    'country2' => array('required' => TRUE, 'type'=>'country'),
                );

                $validation->validate_input('match', $args, $this);
                $error_match = $validation->errors['match'];
                $input_match = $validation->input['match'];
                $count_error_match = $validation->counterrors;
                
                $groupsnaam = ($input_name != "") ? $input_name : "";
                
                if($count_error_match == 0 && $count_error_name == 0){
                    //groeps naam
                    $wpdb->update($wpdb->prefix."poule_matches_groups",array('phase'=>$this->phaseinfo->term_id,'group_name'=>$input_name),array('id' => $groupsid));
                    $groupsid = $wpdb->insert_id;

                    foreach($input_match as $key => $value){
                        $sec = strtotime($value['date']);
                        $starttime = date('d-m-Y', $sec) . ' ' . $value['time'];

                        $wpdb->update(
                            $wpdb->prefix."poule_matches",
                            array(
                                'start_time' => strtotime($starttime),
                                'country_1' => $value['country1'],
                                'country_2' => $value['country2'],
                            ),
                            array(
                                'id' => $key
                            )
                        );
                    }
                }
                
            }else{
                $error_match = array();
                $input_name = array();
            }
            
			$matches = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$groupsinfo['id']),ARRAY_A);
			
			$data = array();
			foreach($matches as $id => $match){
				$date = date("Y-m-d",$match['start_time']);
				$time = date("H:i",$match['start_time']);
                
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
            include_once POULE_PATH_2 . 'poule-admin/template/error.php';
        }
    }
  
    public function auto(){
    	global $wpdb;
        
        //$table_match = new create_table('matches','add');
        
        $phase = (isset($_GET['phase'])) ? $_GET['phase'] : 'groep';
        $this->phaseinfo = get_term_by('slug', $phase, 'phase');
        
        $term_meta = get_option( "taxonomy_".$this->phaseinfo->term_id );
        
        $groups = array();
        
        $input = array();
        $errors = array();
        
        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
        	$validation = new form_validation($_POST);
            //var_dump($_POST['group']);
            
            $args = array(
            	'name' => array('required' => TRUE, 'type'=>'string'),
            	'matches' => array(
            		'date' => array('required' => TRUE, 'type'=>'date'),
                	'time' => array('required' => TRUE, 'type'=>'time'),
                	'country1' => array('required' => TRUE, 'type'=>'country'),
            		'country2' => array('required' => TRUE, 'type'=>'country'),
            	),
            );

            $validation->validate_input('group', $args, $this);
            //$error_match = $validation->errors['group'];
        	//$input_match = $validation->input['group'];
            $count_error_match = $validation->counterrors;
        }
        
        for($g = 1; $g <= $term_meta['number_groups']; $g++){
        	$matches = new create_table('matches','auto');
        	
        	$data = array();
        	for($i = 1; $i <= $term_meta['matches_one_group']; $i++){
        		if(!isset($errors[$g]['matches'][$i])){
        			$errors[$g]['matches'][$i] = array();
        		}
        		if(!isset($input[$g]['matches'][$i])){
        			$input[$g]['matches'][$i] = array();
        		}
            	$data[$i] = array('date' => '', 'time' => '', 'country1' => '' , 'country2' => '','row' => $i, 'group_id' => $g,'error' => $errors[$g]['matches'][$i], 'input' => $input[$g]['matches'][$i]);
        	}
        	
        	$matches->data = $data;
			$matches->columns = array('date' => 'date', 'time' => 'time', 'country1' => 'country1', '-' => '-' ,'country2' => 'country2');
            $matches->prepare_items();
            
            if(!isset($errors[$g]['name'])){
        		$errors[$g]['name'] = "";
        	}
       		if(!isset($input[$g]['name'])){
       			$input[$g]['name'] = "";
       		}
       		
            $groups[$g] = array('matches' => $matches, 'name' => array('group_id' => $g, 'error' => $errors[$g]['name'], 'input' => $input[$g]['name']));
        }
        
        
        include_once $this->template;
	}
	
 	public function validate_country($value){
        if(get_post($value) == null){
            return true;
        }
    }
    
    public function column_start_time($item){
        return $item['start_time'];
    }
//     input alleen gebruiken na wijzigingen en er een veld fout is
//	   normaal gewoon column naam
    public function column_date($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['date'] == 1)? $item['error']['date'] : "";
        }
        
        $input = (isset($item['input']['date']) && $item['input']['date'] != "") ? $item['input']['date'] : $item['date'];
        
		return '<input type="date" class="'.$error.'" id="match_" name="match['.$item['row'].'][date]" value="'.$input.'"/>';
    }
    
    public function column_time($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['time'] == 1)? $item['error']['time'] : "";
        }
        
        $input = (isset($item['input']['time']) && $item['input']['time'] != "") ? $item['input']['time'] : $item['time'];
        
        return '<input type="time" class="'.$error.'" id="match_" name="match['.$item['row'].'][time]" value="'.$input.'"/>';
        
    }
    
    private function get_countries(){
        $args = array(
            'post_type' => 'country',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        
        $countries = get_posts($args);
        return $countries;
    }
    
    public function column_country1($item){
        $error = '';
        if(isset($item['error']['country1'])){
            $error = ($item['error']['country1'] == 1)? $item['error']['country1'] : "";
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
     * 
     * @param type $item
     * @return string
     */
    public function column_country2($item){
        $error = '';
        if(isset($item['error']['country2'])){
            $error = ($item['error']['country2'] == 1)? $item['error']['country2'] : "";
        }
        
        $input = (isset($item['input']['country2']) && $item['input']['cvountry2'] != "") ? $item['input']['country2'] : $item['country2'];
        
        $return = '<select name="match['.$item['row'].'][country2]" class="'.$error.'">';
		$return .= '<option value=""></option>';
        foreach($this->countries as $country){
            $select = ($country->ID == $input) ? 'selected="selected"' : "";
            $return .= '<option value="'. $country->ID .'" '.$select.'>'. $country->post_title .'</option>';
        }
        $return .='</select>';
		return $return;
    }
    
    public function home_column_country1($item){
        return $item['country1'];
    }
    
    public function home_column_country2($item){
        return $item['country2'];
    }
    
    public function auto_column_country1($item){
        $error = '';
        if(isset($item['error']['country1'])){
            $error = ($item['error']['country1'] == 1)? $item['error']['country1'] : "";
        }
        
        $input = (isset($item['input']['country1']) && $item['input']['country1'] != "") ? $item['input']['country1'] : $item['country1'];
        
        $return = '<select name="group['.$item['group_id'].'][matches]['.$item['row'].'][country1]" class="'.$error.'">';
		$return .= '<option value=""></option>';
        foreach($this->countries as $country){
            $select = ($country->ID == $input) ? 'selected="selected"' : "";
            $return .= '<option value="'. $country->ID .'" '.$select.'>'. $country->post_title .'</option>';
        }
        $return .='</select>';
		return $return;
    }
    
    public function auto_column_country2($item){
        $error = '';
        if(isset($item['error']['country1'])){
            $error = ($item['error']['country1'] == 1)? $item['error']['country1'] : "";
        }
        
        $input = (isset($item['input']['country1']) && $item['input']['country1'] != "") ? $item['input']['country1'] : $item['country1'];
        
        $return = '<select name="group['.$item['group_id'].'][matches]['.$item['row'].'][country2]" class="'.$error.'">';
		$return .= '<option value=""></option>';
        foreach($this->countries as $country){
            $select = ($country->ID == $input) ? 'selected="selected"' : "";
            $return .= '<option value="'. $country->ID .'" '.$select.'>'. $country->post_title .'</option>';
        }
        $return .='</select>';
		return $return;
    }
    
    public function auto_column_date($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['date'] == 1)? $item['error']['date'] : "";
        }
        
        $input = (isset($item['input']['date']) && $item['input']['date'] != "") ? $item['input']['date'] : $item['date'];
        
		return '<input type="date" class="'.$error.'" id="match_" name="group['.$item['group_id'].'][matches]['.$item['row'].'][date]" value="'.$input.'"/>';
    }
    
    public function auto_column_time($item){
        $error = '';
        if(isset($item['error']['date'])){
            $error = ($item['error']['time'] == 1)? $item['error']['time'] : "";
        }
        
        $input = (isset($item['input']['time']) && $item['input']['time'] != "") ? $item['input']['time'] : $item['time'];
        
        return '<input type="time" class="'.$error.'" id="match_" name="group['.$item['group_id'].'][matches]['.$item['row'].'][time]" value="'.$input.'"/>';
       
    }
} 

?>