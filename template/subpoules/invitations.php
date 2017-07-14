<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
	do_action('poule_subpoule_url_action');

	$subpoules = apply_filters('poule_subpoule_invitations_poules',"");
?>

<table id="podium" class="table table-hover">
		<thead>
			<tr>
				<th>
					<?php _e('Subpoule Name', 'poule-tournament') ?>
				</th>
				<th >
					<?php _e('Invited By', 'poule-tournament') ?>
				</th>
				<th>
					<?php _e('Actions', 'poule-tournament') ?>
				</th>
			</tr>
		</thead>
        <tfoot>
            <tr>
				<th>
					<?php _e('Subpoule Name', 'poule-tournament') ?>
				</th>
				<th >
					<?php _e('Invited By', 'poule-tournament') ?>
				</th>
				<th>
					<?php _e('Actions', 'poule-tournament') ?>
				</th>
			</tr>
        </tfoot>
        
        <tbody>
		<?php if(count($subpoules) != 0):?>
        <?php foreach($subpoules as $subpoule): ?>
            <tr>
                <td id="invitation_name"><?php echo $subpoule['name']?></td>
                <td id="invitation_inviter"><?php echo $subpoule['inviter']?></td>
                <td id="invitation_">
                    <input type="button" id="accept_subpoule" pouleid="<?php echo $subpoule['id']?>" key="<?php echo $subpoule['key_accept']?>" class="btn btn-success" value="<?php _e('Accept', 'poule-tournament')?>"/>
                    <input type="button" id="delete_subpoule" pouleid="<?php echo $subpoule['id']?>" key="<?php echo $subpoule['key_delete']?>" class="btn btn-danger" value="<?php _e('Remove', 'poule-tournament')?>"/>
                </td>
            </tr>
        <?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="3"><?php _e('No invitations', 'poule-tournament');?></td>
			</tr>
		<?php endif; ?>
		</tbody>
    </table>