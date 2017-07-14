<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php  $subpoules = apply_filters('poule_get_subpoules', ''); ?>

<table id="member_of_subpoules" class="table table-hover subpoules">
    <thead>
        <tr>
            <th><?php _e('Name') ?></th>
            <th><?php _e('From') ?></th>
            <th><?php _e('Actions') ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th><?php _e('Name') ?></th>
            <th><?php _e('From') ?></th>
            <th><?php _e('Actions') ?></th>
        </tr>
    </tfoot>
    <tbody>
        <?php foreach($subpoules as $subpoule): ?>
        <tr poule-id="<?php echo $subpoule->poule_id; ?>">
            
            <td><?php echo $subpoule->title; ?></td>
            <td><?php echo $subpoule->from; ?></td>
            <td><a class="btn btn-default" poule-id="<?php echo $subpoule->poule_id; ?>" id="deletesubpoule" hash="<?php echo $subpoule->delete_link; ?>" href="#"><?php _e('Delete')?></a></td>
			
        </tr>
        <?php endforeach;?>
    </tbody>
</table>