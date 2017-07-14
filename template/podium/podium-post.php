<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php 
    get_header();
    
    $podium = apply_filters('poule_get_podium','');
    $settings = get_option("poule_settings")
?>


<div class="col-12 col-lg-12 col-sm-12">

    <?php if(array_key_exists('subpoules', $settings) && $settings['subpoules'] == 1):?>
    <?php do_action('poule_sub_poules'); ?>
    <?php endif;?>
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
                        <?php // echo get_avatar($place['id'], '96'); ?>
						<!--<input id="poules" type="hidden" hidden="hidden" value="<?php //echo $place['poules'];?>"/>-->
                        <a href="<?php echo $place['fullname']; ?>"><?php echo $place['fullname']; ?></a>
					</td>
					<td><?php echo $place['score']; ?></td>
				</tr>
			<?php } ?>
		</tbody>
    </table>
</div>

<?php get_footer(); ?>