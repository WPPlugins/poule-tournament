
<?php 
/**
 * Templatefile
 * 
 * Matches add automatic matches
 * 
 * @version 2.2
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */
?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<style>.tablenav{display: none !important;}</style>

<div class="wrap">
    <h2><?php _e('Add match group', 'poule-tournament'); ?></h2>
    
    <form action="" method="post">
    	
    	<?php foreach($groups as $group):?>
    	
        <div id="titlediv">
            <div id="titlewrap">
                <input type="text" name="group[<?php echo $group['name']['group_id']?>][name]" class="<?php echo $group['name']['error']?>" size="30" value="<?php echo $group['name']['input']?>" id="title" autocomplete="off" placeholder="<?php _e('Type here the name of the group','poule-tournament');?>">
            </div>
        </div>

        <?php $group['matches']->display(); ?>
		<hr />
        <br />
		<?php endforeach; ?>

        <?php submit_button(__('Add', 'poule-tournament')); ?>
    </form>
</div>