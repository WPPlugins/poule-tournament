<?php 
/**
 * Templatefile
 * 
 * official-resut home set result
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 * @version 2.2
 */
?>

<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<style>.tablenav{ display: none !important;}</style>

<?php do_action('admin_notices');?>

<div class="wrap">
    <h2>
        <?php _e('Official Result', 'poule-tournament') ?>
    </h2>

	<?php do_action('poule_create_pagination_admin');?> 
    
	<?php do_action('poule_phase_message',TRUE); ?>
	
    <form action="" method="post">
    <?php foreach($groups as $groupid => $groupdata): ?>
    
    <h3>
        <?php echo $groupdata['group_name']?>
    </h3>
    
    <?php $groupdata['matches']->display(); ?>
    <br />
    
    <?php endforeach; ?>
    
    <?php submit_button(); ?>
    </form>
    
</div>