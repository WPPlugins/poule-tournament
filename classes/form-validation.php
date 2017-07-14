<?php
/**
 * This file create the custom mete boxes.
 * 
 * It's create the custom meta boxes for custom post stype subpoule and the cusotm filed for custom taxonomie phase 
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The class that create it
 * 
 * validate the post input
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class form_validation {
    
	/**
	 * Validated, formated and sanitize input data
	 * 
	 * Input data after Validated, formated and sanitize. For use in the class. By error the input is empty
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var array
	 */
    public $input;
    
	/**
	 * array with the errors
	 * 
	 * This variable contains all the errors in the input
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var array
	 */
    public $errors;
    
	/**
	 * array with a error count
	 * 
	 * This contains the count of the errors for a check if there are errors.
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var array
	 */
    public $counterrors = array();
    
	/**
	 * Namespace of a class
	 * 
	 * namespace of the class that call validate_input() 
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    private $namespace;
    
	/**
	 * Name of the input name/key
	 * 
	 * Name of the key from the post array
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    private $rootkey;
    
	/**
	 * temp key
	 * 
	 * temp key during the validation
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var int
	 */
    private $index = 1;
    
	/**
	 * Temp value of the root
	 * 
	 * temp value for the validation
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    private $valueroot;
    
	/**
	 * count of Temp value of the root
	 * 
	 * The count of temp value for the validation
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var int
	 */
    private $valuerootcount;
    
	/**
	 * constructor
	 * 
	 * The constructor for the class. Its add the hooks and filters for wordpress
	 * 
	 * @package poule_tournament
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @since version 2.2
	 * @version 1
	 * @access public
	 */
    public function __construct() {
        $this->errors = array();
        $this->input = array();
    }
    
	/**
	 * Form init for the class
	 * 
	 * Add all the validation functions 
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function form_init(){
        add_action('poule_validate_int', array(&$this,'validate_int'),10,4);
        add_action('poule_validate_date', array(&$this,'validate_date'),10,4);
        add_action('poule_validate_time', array(&$this,'validate_time'),10,4);
    }
    
	/**
	 * Start of the validation
	 * 
	 * prepare the class for validation
	 * 
	 * @param string $inputname
	 * @param array $args
	 * @param namespace $namespace variable of $this from the other class
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function validate_input($inputname, $args, $namespace){
    	$this->namespace = $namespace;
    	
        $this->rootkey = $inputname;
    	$this->errors = array($inputname => array());
    	
    	$a = $this->validate($_POST[$inputname], $args,null,TRUE);
    }
	
	/**
	 * The validation function
	 * 
	 * Validate the input by looping the input and call the validate function to check. fill the public class variables
	 * 
	 * @param array $input the input for validation
	 * @param array $args the arguments for validation
	 * @param int|string|null $keyname The keyname of te array element
	 * @param bool $root first call
	 * @return array new validate results
	 * @access private
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    private function validate($input, $args, $keyname = null, $root = FALSE){
        $return = FALSE;
		$array = array('data' => array('errors' => array(), 'input' => array(),'total' => 0),'info' => array());
		if(is_array($input)){
			foreach($input as $key => $value){
                if($root){
                    $this->index = $key;
                    $this->valueroot = $value;
                    $this->valuerootcount = count($value);
                }
                
                if(is_numeric($key) && $this->valuerootcount == count($value))$this->index = $key;
				
				if(is_array($value)){
					$args = (is_numeric($key)) ? $args : $args[$key];
					
                    $return = $this->validate($input[$key],$args,$key);
					
                    $keyname3 = (is_numeric($keyname)) ? $keyname : $key;
                    
                    $keyname2 = ($keyname == null) ? $key : $keyname;
                    
                    if(array_key_exists($this->rootkey, $this->counterrors)){
                        $this->counterrors[$this->rootkey] += $return['data']['total'];
                    }else{
                        $this->counterrors[$this->rootkey] = $return['data']['total'];
                    }
                    
                    if($keyname != ""){
                        //sub
                        if(isset($this->errors[$this->rootkey][$this->index][$keyname][$keyname3])){
                            $this->errors[$this->rootkey][$this->index][$keyname][$keyname3] = array_merge($this->errors[$this->rootkey][$this->index][$keyname][$keyname3], $return['data']['errors']);
                        }else{
                            $this->errors[$this->rootkey][$this->index][$keyname][$keyname3] = $return['data']['errors'];
                        }
                    }else{
                        //root
                        if(isset($this->errors[$this->rootkey][$this->index])){
                            $this->errors[$this->rootkey][$this->index] = array_merge($this->errors[$this->rootkey][$this->index], $return['data']['errors']);
                        }else{
                            $this->errors[$this->rootkey][$this->index] = $return['data']['errors'];
                        }
                    }
                    
                    
                    if($keyname != ""){
                        //sub
                        if(isset($this->input[$this->rootkey][$this->index][$keyname][$keyname3])){
                            $this->input[$this->rootkey][$this->index][$keyname][$keyname3] = array_merge($this->input[$this->rootkey][$this->index][$keyname][$keyname3], $return['data']['input']);
                        }else{
                            $this->input[$this->rootkey][$this->index][$keyname][$keyname3] = $return['data']['input'];
                        }
                    }else{
                        //root
                        if(isset($this->input[$this->rootkey][$this->index])){
                            $this->input[$this->rootkey][$this->index] = array_merge($this->input[$this->rootkey][$this->index], $return['data']['input']);
                        }else{
                            $this->input[$this->rootkey][$this->index] = $return['data']['input'];
                        }
                    }
                    
                    
				}else{
					if($args[$key]['required'] == 1){
                        $validate_check = $this->call_function($args[$key]['type'], $value, $key, $keyname);
						if($validate_check){
							$array['data']['errors'][$key] = 1;
							$array['data']['input'][$key] = "";
							$array['data']['total']++;
                            $array['info']['keyname'] = $keyname;
						}else{
							$array['data']['input'][$key] = $value;
                            $array['info']['keyname'] = $keyname;
                        }
					}else{
						if($value != null || $value != ""){
							$validate_check = $this->call_function($args[$key]['type'], $value, $key, $keyname);
                            if($validate_check){
								$array['data']['errors'][$key] = 1;
								$array['data']['input'][$key] = "";
								$array['data']['total']++;
                                $array['info']['keyname'] = $keyname;
							}else{
                                $array['data']['input'][$key] = $value;
                                $array['info']['keyname'] = $keyname;
                            }
						}
					}
				}
			}
		}else{
			$key = null;
			if($args[$this->rootkey]['required'] == 1){
//				echo $input . '-';
                $validate_check = $this->call_function($args[$this->rootkey]['type'],$input, $key, $keyname);
                if($validate_check){
                    $this->errors[$this->rootkey] = 1;
                    $this->input[$this->rootkey] = "";
                    $this->counterrors[$this->rootkey] = 1;
                }else{
                    $this->input[$this->rootkey] = $input;
                    $this->counterrors[$this->rootkey] = 0;
                }
            }else{
                if($value != null || $value != ""){
//					echo $keyname . '-';
                    $validate_check = $this->call_function($args[$this->rootkey]['type'],$input, $key, $keyname);
                    if($validate_check){
                        $this->errors[$this->rootkey] = 1;
                        $this->input[$this->rootkey] = "";
                        $this->counterrors[$this->rootkey] = 1;
                    }else{
                        $this->input[$this->rootkey] = $input;
                        $this->counterrors[$this->rootkey] = 0;
                    }
                }
            }
		}
		
		return $array;
    }
    
	/**
	 * Call the different functions
	 * 
	 * Call the validate functions for validation and return a boolean
	 * 
	 * @param string $function input sort int|string|bool
	 * @param string $value the input value
	 * @param string $key keyname of the element
	 * @param int $row row number for multi array
	 * @return bool
	 * @access private
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
	private function call_function($function, $value, $key, $row){
		$method = 'validate_'.$function;
        if(method_exists($this,$method)){
        	$data = call_user_func_array(array($this, $method), array($value));
        }else{
        	$data = call_user_func_array(array($this->namespace, $method), array($value, $key, $row));
        }
        return $data;
	}

	/**
	 * Validate string
	 * 
	 * Check if the input is a string
	 * 
	 * @param string $value the input value
	 * @return bool
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function validate_string($value){
        $value = trim($value);
        if($value == "" || $value == null || !is_string($value)){
            return TRUE;
        }
        return false;
    }
    
	/**
	 * Validate int
	 * 
	 * Check if the input is a int
	 * 
	 * @param string $value the input value
	 * @return bool
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function validate_int($value){
        if(!is_numeric($value)){
            return true;
        }
    }
    
	/**
	 * Validate date
	 * 
	 * Check if the input is a date
	 * 
	 * @param string $value the input value
	 * @return bool
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function validate_date($value){
//        echo $value;
        $d = DateTime::createFromFormat("Y-m-d", $value);
		$return = $d && $d->format("Y-m-d") == $value;
        
        if($return === FALSE){
            return true;
        }
    }
    
	/**
	 * Validate time
	 * 
	 * Check if the input is a time
	 * 
	 * @param string $value the input value
	 * @return bool
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function validate_time($value){
        $time = (strlen($value) == 5)? $value . ':00' : $value;
        
        $d = DateTime::createFromFormat("H:i:s", $time);
        $return = $d && $d->format('H:i:s') == $time;
        //Y-m-d H:i:s
        if($return === FALSE){
            return true;
        }
        
    }
    
	/**
	 * Validate email
	 * 
	 * Check if the input is a email
	 * 
	 * @param string $value the input value
	 * @return bool
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function validate_email($value){
        $check = is_email( $value );
        if($value == $check){
            return false;
        }else{
            return true;
        }
    }
    
	/**
	 * Reset class variables
	 * 
	 * Reset the class variables for a clear validation.
	 * 
	 * @return bool
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.nl>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function reset(){
        $this->counterrors = array();
        $this->errors = null;
        $this->input = null;
        $this->namespace = null;
        $this->rootkey = null;
        $this->valueroot = null;
        $this->valuerootcount = 0;
    }
}

/**
 * Create global variable
 * 
 * Create the gloabel variable for the form valivaidations
 * 
 * @author Stefan de Bruin <info@stefandebruin.nl>
 * @package poule_tournament
 * @since version 2.2
 * @version 1
 */
$poule_validation = new form_validation();