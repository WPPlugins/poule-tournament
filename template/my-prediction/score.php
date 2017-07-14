<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
    
    $groups = apply_filters("poule_get_matches_own","");
?>

<div class="text-center">
    <?php do_action("poule_create_pagination"); ?>
</div>

<?php //do_action('poule_thank_you_message',$groups); ?>

<?php do_action('poule_before_own_score',$groups); ?>


<?php if(is_user_logged_in()):?>
<form method="POST" action="">
<?php foreach($groups as $groupid => $group): ?>
    
<!--<div class="col-12 col-lg-12 col-sm-12">-->
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo $group['group_name']; ?>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover score">
                <thead>
                    <tr>
                        <th style="word-wrap:break-word" class="col-lg-3"><?php _e('Start Time', 'poule-tournament') ?></th>
                        <th colspan="1" class="text-right col-lg-3"><?php _e('Country 1', 'poule-tournament') ?></th>
                        <th class="text-right col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th class="text-center">-</th>
                        <th class="col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th colspan="1" class="col-lg-3"><?php _e('Country 2', 'poule-tournament') ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th style="word-wrap:break-word" class="col-lg-3"><?php _e('Start Time', 'poule-tournament') ?></th>
                        <th colspan="1" class="text-right col-lg-3"><?php _e('Country 1', 'poule-tournament') ?></th>
                        <th class="text-right col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th class="text-center">-</th>
                        <th class="col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th colspan="1" class="col-lg-3"><?php _e('Country 2', 'poule-tournament') ?></th>
                    </tr>
                </tfoot>
                
                <tbody>
                    <?php foreach($group['matches'] as $matchid => $match): ?>
					
				
                    
                    <tr>
                        <td style="word-wrap:break-word" class=" col-lg-3"><?php echo $match['start_time']; ?></td>
<!--                        <td>
                            <img src="<?php echo $match['flag_country1']; ?>" alt="" style="width:45px;">
                        </td>-->
                        <td>
                            <div class=" text-right">
                                <?php echo $match['country1']; ?>
                            </div>
                        </td>
                        <td>
                            <div class="form-group <?php echo $match['error_score1'] ?> <?php //echo $match['visual_check_1']; ?>">
                                <input type="number" min="0" max="20" class="inputscore form-control text-right " <?php echo $match['readonly'] ?> name="score[<?php echo $match['row'] ?>][score1]" id="score_<?php echo $match['row'] ?>_1" value="<?php echo $match['score1'] ?>" match_id="<?php echo $match['row'] ?>" number="1"/>
                            </div>
                        </td>
                        <td class="text-center">-</td>
                        <td>
                            <div class="form-group <?php echo $match['error_score2'] ?> <?php //echo $match['visual_check_1']; ?>">
                                <input type="number" min="0" max="20" class="inputscore form-control " <?php echo $match['readonly'] ?> name="score[<?php echo $match['row'] ?>][score2]" id="score_<?php echo $match['row'] ?>_2" value="<?php echo $match['score2'] ?>" match_id="<?php echo $match['row'] ?>" number="2"/>
                            </div>
                        </td>
                        <td class="col-lg-3">
                            <?php echo $match['country2']; ?>
                        </td>
<!--                        <td>
                            <img src="<?php echo $match['flag_country2']; ?>" alt="" style="width:45px;">
                        </td>-->
                    </tr>
                    
                    <?php // $hiden = ($match['score1'] == $match['score2'] && $match['score1'] != "" && $match['score1'] != null  && $match['score1'] != 0 ) ? 'hidden="hidden"' : '' ;?>
                    
                    <tr id="hidden_<?php echo $match['row'] ?>" <?php echo $match['hidden']; ?>>
                        <td class="visible-lg visible-md col-lg-3"></td>
                        <td></td>
                        <td colspan="3">
                            <div class="text-center">
                                <select name="score[<?php echo $match['row'] ?>][penalty]" class="form-control <?php echo $match['error_penalty'] ?>" <?php echo $match['readonly'] ?> <?php // echo $match['visual_check_penalty']; ?>>
                                    <option></option>
                                    <?php echo $match['readonly'] ?>
                                    <?php foreach ($match['penalty'] as $country) { ?>
                                        <option value="<?php echo $country['id'] ?>" <?php echo $country['select'] ?>><?php echo $country['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                    
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
<!--</div>-->
<?php endforeach; ?>

    <input type="submit" class="btn btn-primary" value="<?php _e('Save');?>"/>
    
</form>
<?php endif; ?>
<?php do_action('poule_after_own_score'); ?>