<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Description of score
 *
 * @author Stefan
 */
class score {
    
    public $template;
    
    public $create_table;
    
    public function __construct() {
        $function = (isset($_GET['function']))?$_GET['function']:"home";
        $this->template = POULE_PATH_2 . 'poule-admin/template/score/' . $function . ".php";
        
        add_filter('poule_table_score_column_country1', array($this,'column_country1'),10,1);
        add_filter('poule_table_score_column_country2', array($this,'column_country2'),10,1);
        add_filter('poule_table_score_column_score1', array($this,'column_score1'),10,1);
        add_filter('poule_table_score_column_score2', array($this,'column_score2'),10,1);
        
        require_once(POULE_PATH_2 . 'classes/create-table.php');
    }
    
    public function init(){
        //check function
        $possible = array('home');
        $functionname = (isset($_GET['function'])) ? $_GET['function'] : 'home';
        if(in_array($functionname, $possible)){
        	$this->$functionname(); 
        }
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
        
        $error = array();
        $input = array();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $validation = new form_validation($_POST);
            $args = array(
                'score1' => array('required' => FALSE, 'type'=>'int'),
                'score2' => array('required' => FALSE, 'type'=>'int'),
                'penalty1' => array('required' => FALSE, 'type'=>'int'),
                'penalty2' => array('required' => FALSE, 'type'=>'int'),
            );

            $validation->validate_input('score', $args, $this);
            $error = $validation->errors['score'];
            $input = $validation->input['score'];
            $count_error = $validation->counterrors;
            
            //var_dump($error);
            //var_dump($input);
            if($count_error == 0){
                foreach($input as $key => $match){
                    $score1 = ($match['score1'] != "") ? $match['score1'] : 0;
                    $score2 = ($match['score2'] != "") ? $match['score2'] : 0;

                    if($score1 === $score2 && $score1 != 0){
                        $penalty1 = ($match['penalty1'] != "") ? $match['penalty1'] : 0;
                        $penalty2 = ($match['penalty2'] != "") ? $match['penalty2'] : 0;
                    }else{
                        $penalty1 = 0;
                        $penalty2 = 0;
                    }

                    $update = array(
                        'score_1' => $score1,
                        'score_2' => $score2,
                        'penalty_1' => $penalty1,
                        'penalty_2' => $penalty2,
                    );

                    $wpdb->update($wpdb->prefix.'poule_matches',array('score' => serialize($update)),array('id' => $key));
                }
            }
        }
        
        $groups = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $term->term_id),ARRAY_A) as $rowid => $group){
            $matches = array();
            
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $id => $match) {
            	$starttime = date("d-m-Y H:i:s",$match['start_time']);
                $country1 = get_post($match['country_1']);
                $country2 = get_post($match['country_2']);
                
                $score = unserialize($match['score']);
            	
                $matchrow = array(
            		'start_time' => $starttime,
            		'country1' => $country1->post_title,
            		'country2' => $country2->post_title,
                    'score1' => $score['score_1'],
                    'score2' => $score['score_2'],
                    'penalty1' => $score['penalty_1'],
                    'penalty2' => $score['penalty_2'],
                    'row' => $match['id'],
            	);
            	
                $matches[$match['id'] . 1] = $matchrow;
            }
            //var_dump($matches);
            $this->create_table = new create_table('score','home');
            $this->create_table->columns = array('country1' => 'country1', 'score1' => 'score1', '-' => '-', 'score2' => 'score2' ,'country2' => 'country2');
            $this->create_table->data = $matches;
            $this->create_table->prepare_items();
            
            $groups[$group['id']] = array('group_name' => $group['group_name'],'matches' => $this->create_table);
        }
        
        include_once $this->template;
    }
    
    public function column_country1($item){
        //return $item['country1'];
        return ($this->create_table->penalty == FALSE)?$item['country1']:'';
    }
    
    public function column_country2($item){
        //return $item['country2'];
        return ($this->create_table->penalty == FALSE)?$item['country2']:'';
    }
    
    public function column_score1($item){
        $start = strtotime($item['start_time']) + 6300;
        $readonly = ($start <= time()) ? '' : 'readonly="readonly"';
        
        $penalty = FALSE;
        if($this->create_table->penalty === FALSE){
			$score = (isset($item['input']['score1']) && $item['input']['score1'] != "") ? $item['input']['score1'] : $item['score1'];
			$error = (isset($item['error']['score1']))?$item['error']['score1']:"";
			
            $onkeyup = ' onkeyup="penalties(\''.$item['row'].'\', \'1\')"';
            
			$return = '<input type="number" min="0" max="20" name="score['.$item['row'].'][score1]" class="'.$error.'" id="score_'.$item['row'].'_1" value="'.$score.'" '.$readonly.' '.$onkeyup.' />';
			return $return;
		}else{
			$error = (isset($item['error']['penalty1']))?$item['error']['penalty1']:"";
			
			$score = (isset($item['input']['penalty1']) && $item['input']['penalty1'] != "") ? $item['input']['penalty1'] : $item['penalty1'];
            
			$return = '<div id="penalties_' . $item['row'] . '_1">';
			
			$return .= '<input type="number" min="0" max="5" name="score['.$item['row'] .'][penalty1]" class="'.$error.'" '.$readonly .' value="'. $score .'"/>';
			
			$return .= '</div>';
			
			return $return;
		}
    }
    
    public function column_score2($item){
        $start = strtotime($item['start_time']) + 6300;
        $readonly = ($start <= time()) ? '' : 'readonly="readonly"';
        
        $penalty = FALSE;
        if($this->create_table->penalty === FALSE){
			$score = (isset($item['input']['score2']) && $item['input']['score2'] != "") ? $item['input']['score2'] : $item['score2'];
			$error = (isset($item['error']['score2']))?$item['error']['score2']:"";
			
            $onkeyup = ' onkeyup="penalties(\''.$item['row'].'\', \'2\')"';
            
			$return = '<input type="number" min="0" max="20" name="score['.$item['row'] .'][score2]" class="'.$error.'" id="score_'.$item['row'] .'_2" value="'.$score .'" '.$readonly . ' ' .$onkeyup.' />';
			return $return;
		}else{
			$error = (isset($item['error']['penalty2']))?$item['error']['penalty2']:"";
			
			$score = (isset($item['input']['penalty2']) && $item['input']['penalty2'] != "") ? $item['input']['penalty2'] : $item['penalty2'];
            
			$return = '<div id="penalties_' . $item['row'] . '_1">';
			
			$return .= '<input type="number" min="0" max="5" name="score['.$item['row'] .'][penalty2]" class="'.$error.'" '.$readonly .' value="'. $score .'"/>';
			
			$return .= '</div>';
			
			return $return;
		}
    }
}