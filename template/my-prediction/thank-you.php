<?php 
$settings = get_option( 'poule_settings', array() );

if(array_key_exists('message_after_save', $settings)){
	echo apply_filters('the_content',$settings['message_after_save']); 
}

?>