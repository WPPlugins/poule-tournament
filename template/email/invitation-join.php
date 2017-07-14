<?php do_action('poule_email_header',$message);?>
<p><?php echo __('Dear: ', 'poule-tournament') . $message['full_name'] ?></p>

<p><?php echo apply_filters('the_content',$message['email_message']); ?></p>

<hr>

<p><?php echo $message['email_message']; ?></p>

<div style="background-color:#fdfdfd;display:block;border:1px solid black;border-radius:0px!important; padding:15px;text-align: center;">
<h2 style="color:#505050; display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:0px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:center;line-height:150%">
	<?php echo $message['subpoule_title']; ?>
</h2>
<p><?php echo $message['subpoule_description']; ?></p>
<div style="text-align: center;">
	<a href="<?php echo $message['accept_link']?>" style="display: inline-block;border-radius:6px!important;border:1px solid black; background-color: green; padding: 10px; width: 75%!important;"><?php echo _e('Accept', 'poule-tournament') ?></a>
	<a href="<?php echo $message['delete_link']?>" style="display: inline-block;border-radius:6px!important;border:1px solid black; background-color: red; margin-top: 5px!important; padding: 10px; width: 75% !important"><?php echo _e('Delete', 'poule-tournament') ?></a>
</div>
</div>
<?php do_action('poule_email_footer',$message);?>