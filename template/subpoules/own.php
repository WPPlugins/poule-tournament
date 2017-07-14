<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
    $my_poules = new poule_subpoule();
    $current_user = wp_get_current_user();
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th><?php _e('Name', 'poule-tournament')?></th>
            <th><?php _e('Date Create', 'poule-tournament')?></th>
            <th><?php _e('Count Users', 'poule-tournament')?></th>
            <th><?php _e('Actions', 'poule-tournament')?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th><?php _e('Name', 'poule-tournament')?></th>
            <th><?php _e('Date Create', 'poule-tournament')?></th>
            <th><?php _e('Count Users', 'poule-tournament')?></th>
            <th><?php _e('Actions', 'poule-tournament')?></th>
        </tr>
    </tfoot>
    <tbody>
        <?php foreach($my_poules->poules() as $subpoule):?>
        <tr>
            <td><?php echo $subpoule->post_title; ?></td>
            <td><?php echo $subpoule->post_date; ?></td>
            <td><?php echo $subpoule->users; ?></td>
            <td>
                <button type="button" class="btn btn-info" id="edit_group" sub-poule-id="<?php echo $subpoule->ID; ?>"><?php _e('Edit', 'poule-tournament');?></button>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>


<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_subpoule"><?php _e('Add', 'poule-tournament');?></button>

<!-- Modal add -->
<div class="modal fade" id="add_subpoule" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?php _e('Add a subpoule','poule-tournament'); ?></h4>
            </div>
            <div class="modal-body">
        
                <form method="POST" id="subpoule_form_add">
                    <h3><?php _e("General");?></h3>
                    <div id="own_name" class="form-group">
                        <input type="text" class="form-control " name="name" placeholder="<?php _e('Subpoule name','poule-tournament')?>"/>
                    </div>
                    <div id="own_description" class="form-group">
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                    <input type="hidden" hidden="hidden" name="action" id="action" value="poule_subpoule_add"/>
                </form>
                <p>
                    <?php _e("Users can added by the edit page.", 'poule-tournament'); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'poule-tournament')?></button>
                <button type="button" id="subpoule_add" class="btn btn-primary"><?php _e('Add subpoule', 'poule-tournament')?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal edit -->
<div class="modal fade" id="edit_subpoule" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?php _e('Edit a subpoule','Poule-tournament'); ?></h4>
            </div>
            <div class="modal-body">
        
                <form method="POST" id="subpoule_form_edit">
                    <h3><?php _e("General", 'poule-tournament');?></h3>
                    <div id="own_name" class="form-group">
                        <input type="text" class="form-control " name="name" id="edit_name" placeholder="<?php _e('Subpoule name','poule-tournament')?>"/>
                    </div>
                    <div id="own_description" class="form-group">
                        <textarea name="description" id="edit_description" class="form-control" rows="4" placeholder="<?php _e('Description', 'poule-tournament');?>"></textarea>
                    </div>
                    
                    <br />
                    <h3><?php _e("Users");?></h3>
                    <div class="row">
                        <div class="col-xs-9">
                            <input type="text" name="" id="user" class="form-control"/>
                        </div>
                        <div class="col-xs-3 text-right">
                            <input type="button" id="add_user_edit" class="btn btn-default" value="<?php _e('Add','poule-tournament')?>"/>
                            <!--<button class="btn" data-toggle="modal" href="#add_user">Launch modal</button>-->

                        </div>
                    </div>
                    
                    <table id="users" class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'poule-tournament')?></th>
                                <th><?php _e('Status', 'poule-tournament')?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th><?php _e('Name', 'poule-tournament')?></th>
                                <th><?php _e('Status', 'poule-tournament')?></th>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
                    </table>
                    <input type="hidden" hidden="hidden" name="action" id="action" value="poule_subpoule_edit"/>
                    <input type="hidden" hidden="hidden" name="poule_id" id="poule_id" value=""/>
                </form>
          
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'poule-tournament')?></button>
                <button type="button" id="subpoule_edit" class="btn btn-primary"><?php _e('Save', 'poule-tournament')?></button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="add_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?php _e('Add a subpoule','poule-tournament'); ?></h4>
            </div>
            <div class="modal-body">
                
                <p id="message_add_user">
                    
                </p>
                
                <form method="POST" id="subpoule_form_add">
                    <h3><?php _e("General", 'poule-tournament');?></h3>
                    <div id="own_name" class="form-group">
                        <input type="text" class="form-control" id="full_name" placeholder="<?php _e('Full name', 'poule-tournament'); ?>"/>
                    </div>
                    <div id="own_name" class="form-group">
                    <input type="email" class="form-control" id="user_email" placeholder="<?php _e('Email', 'poule-tournament'); ?>"/>
                    </div>
                    <div id="description" class="form-group">
                        <textarea name="description" id="email_message" class="form-control" rows="4" placeholder="<?php _e('Description', 'poule-tournament')?>"></textarea>
                    </div>
                </form>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'poule-tournament'); ?></button>
                <button type="button" id="subpoule_add_user" class="btn btn-primary"><?php _e('Save', 'poule-tournament')?></button>
            </div>
        </div>
    </div>
</div>