<?php 
/**
 * A template file for matches add
 * 
 * Template for add a group of matches
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
    <h2><?php _e('Add match group', 'poule-tournament'); ?></h2>
    
    <form action="" method="post">
        <div id="titlediv">
            <div id="titlewrap">
                <input type="text" name="group_name" class="<?php echo $error_group_name; ?>" size="30" value="<?php echo $input_name?>" id="title" autocomplete="off" placeholder="<?php _e('Type here the name of the group','poule-tournament');?>">
            </div>
        </div>

        <?php $table_match->display(); ?>

        <?php submit_button(__('Add')); ?>
    </form>
</div>