<?php 
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
   
?>


<div class="text-center">
    <?php do_action("poule_create_pagination"); ?>
</div>

<?php do_action('poule_before_official_score'); ?>


//loop met de functies nieuwe class voor de match 


<?php do_action('poule_after_official_score'); ?>





<?php exit(); ?>



<?php  if ($phase_id != null && count($groups) != 0) { ?>
	<?php foreach ($groups as $group) { ?>
		<div class="col-12 col-lg-12 col-sm-12">

			<div class="panel panel-default">

				<div class="panel-heading">
					<?php echo $group['group_name']; ?>
				</div>
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th style="word-wrap:break-word" class="visible-lg visible-md col-lg-3"><?php _e('Start Time', 'poule-tournament') ?></th>
								<th class="text-right col-lg-3"><?php _e('Country 1', 'poule-tournament') ?></th>
								<th class="text-right col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
								<th class="text-center">-</th>
								<th class="col-lg-1"><?php _e('Score', 'poule-tournament') ?></th>
								<th class="col-lg-3"><?php _e('Country 2', 'poule-tournament') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($group['matches'] as $match) { ?>
								<tr>
									<td style="word-wrap:break-word" class="visible-lg visible-md col-lg-3"><?php echo $match['start_time']; ?></td>
									<td><div class=" text-right"><?php echo $match['country1']; ?></div></td>
									<td>
										<div class="text-right text-center">
											<?php echo $match['score_1']; ?>
										</div>
									</td>
									<td class="text-center">-</td>
									<td>
										<div class="control-group text-center">
											<?php echo $match['score_2']; ?>
										</div>
									</td>
									<td class="col-lg-3"><?php echo $match['country2']; ?></td>
								</tr>

								<tr <?php echo $match['hidden']; ?>>
									<td></td>
									<td></td>
									<td colspan="3">
										<div class="text-center">
											<?php echo $match['penalty_country']; ?>
										</div>
									</td>
									<td></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php } ?>
<?php }else{?>
	<div class="alert alert-danger"><?php _e('No matches', 'poule-tournament') ?></div>
<?php } ?>
