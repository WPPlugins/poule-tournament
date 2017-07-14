<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
$settings = get_option("poule_settings",array());
?>

<div class="bs-example bs-example-tabs">
    <ul id="myTab" class="nav nav-tabs">
        <li class="active"><a href="#subpoules" data-toggle="tab"><?php _e('Subpoules','poule-tournament');?></a></li>
        <li class=""><a href="#invitations" data-toggle="tab"><?php _e('Invitations','poule-tournament');?> <?php do_action('poule_add_badge'); ?></a></li>
		<?php if(array_key_exists('subpoules', $settings) && $settings['subpoules'] == 1): ?>
        <li class=""><a href="#own_subpoules" data-toggle="tab"><?php _e('Own Subpoules','poule-tournament');?></a></li>
		<?php endif;?>
    </ul>
    <div id="myTabContent" class="tab-content thumbnail subpoules">
        <div class="tab-pane fade active in" id="subpoules">
            <p>
                <?php do_action('poule_subpoules_subpoules');?>
            </p>
        </div>
        <div class="tab-pane fade" id="invitations">
            <p>
                <?php do_action('poule_subpoules_invitations');?>
            </p>
        </div>
        <div class="tab-pane fade" id="own_subpoules">
            <p>
                <?php do_action('poule_subpoules_own_subpoules');?>
            </p>
        </div>
    </div>
</div>