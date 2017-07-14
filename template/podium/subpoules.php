<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php 

    $subpoules = apply_filters('poule_get_sub_poules','');
	
	$invitations = apply_filters('poule_subpoule_invitations_poules',"");
	
	$settings = get_option("poule_settings",array());
	
    $subpoulespage = array_key_exists('page_subpoules', $settings)?$settings['page_subpoules'] : '';
    $podiumpage = array_key_exists('page_podium', $settings) ? $settings['page_podium'] : '';
	
?>

<!--//<select name="any_user_arg" onchange="window.location='/warehouse/loan/user_id/'+this.value+'.html'" >-->
<!--<select  onchange="window.location='<?php //echo functions::create_link("poule=","?") ?>'+this.value" onfocus="this.selectedIndex = -1;">-->
<div class="row">
	<?php if($subpoulespage != ""){?>
    <div class="col-lg-6 col-md-6 col-12">
        <select class="form-control" id="change_podium" onfocus="this.selectedIndex = -1;">
			<?php if(array_key_exists('subpoule_default', $settings) && $settings['subpoule_default'] == 1):?>
            <option value="default"><?php _e('Default','poule-tournament');?></option>
			<?php endif; ?>
            <?php foreach($subpoules as $poule_id => $poule_info){?>
            <option value="<?php echo $poule_info['slug']; ?>"><?php echo $poule_info['name']; ?></option>
            <?php } ?>

        </select>
    </div>
	<?php }?>
	<?php if(array_key_exists('subpoules', $settings) && $settings['subpoules'] == 1 || !array_key_exists('subpoules', $settings) && count($invitations) != 0):?>
    <div class="col-lg-6 col-md-6 col-12">
        <a href="/?p=<?php echo $subpoulespage; ?>"  class="magage-btn btn btn-default"><?php _e('Manage own poules', 'poule-tournament');?> <?php // do_action('poule_add_badge'); ?></a>
    </div>
	<?php endif; ?>
	
</div>

<script>
var poule_url = "<?php echo site_url('?p='.$podiumpage.'&poule=');?>";
</script>