<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

  <?php do_action('poule_email_header');?>
    <body>
        
        <div class="top_header text-center">
            DATA DATA
        </div>
        
        <div class="container" style="">
            <div class="header text-center">
                <h1>TITEL</h1>
            </div>
            
            <div class="column">
                
                Beste {naam}
                
                Je bent door {naam van uitnodiger} om ook mee te doen aan een subpoule.
                
                {naam subpoule}
                
                {beschrijving subpoule}
                
                Met vriendelijke groet,
                {van gegevens website}
                <?php //echo $message['message_text']; ?>
            </div>
            
            <div class="">
                
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <a href="<?php echo $message['delete_link'] ?>" class="btn btn-success">knop groen</a>
                    </div>
                    
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <a href="<?php echo $message['accept_link']; ?>" class="btn btn-danger">knop rood</a>
                    </div>
                </div>
                
            </div>
            
            <div class="footer text-center">
                <h1>TITEL</h1>
            </div>
            
        </div>
	</body>
	<?php do_action('poule_email_footer');?>