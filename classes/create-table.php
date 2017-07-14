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
 * create the custom fields
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class create_table extends WP_List_Table{
    
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's the folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $data;
    
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's the folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $columns;
    
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's the folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $classname;
    
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's the folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $function;
    
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's the folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $penalty = null;
    
	/**
	 * location to the template directory
	 * 
	 * The path to the template directory. It's the folder in the plugin or the current theme.
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
    public $class = 0;
    
	/**
	 * constructor
	 * 
	 * The constructor for the class. Its add the hooks and filters for wordpress
	 * 
	 * @param string $class Name of the class for show column data
	 * @param string $function Name of the function for show column data
	 * @package poule_tournament
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @access public
	 */
    public function __construct($class, $function = '') {
		parent::__construct(array());
        
        $this->classname = $class;
        $this->function = $function;
	}
    
	/**
	 * Return the coulm names
	 * 
	 * Return the column names for the tables
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @return array of the column names
	 */
    function get_columns() {
		return $this->columns;
	}
    
	/**
	 * Prepare the table
	 * 
	 * Prepare the tables. Set the data corretc for showing.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		//$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, null);
		$this->items = $this->data;
	}
    
	/**
	 * Get the content of the column
	 * 
	 * Create the function name and call it for the column content. Nof function is column header name
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item column data array
	 * @param string $column_name Name of the column
	 * @return string column content
	 */
    function column_default($item, $column_name) {
        
        $filter = 'poule_table_'.$this->classname.'_';
        $filter .= ($this->function != "") ? $this->function.'_' : "";
        $filter .= 'column_'.$column_name;
        
        $return = apply_filters($filter,$item);
        
        if($return == $item){
            $filter = 'poule_table_'.$this->classname.'_';
            $filter .= 'column_'.$column_name;
            
            $return = apply_filters($filter,$item);
            
            if($return == $item){
                $return = $column_name;
            }
        }
        
        return $return;
	}
    
	/**
	 * Create the single rows
	 * 
	 * Create the single rows. if the class name is Score then create a second row for the penalty and check for hidden.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param array $item Array of the item row
	 */
    function single_row($item) {
        if($this->classname == "official_result"){
            if($this->class == 0){
                $row_class = ' class="alternate"';
                $this->class++;
            }else if($this->class == 1){
                $row_class = ' ';
                $this->class++;
            }else if($this->class == 2){
                $row_class = ' class="alternate"';
                $this->class = 1;
            }
			
			$phases = poule_get_phases();
			
			$phase = (isset($_GET['phase-url'])) ? $_GET['phase-url'] : $phases[0]->slug;
        
			$term = get_term_by('slug', $phase, 'phase');
			
			$meta = Poule_Get_Phase_Meta($term->term_id, 'penalties');
//			var_dump($meta);
			if(is_object($meta) && $meta->penalties != null && $meta->penalties != '0'){
				$hidden = ($item['score1'] == $item['score2'] && $item['score1'] != '') ? "" : 'hidden="hidden"';
			}else{
				$hidden = 'hidden="hidden"';
			}
           
//            $hidden = '';
            echo '<tr' . $row_class . '>';
            $this->penalty = FALSE;
			$item['penalty'] = FALSE;
            $this->single_row_columns( $item );
            echo '</tr>';
            echo '<tr id="hidden_'.$item['row'].'" '. $hidden . $row_class . '>';
            $this->penalty = TRUE;
            $item['penalty'] = TRUE;
			$this->single_row_columns( $item );
            echo '</tr>';
        }else{
            parent::single_row($item);
        }
	}
}