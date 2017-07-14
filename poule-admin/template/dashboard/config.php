<?php 
/**
 * Templatefile
 * 
 * Dashboard congif template change the variables
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 * @version 2
 */
?>

<?php 
$args = array( 'post_type' => 'subpoule', 'posts_per_page'=> -1 );
$loop =  new WP_Query( $args );

$widget_settings = get_option('poule_widget_settings',0);

$subpoule = (array_key_exists('subpoule', $widget_settings))?$widget_settings['subpoule']:"0";
?>

<table>
	<tr>
		<td>
			<select name="subpoule">
				<option value="0"><?php _e('default', 'poule-tournament')?></option>
				<?php if($loop->have_posts()): while( $loop->have_posts() ): $loop->the_post() ?>
				<?php $select = ($subpoule == get_the_ID()) ? 'selected="selected"' : "";?>
				
				<option value="<?php the_ID();?>" <?php echo $select ?>> <?php the_title()?> </option>
				
				<?php endwhile;	endif;?>
			</select>
		</td>
	</tr>
</table>