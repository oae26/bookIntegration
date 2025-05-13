#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('vendor/autoload.php');

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

//RabbitMQ connection
try {
	$client = new rabbitMQClient("../rabbitMQ.ini", "emailServer");
	
	$RMQrequest = array();
	$RMQrequest['type'] = 'getRegistrationEmail';
	$RMQresponse = $client->send_request($RMQrequest);
	
	if ($RMQresponse['returnCode'] === "0") {
		//emails retrieved
		$email = $RMQresponse['email'];
		
		//prepare email details
		$subject = 'Registration COnfirmation';
		$body = 'Hello $username, \n\nWelcome to the Library of Integration! Your acccount has successfully been registered!';
		
		//Server settings
		$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
		$mail->isSMTP();                                            //Send using SMTP
		$mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
	        $mail->Username   = 'it490group@gmail.com';                 //SMTP username
	        $mail->Password   = 'it490password';                        //SMTP password
	        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
	        $mail->Port       = 465;       
	       
    	        $mail->setFrom('it490group@gmail.com', 'Mailer');     
    	        $mail->addReplyTo('it490group@gmail.com', 'Information'); 
    	        
    	        $mail->Subject = $subject;
		$mail->Body = $body;
		$mail->AltBody = strip_tags($body);
		    	
		//Send the email
		if (!$mail->send()) {
		  echo "Message could not be sent to $email. Mailer Error: {$mail->ErrorInfo}\n";
		} else {
		   echo "Message has been sent to $email\n";
		}    
        } else {
        	echo "Failed to retrieve emails from database.\n";
        }
} catch (Exception $e) {
	echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
}

?>

