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
class add_meta_boxes {
    
    /**
	 * constructor
	 * 
	 * The constructor for the class for the plugin. Its add the hooks and filters for wordpress
	 * 
	 * @package poule_tournament
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @access public
	 */
    public function __construct() {
        add_action( 'add_meta_boxes', array($this,'add_poule_metaboxes'));
        //add_action( 'save_post_phase', array($this,'save_metaboxes') );
        
        add_action( 'phase_add_form_fields', array($this,'tax_phase_settings'), 10, 99 );
        add_action( 'phase_edit_form_fields', array($this,'edit_tax_phase_settings'), 10, 99 );
        
        add_action( 'edited_phase', array($this,'save_tax_phase_settings'), 10, 2 );  
        add_action( 'create_phase', array($this,'save_tax_phase_settings'), 10, 2 );
    }
    
	/**
	 * The home page for custom tax phase
	 * 
	 * Add the custom field to the home page of custom taxonomie phase
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function tax_phase_settings(){
		?>
		<div class="form-field">
			<label for="term_meta[groups]"> <?php _e( 'Number of groups in the phase', 'poule-tournament' ) ?></label>
			<input type="number" name="term_meta[groups]" id="term_meta[groups]" value="">
		</div>
		<div class="form-field">
			<label for="term_meta[matches_per_group]"> <?php _e( 'Number of matches in one group', 'pippin' ) ?></label>
			<input type="number" name="term_meta[matches_per_group]" id="term_meta[matches_per_group]" value="">
		</div>
		<div class="form-field">
			<label><?php _e('Penalties for this phase?','poule-tournament') ?></label>
			<input type="checkbox" class="poule_check" id="penalties" name="term_meta[penalties]" value="" />
		</div>
		<?php
    }
	
	/**
	 * The edit page for custom tax phase
	 * 
	 * Add the custom field to the edit page of a custom taxonomie phase item
	 * 
	 * @param object $term Object of the phase for edit
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function edit_tax_phase_settings($term){
        $term_meta = Poule_Get_Phase_Meta($term->term_id);
		
		$input = array(
			'groups' => isset( $term_meta->groups ) ? esc_attr( $term_meta->groups ) : '',
			'matches_per_group' => isset( $term_meta->matches_per_group ) ? esc_attr( $term_meta->matches_per_group ) : '',
			'penalties' => (isset( $term_meta->penalties ) && $term_meta->penalties == 1) ? 'checked="checked"' : ''
		);
        ?>
        
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[groups]">
					<?php _e( 'Number of groups in the phase', 'poule-tournament' ) ?> 
				</label>
			</th>
			<td> 
				<input type="number" name="term_meta[groups]" id="term_meta[groups]" value="<?php echo $input['groups']?>"/> 
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[matches_per_group]">
					<?php _e( 'Number of matches in one group', 'poule-tournament' ) ?>
				</label>
			</th>
            <td> 
				<input type="number" name="term_meta[matches_per_group]" id="term_meta[matches_per_group]" value="<?php echo $input['matches_per_group']?>"/> 
			</td>
		</tr>
		
		<tr>
			<th scope="row" valign="top">
				<label for="term_meta[penalties]">
					<?php _e('Penalties for this phase?','poule-tournament')?>
				</label>
			</th>
			<td> 
				<input type="checkbox" id="term_meta[penalties]" name="term_meta[penalties]" <?php echo $input['penalties']?> value="1" />
			</td>
		</tr>
		
		<?php
    }
    
	/**
	 * Save the custom field for the phase
	 * 
	 * Save the custom field of the taxonomie phase
	 * 
	 * @see Poule_Update_Phase_Meta()
	 * @param int $term_id id of the created phase
	 * @param string $value input value for validation and saving
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    function save_tax_phase_settings( $term_id ,$value) {
        if ( isset( $_POST['term_meta'] ) ) {
            
			foreach(array('groups','matches_per_group','penalties') as $option){
				$value = (isset($_POST['term_meta'][$option])) ? $_POST['term_meta'][$option] : 0;
				Poule_Update_Phase_Meta($term_id,$option,$value);
			}
        }
		
		return TRUE;
    }  
    
    /**
	 * Main function to add a custom metabox
	 * 
	 * Function that add only the meta box
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function add_poule_metaboxes(){
        add_meta_box('Users in the group', __('Users in the group','poule-tournament'), array($this,'users_in_group'), 'subpoule', 'normal', 'default');
    }
    
	/**
	 * Add the metabox
	 * 
	 * add the content to the meta box. The content are all the users in the current subpoule. 
	 * 
	 * @global database $wpdb database object
	 * @param object $post object of the current subpoule
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 */
    public function users_in_group($post){
        global $wpdb;
        
        wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );
        
        $users = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}poule_subpoule_users WHERE poule_id='%s'",$post->ID));
        
        $data = array();
		echo '<table>';
        foreach($users as $user){
			echo '<tr>';
			//echo '<td><a href="#" id="Admin_add_user">'.__('Delete').'</a></td>';
			$user_info =  get_userdata($user->user_id);
			
			$name = $user_info->first_name . ' ' . $user_info->last_name;
			
			if(trim($name) == ""){
				$name = $user_info->display_name;
			}
			echo '<td>' . $name . '</td>';
            $data[] = array('name' => $user->user_id);
            echo '</tr>';
        }
		echo '</table>';
        
    }
    
	/**
	 * Save the data from the custom metabox
	 * 
	 * Save
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @todo afmaken | Verwijderen
	 * @param int $post_id Id of the new post
	 */
	public function save_metaboxes($post_id){
        if ( wp_is_post_revision( $post_id ) )
            return;
        
        if ( "phase" != $_POST['post_type'] )
            return;
            
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;
        
//        $value = (isset($_POST['groups'])) ? esc_attr($_POST['groups']) : 0;
//        Poule_Get_Phase_Meta($post->id,'groups',$value);
//        
//        $value = (isset($_POST['matches_per_group'])) ? esc_attr($_POST['matches_per_group']) : 0;
//        Poule_Get_Phase_Meta($post->id,'matches_per_group',$value);
    }
}

/**
 * initialization of the class
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @see add_meta_boxes
 * @package poule_tournament
 * @since version 2.2
 * @version 1
 */
new add_meta_boxes();