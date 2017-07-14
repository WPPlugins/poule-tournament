<?php 
/**
 * Templatefile
 * 
 * Only visible on a login required page as no user is logged in
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 * @version 1
 */
?>


<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="alert alert-danger"><?php _e('Login is required to view this page','poule-tournament'); ?></div>

<div class="col-12 col-lg-12 col-sm-12">
	<div class="col-12 col-lg-3 col-md-3">
	<?php
		$args = array(
			'echo'           => true,
			'redirect'       => site_url( $_SERVER['REQUEST_URI'] ), 
			'form_id'        => 'poulelogin',
			'label_username' => __( 'Username', 'poule-tournament' ),
			'label_password' => __( 'Password', 'poule-tournament' ),
			'label_remember' => __( 'Remember Me', 'poule-tournament' ),
			'label_log_in'   => __( 'Log In', 'poule-tournament' ),
			'id_username'    => 'user_login',
			'id_password'    => 'user_pass',
			'id_remember'    => 'remember_me',
			'id_submit'      => 'wp-submit',
			'remember'       => FALSE,
			'value_username' => NULL,
			'value_remember' => false
		);
		
		wp_login_form( $args );
	?>
	
	<script>
		
		jQuery(document).ready(function() {
			
			jQuery("#user_login").addClass("form-control");
			jQuery("#user_pass").addClass("form-control");
			jQuery("#wp-submit").addClass("btn btn-primary");
		});
	</script>
	</div>
</div>