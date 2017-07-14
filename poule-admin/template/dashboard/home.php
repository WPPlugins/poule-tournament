<?php 
/**
 * Templatefile
 * 
 * Dashboard home page
 * 
 * @version 2
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */
?>

<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly  ?>

<!--<div class="activity-block">-->

	<?php $table_match->display();?>
	
<!--</div>-->

<style>
	
	div.tablenav.top, div.tablenav.bottom{
		display: none;
	}
	
</style>