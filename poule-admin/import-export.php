<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class import_export{
	public function __construct(){
		
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
        
	}
    
    public function create_file(){
        global $wpdb;
       
		$dom = new DOMDocument('1.0', 'utf-8');

        $element = $dom->createElement('import_export');
        

        $root = $dom->getElementsByTagName('import_export');
        
        $matches = $dom->createElement('matches');
        foreach($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_matches_groups ORDER BY group_name ASC",ARRAY_A) as $rowid => $groupinfo){
            $group = $dom->createElement('group');
            
            $groupattribute = $dom->createAttribute('phaseid');
            $groupattribute->value = $groupinfo['id'];
            $group->appendChild($groupattribute);
            
            $groupattribute = $dom->createAttribute('name');
            $groupattribute->value = $groupinfo['group_name'];
            $group->appendChild($groupattribute);
            
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_matches WHERE group_id='%s'",$groupinfo['id']),ARRAY_A) as $id => $matchinfo) {
                $match = $dom->createElement('match',$matchinfo['score']);
                
                $matchattribute = $dom->createAttribute('id');
                $matchattribute->value = $matchinfo['id'];
                $match->appendChild($matchattribute);
                
                $matchattribute = $dom->createAttribute('country1');
                $matchattribute->value = $matchinfo['country_1'];
                $match->appendChild($matchattribute);
                
                $matchattribute = $dom->createAttribute('country2');
                $matchattribute->value = $matchinfo['country_2'];
                $match->appendChild($matchattribute);
                
                $group->appendChild($match);
            }
            
            
            $matches->appendChild($group);
        }
        
        $element->appendChild($matches);
        
        //user score
        $users = array();
		foreach ($wpdb->get_results("SELECT distinct user_id FROM {$wpdb->prefix}poule_score", ARRAY_A) as $user)
			$users[$user['user_id']] = array('id' => $user['user_id']);
        
        $matches = array();
        foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}poule_matches",ARRAY_A) as $id => $match) {
            $matches[$match['id']] = $match;
        }
        
        $score = $dom->createElement('score');
        foreach($users as $userid => $user){
            $group = $dom->createElement('user');
            
            $userattribute = $dom->createAttribute('id');
            $userattribute->value = $userid;
            $group->appendChild($userattribute);
            
            foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_score WHERE user_id='%s'",$userid),ARRAY_A) as $id => $phaseinfo) {
                $phase = $dom->createElement('phase',$phaseinfo['score']);
                
                $phaseattribute = $dom->createAttribute('id');
                $phaseattribute->value = $phaseinfo['phase'];
                $phase->appendChild($phaseattribute);
                
                $group->appendChild($phase);
            }
            
            $score->appendChild($group);
        }
        
        $element->appendChild($score);
        
        $dom->appendChild($element);
        
        echo $dom->saveXML();
        
        die();
    }
}


?>