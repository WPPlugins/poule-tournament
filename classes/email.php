<?php 
/**
 * This file send the emails to the users
 * 
 * It send the emails
 * 
 * @author Stefan de Bruin <info@stefandebruin.eu>
 * @filesource
 * @package poule_tournament
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * send
 * 
 * Send the email
 * 
 * @since version 2.2
 * @version 1
 * @author Stefan de Bruin <info@stefandebruin.eu>
 */
class email {
	
	/**
	 * Email address to
	 * 
	 * The email address to send the email to
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @var string|array
	 */
	public $to;
	
	/**
	 * Email subject
	 * 
	 * Subject of the email
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
	public $subject;
	
	/**
	 * Email from
	 * 
	 * Email from email address
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string|array
	 */
	public $from = "";
	
	/**
	 * attachment
	 * 
	 * Al;l the attachments
	 * 
	 * @access public
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var array
	 */
	public $attachment = array();
	
	/**
	 * Email message
	 * 
	 * The full email content
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
	private $message;
	
	/**
	 * File name
	 * 
	 * The email file name
	 * 
	 * @access private
	 * @since version 2.2
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @version 1
	 * @var string
	 */
	private $email;
	
	/**
	 * constructor
	 * 
	 * The constructor for the class for the plugin. Its add the hooks and filters for wordpress
	 * 
	 * @package poule_tournament
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @since version 2.2
	 * @version 1
	 * @access public
	 */
	public function __construct() {
        
    }
    
	/**
	 * Create the email content
	 * 
	 * Open the content file and do the functions in the file.
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @param string $file the filename
	 * @param array $message replace text
	 */
    public function content($file, $message = array()){
        $this->email = $file;
		$settings = get_option( 'poule_settings',array() );
		$message['email_title'] = array_key_exists($file.'_title',$settings) ? $settings[$file.'_title'] : "";
		$message['email_message'] = array_key_exists($file.'_content',$settings) ? $settings[$file.'_content'] : "" ;
		ob_start();
		
		if (file_exists(Poule_Tournament::$template_path . "email/$file.php")) {
			include_once Poule_Tournament::$template_path . "email/$file.php";
		}else{
			include_once POULE_PATH . "template/email/$file.php";
		}

		$this->message = ob_get_contents();
		ob_end_clean();
    }
	
	/**
	 * Send the email to the user
	 * 
	 * Send the email with the class parameters as data
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @return boolean check if email is send
	 */
	public function send(){
		$settings = get_option( 'poule_settings',array() );
		if(array_key_exists('email_from_email',$settings) && $settings['email_from_email'] != ""){
			if (filter_var($settings['email_from_email'], FILTER_VALIDATE_EMAIL)) {
				$from_email = $settings['email_from_email'];
			}else if($settings['email_from_email'] == '{admin_email}' || !filter_var($settings['email_from_email'], FILTER_VALIDATE_EMAIL) ){
				$from_email = get_option('admin_email');
			}
		}else{
			$from_email = get_option('admin_email');
		}
		
		if(isset($settings['email_from_name']) && $settings['email_from_name'] != ""){
			if($settings['email_from_name'] == "{website_title}"){
				$from_name = get_option('blogname');
			}else{
				$from_name = $settings['email_from_name'];
			}
		}else{
			$from_name = $settings['email_from_name'];
		}
		
		$headers = 'From: '.$from_name .'<'.$from_email.'>' . "\r\n";
		
		add_filter( 'wp_mail_content_type', array($this,'content_type'));
		
		$result = wp_mail($this->to, $this->subject, $this->message, $headers, $this->attachment );
		
		$errors = '';
		
		if (!$result) {
			global $ts_mail_errors;
			global $phpmailer;
			if (!isset($ts_mail_errors)) $ts_mail_errors = array();
			if (isset($phpmailer)) {
				$ts_mail_errors[] = $phpmailer->ErrorInfo;
			}
			$errors = $phpmailer;
		}
		
		remove_filter( 'wp_mail_content_type', array($this,'content_type'));
		
		return ($errors != "") ? $errors : TRUE;
	}
	/**
	 * Change email content
	 * 
	 * change email content type to html
	 * 
	 * @access public
	 * @author Stefan de Bruin <info@stefandebruin.eu>
	 * @package poule_tournament
	 * @since version 2.2
	 * @version 1
	 * @return string
	 */
	public function content_type(){
		return 'text/html';
	}
}

new email();

?>