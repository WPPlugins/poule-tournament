<?php 
/**
 * A template file for matches home
 * 
 * Template for a list of all the groups in that phase
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 * @version 2.2
 */
?>

<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<style>.tablenav{display: none !important;}</style>

<div class="wrap">
	<h2><?php _e('Matches', 'poule-tournament') ?>
		<a href="<?php echo poule_create_correct_url(array('function' => 'add', 'phase' => $phase))?>" class="add-new-h2"><?= _e('New Match', 'poule-tournament') ?></a>
		<?php if($terms[0]->slug != $phase):?>
			<a href="<?php echo poule_create_correct_url(array('function' => 'auto', 'phase' => $phase),array('post_type'))?>" class="add-new-h2"><?= _e('Auto Match', 'poule-tournament') ?></a>
		<?php endif; ?>
	</h2>
	
	<?php do_action('poule_create_pagination_admin');?> 
    
    <?php do_action('poule_phase_message',TRUE); ?>
    
    <?php foreach($groups as $groupid => $groupdata): ?>
    
    <h3>
		<a href="?post_type=country&page=matches&function=edit&phase=<?php echo $phase?>&groupid=<?php echo $groupid?>">
    		<?php echo $groupdata['group_name']?>
    	</a>
    </h3>
    
    <?php $groupdata['matches']->display(); ?>
    <br />
    
    <?php endforeach; ?>
</div>