<?php
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include class file
require_once(dirname(__FILE__)."/../../ext/phpmailer/vendor/autoload.php");

/**
 * Utilises the PHPMailer class for sending emails.
 * 
 * 
 * @author Paul Cranner <paul.cranner@sunderland.ac.uk>
 * @copyright 2024 University of Sunderland
 * @license Proprietary
 * @version 1.0.0
 * @package UosCore
 */
class UoSMail extends PHPMailer {

	protected $smtpHost = 'smtp.sunderland.ac.uk';
	protected $smtpPort = 587;
	protected $smtpAuth = true;
	protected $smtpUser = 'svc_uks_web_smtp';
	protected $smtpPassword = 'Northern24Lights^Sky%';
	protected $smtpFromEmail = 'parkingservices@sunderland.ac.uk';
	protected $smtpFromName = 'Parking Services';
	protected $smtpOptions = array(
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
		)
	);

	function __construct($exceptions, $debug = false) {
		// Don't forget to do this or other things may not be set correctly!
		parent::__construct($exceptions);

		// SMTP debugging mode
		$this->SMTPDebug = SMTP::DEBUG_OFF;
		if ($debug) $this->SMTPDebug = SMTP::DEBUG_SERVER;

		// Send via SMTP
		$this->isSMTP();

		// SMTP settings
		$this->Host = $this->smtpHost;
		$this->Port = $this->smtpPort;
		$this->SMTPAuth = $this->smtpAuth;
		$this->Username = $this->smtpUser;
		$this->Password = $this->smtpPassword;
		$this->setFrom($this->smtpFromEmail, $this->smtpFromName);
		$this->SMTPOptions = $this->smtpOptions;
	}

	// Extend the send function
    public function send()
    {
        $result = parent::send();
        //echo 'I sent a message with subject ' . $this->Subject;

        return $result;
    }
}
?>