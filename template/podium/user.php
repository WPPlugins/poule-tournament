<?php 
$current_user = apply_filters("poule_get_user_info",array());
$score = apply_filters("poule_get_user_score",array());
$user = get_user_by( 'slug', $_GET['user'] );
$groups = apply_filters("poule_get_matches_own", $user->ID, 'phase-url');
?>


<h3><?php  echo $user->user_firstname . ' ' . $user->user_lastname; ?></h3>
<div class="text-center">
    <?php do_action("poule_create_pagination", 'phase-url'); ?>
</div>

<h3><?php _e('Points', 'poule-tournament');?></h3>
<table class="table">
	<thead>
		<tr>
			<th><?php _e('Phase name','poule-tournament'); ?></th>
			<th><?php _e('Score','poule-tournament'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($score as $phase){ ?>
		<tr>
			<td><?php echo $phase['name'] ?></td>
			<td><?php echo $phase['points'] ?></td>
			
		</tr>
		<?php } ?>
	</tbody>
</table>

<h3><?php _e('Predictions','poule-tournament');?></h3>

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
                        <th style="word-wrap:break-word" class="visible-lg visible-md col-lg-3"><?php _e('Start Time', 'poule-tournament') ?></th>
                        <th colspan="1" class="text-right col-lg-3"><?php _e('Country 1', 'poule-tournament') ?></th>
                        <th class="text-right col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th class="text-center">-</th>
                        <th class="col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th colspan="1" class="col-lg-3"><?php _e('Country 2', 'poule-tournament') ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th style="word-wrap:break-word" class="visible-lg visible-md col-lg-3"><?php _e('Start Time', 'poule-tournament') ?></th>
                        <th colspan="1" class="text-right col-lg-3"><?php _e('Country 1', 'poule-tournament') ?></th>
                        <th class="text-right col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th class="text-center">-</th>
                        <th class="col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
                        <th colspan="1" class="col-lg-3"><?php _e('Country 2', 'poule-tournament') ?></th>
                    </tr>
                </tfoot>
                
                <tbody>
                    <?php foreach($group['matches'] as $matchid => $match):?>
                    
                    <tr>
                        <td style="word-wrap:break-word" class="visible-lg visible-md col-lg-3"><?php echo $match['start_time']; ?></td>
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
								<div class="text-right">
									<?php echo $match['score1'] ?>
								</div>
                            </div>
                        </td>
                        <td class="text-center">-</td>
                        <td>
                            <div class="form-group <?php echo $match['error_score2'] ?> <?php //echo $match['visual_check_1']; ?>">
                                <div>
									<?php echo $match['score2'] ?>
								</div>
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
                        <td></td>
                        <td></td>
                        <td colspan="3">
                            <div class="text-center">
								<?php echo $match['penaltywinnar']?>
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