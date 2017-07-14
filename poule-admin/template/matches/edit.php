<?php 
/**
 * Templatefile
 * 
 * Matches edit match groep
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
    <h2><?php _e('Edit match group','poule-tournament');?></h2>
    <form action="" method="post">
        <div id="titlediv">
            <div id="titlewrap">
                <input type="text" name="group_name" class="" size="30" value="<?php echo $groupsnaam; ?>" id="title" autocomplete="off" placeholder="<?php _e('Type here the name of the group','poule-tournament');?>">
            </div>
        </div>

        <?php $table_match->display(); ?>

        <br />
        <?php submit_button(__('Save changes', 'poule-tournament'),'primary','submit',FALSE); ?>
        <a href="#" class="button-secondary" id="pouledelete"><?php _e('Delete', 'poule-tournament'); ?></a>
    </form>
</div>
<script>
var poule_phase="<?php echo $phase; ?>";
var poule_group="<?php echo $groupsid; ?>";
</script>