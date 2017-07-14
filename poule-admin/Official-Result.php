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
 * score pages class
 * 
 * Class with all the function to show the pages for score
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class Official_Result {
    
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's th folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @since version
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $template;
    
	/**
	 * Array with the matches
	 * 
	 * contains a array of all the matches
	 * 
	 * @access private
	 * @since version
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
	private $matches = array();
	
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
        $this->template = POULE_PATH . 'poule-admin/template/official-result/' . $function . ".php";
        
        add_filter('poule_table_official_result_column_country1', array($this,'column_country1'),10,1);
        add_filter('poule_table_official_result_column_country2', array($this,'column_country2'),10,1);
        add_filter('poule_table_official_result_column_score1', array($this,'column_score1'),10,1);
        add_filter('poule_table_official_result_column_score2', array($this,'column_score2'),10,1);
        
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
        //check function
        $possible = array('home');
        $functionname = (isset($_GET['function'])) ? $_GET['function'] : 'home';
        if(in_array($functionname, $possible)){
        	$this->$functionname(); 
        }
    }
    
	/**
	 * show the page
	 * 
	 * show the page for admin to set the official result and update the matches in the database
	 * 
	 * @global database $wpdb Object for sql queries
	 * @global form_validation $poule_validation validate the forms
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 1
	 * @version 2.2
	 */
    public function home(){
        global $wpdb, $poule_validation;
        
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
        
        $term = get_term_by('slug', $phase, $taxonomyname);
        $this->get_matches($term->term_id);
		$phases = poule_get_phases();
        $error = array();
        $input = array();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
          	
            $args = array(
                'score1' => array('required' => true, 'type'=>'score'),
                'score2' => array('required' => true, 'type'=>'score'),
                'penalty1' => array('required' => true, 'type'=>'penalty'),
                'penalty2' => array('required' => true, 'type'=>'penalty'),
            );

            $poule_validation->validate_input('score', $args, $this);
			
            if($poule_validation->counterrors['score'] == 0){
				
                foreach($poule_validation->input['score'] as $key => $match){
                    $score1 = ($match['score1'] != "") ? $match['score1'] : '';
                    $score2 = ($match['score2'] != "") ? $match['score2'] : '';
					
					$penalties = FALSE;
					
					foreach($phases as $phasesql){
						$meta = Poule_Get_Phase_Meta($phasesql->term_id, 'penalties');
						if(is_object($meta) && $meta->penalties != null && $meta->penalties != '0' && $phase == $phasesql->slug){
							$penalties = TRUE;
						}
					}
					
					$penalty1 = '';
					$penalty2 = '';
						
					if($penalties && $score1 === $score2 && $score1 != ''){
						$penalty1 = ($match['penalty1'] != "") ? $match['penalty1'] : 0;
                        $penalty2 = ($match['penalty2'] != "") ? $match['penalty2'] : 0;
					}
					
                    $update = array(
                        'score_1' => $score1,
                        'score_2' => $score2,
                        'penalty_1' => $penalty1,
                        'penalty_2' => $penalty2,
                    );

                    $wpdb->update($wpdb->prefix.'poule_matches',array('score' => serialize($update)),array('id' => $key));
                }
            }else{
				$error = $poule_validation->errors['score'];
				$input = $poule_validation->input['score'];
			}
        }

		
        date_default_timezone_set(get_option('timezone_string'));
        
        $groups = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $term->term_id),ARRAY_A) as $rowid => $group){
            $matches = array();
            
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $id => $match) {
				
            	$starttime = date("d-m-Y H:i:s",  strtotime($match['start_date']));
				
                $country1 = get_post($match['country_1']);
                $country2 = get_post($match['country_2']);
                
                $score = unserialize($match['score']);
            	
                $matchrow = array(
            		'start_time' => strtotime($match['start_date']),
            		'country1' => $country1->post_title,
            		'country2' => $country2->post_title,
                    'score1' => $score['score_1'],
                    'score2' => $score['score_2'],
                    'penalty1' => $score['penalty_1'],
                    'penalty2' => $score['penalty_2'],
                    'row' => $match['id'],
            	);
            	
				if(array_key_exists($match['id'], $error) && array_key_exists('score1', $error[$match['id']])){
					$matchrow['error']['score1'] = $error[$match['id']]['score1'];
				}
				if(array_key_exists($match['id'], $error) && array_key_exists('score2', $error[$match['id']])){
					$matchrow['error']['score2'] = $error[$match['id']]['score2'];
				}
				
				if(array_key_exists($match['id'], $input) && array_key_exists('score1', $input[$match['id']])){
					$matchrow['input']['score1'] = $input[$match['id']]['score1'];
				}
				if(array_key_exists($match['id'], $input) && array_key_exists('score2', $input[$match['id']])){
					$matchrow['input']['score2'] = $input[$match['id']]['score2'];
				}
				
                $matches[$match['id'] . 1] = $matchrow;
            }
			
			$table = new create_table('official_result','home');
			$table->columns = array('country1' => 'country1', 'score1' => 'score1', '-' => '-', 'score2' => 'score2' ,'country2' => 'country2');
            $table->data = $matches;
            $table->prepare_items();
            
            $groups[$group['id']] = array('group_name' => $group['group_name'],'matches' => $table);
        }
        
        include_once $this->template;
    }
	
    /**
	 * get country name
	 * 
	 * return the country name if penalty is false also ''
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item all the elements of a match
	 * @return string country name
	 */
    public function column_country1($item){
		
        return ($item['penalty'] == FALSE)?$item['country1']:'';
    }
    
	/**
	 * get country name
	 * 
	 * return the country name of if penalty is true ""
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item all the elements for a match
	 * @return string the country name
	 */
    public function column_country2($item){
        //return $item['country2'];
        return ($item['penalty'] == FALSE)?$item['country2']:'';
    }
    
	/**
	 * create input field
	 * 
	 * create the field for input check for readonly score|penalty
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item all the elements for a match
	 * @return string the score input
	 */
    public function column_score1($item){
        date_default_timezone_set(get_option('timezone_string'));
        
        $start = $item['start_time'] + 5400;
        
        $readonly = ($start <= time()) ? '' : 'readonly="readonly"';
		
        if($item['penalty'] === FALSE){
			$score = (isset($item['input']['score1']) && $item['input']['score1'] != "") ? $item['input']['score1'] : $item['score1'];
			$error = (isset($item['error']['score1']))? 'form-error':"";
			
			$return = '<input type="number" min="0" max="20" match_id="'.$item['row'].'" number="1" name="score['.$item['row'].'][score1]" class="inputresult '.$error.'" id="score_'.$item['row'].'_1" value="'.$score.'" '.$readonly.' />';
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
    
	/**
	 * create input field 
	 * 
	 * create the field for input check for readonly score|penalty
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item array with elements of a match
	 * @return the input field
	 */
    public function column_score2($item){
		date_default_timezone_set(get_option('timezone_string'));
        $start = $item['start_time'] + 6300;
        $readonly = ($start <= time()) ? '' : 'readonly="readonly"';
        
        $penalty = FALSE;
        if($item['penalty'] === FALSE){
			$score = (isset($item['input']['score2']) && $item['input']['score2'] != "") ? $item['input']['score2'] : $item['score2'];
			$error = (isset($item['error']['score2']))?'form-error':"";
			
			$return = '<input type="number" min="0" max="20" match_id="'.$item['row'].'" number="2" name="score['.$item['row'] .'][score2]" class="inputresult '.$error.'" id="score_'.$item['row'] .'_2" value="'.$score .'" '.$readonly . ' />';
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
	
	/**
	 * validate input score
	 * 
	 * check if match is readonly and for a number
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $value the input data for validation
	 * @param string|int $key the key of the input field
	 * @param int $row the number of the row match id
	 * @return boolean validation check
	 */
	public function validate_score($value, $key = null, $row = null){
		$match = $this->matches[$row];
		if($match['readonly'] == ''){
			return FALSE;
		}else{
			if(!is_numeric($value) || $value == "" || $value == null){
				return false;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * validate penalty input
	 * 
	 * check if score1 is same as score2 then check for a number. default no
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $value the input value
	 * @param string $inputname the name of the input field
	 * @param int $row the number row for multi input field array
	 * @param int $key
	 * @return boolean validation check
	 */
	public function validate_penalty($value, $inputname = null, $row = null, $key = null){
		$match = $this->matches[$row];
		
		$phases = poule_get_phases();
		
		$phaseurl = (isset($_GET['phase'])) ? $_GET['phase'] : $phases[0]->slug;
		
		$penalties = FALSE;
		$code = 0;
		$Phase_Penalties = array();
		foreach($phases as $phase){
			$meta = Poule_Get_Phase_Meta($phase->term_id, 'penalties');
//			$code = 1;
			if(is_object($meta) && $meta->penalties != null && $meta->penalties != '0' && $phaseurl == $phase->slug){
				$penalties = TRUE;//true
//				$code = 2;
			}
		}
		
		if($penalties){
			$code = 3;
			
			if($match['readonly'] != ''){
				$code = 4;
				if($_POST['score'][$row]['score1'] == $_POST['score'][$row]['score2'] && $_POST['score'][$row]['score1'] != "" ){
					$code = 5;
					if(!is_numeric($value) || $value == "" || $value == null){
						$code = 6;
						return TRUE;//true
					}
				}
			}
		}
		
		return FALSE;
	}
	
	private function get_matches($phase){
		date_default_timezone_set(get_option('timezone_string'));
		
		global $wpdb;
//		if(count($this->matches) == 0){
			foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches_groups WHERE phase='%s' ORDER BY group_name ASC", $phase),ARRAY_A) as $rowid => $group){
				foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$group['id']),ARRAY_A) as $id => $match) {		
					$starttime = date("d-m-Y H:i:s",  strtotime($match['start_date']));
					$match['start_time'] = $starttime;
					
					$start = strtotime($match['start_date']) + 6300;
					$match['readonly'] = ($start <= time()) ? '' : 'readonly="readonly"';
					
					$this->matches[$match['id']] = $match;
					
				}
			}
//		}
	}
}