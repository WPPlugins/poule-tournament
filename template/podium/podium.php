<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php 
    
    $podium = apply_filters('poule_get_podium','');
    $settings = get_option("poule_settings",array());
?>


<!--<div class="col-12 col-lg-12 col-sm-12">-->

    <?php if(array_key_exists('subpoules',$settings) && $settings['subpoules'] == 1):?>
    <?php do_action('poule_sub_poules'); ?>
    <?php endif;?>
	<?php do_action('poule_before_podium'); ?>
    <table id="podium" class="table table-hover">
		<thead>
			<tr>
				<th>
					<?php _e('Podium place', 'poule-tournament') ?>
				</th>
				<th >
					<?php _e('Fullname', 'poule-tournament') ?>
				</th>
				<th>
					<?php _e('Points', 'poule-tournament') ?>
				</th>
			</tr>
		</thead>
        <tfoot>
            <tr>
				<th>
					<?php _e('Podium place', 'poule-tournament') ?>
				</th>
				<th >
					<?php _e('Fullname', 'poule-tournament') ?>
				</th>
				<th>
					<?php _e('Points', 'poule-tournament') ?>
				</th>
			</tr>
        </tfoot>
        
        <tbody>
			<?php foreach ($podium as $place) { ?>
				<tr id="<?php // echo $place['poules'];?>">
					<td>
						<div id="place"><?php echo $place['place']; ?></div>
					</td>
					<td>
                        <?php  echo get_avatar($place['id'],28); ?>
						<!--<input id="poules" type="hidden" hidden="hidden" value="<?php //echo $place['poules'];?>"/>-->
                        <a href="<?php echo poule_create_correct_url(array('user' => sanitize_title($place['url']))); ?>"><?php echo $place['fullname']; ?></a>
					</td>
					<td><?php echo $place['score']; ?></td>
				</tr>
			<?php } ?>
		</tbody>
    </table>
<!--</div>-->