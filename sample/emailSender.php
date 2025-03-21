#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');
require_once('vendor/autoload.php');


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendRegistrationEmail($email)
{
	$mail = new PHPMailer(true);
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
        
        $mail->isHTML(true);
        $mail->Subject = 'Registration COnfirmation';
	$mail->Body = 'Hello $username, \n\nWelcome to the Library of Integration! Your acccount has successfully been registered!';
	$mail->AltBody = "Thank you for regiserting with us.";
	
	//Send the email
	if (!$mail->send()) {
	    echo "Message could not be sent to $email. Mailer Error: {$mail->ErrorInfo}\n" . PHP_EOL;
	    return array('status' => 'error', 'message' => $mail->ErrorInfo);
	} else {
  	    echo "Message has been sent to $email\n" . PHP_EOL;
  	    return array('returnCode' => '0', 'message' => $mail->ErrorInfo);
	} 
}

function sendTwoFactorAuthEmail($email)
{
	$mail = new PHPMailer(true);
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
        
        $mail->isHTML(true);
        $mail->Subject = 'Confirm Login';
	$mail->Body = 'Hello $username, please confirm this is you.';
	//$mail->AltBody = "";
	
	//Send the email
	if (!$mail->send()) {
	    echo "Message could not be sent to $email. Mailer Error: {$mail->ErrorInfo}\n" . PHP_EOL;
	    return array('status' => 'error', 'message' => $mail->ErrorInfo);
	} else {
  	    echo "Message has been sent to $email\n" . PHP_EOL;
  	    return array('returnCode' => '0', 'message' => $mail->ErrorInfo);
	} 
}

function sendReadingGroupEmail($email, $groupName)
{
	$mail = new PHPMailer(true);
	//Server settings
	$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
	$mail->isSMTP();                                            //Send using SMTP
	$mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
	$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
	$mail->Username   = 'it490group@gmail.com';                 //SMTP username
	$mail->Password   = 'uyca cuvb qkvd gbkq';                        //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;       
	       
        $mail->setFrom('it490group@gmail.com', 'Mailer');     
        $mail->addReplyTo('it490group@gmail.com', 'Information'); 
        
        $mail->clearAddresses();
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Reading Group Confirmation';
	$mail->Body = 'Hello '.$email.', \n\nWelcome to the '.$groupName.'reading group!';
	$mail->AltBody = "";
	
	//Send the email
	if (!$mail->send()) {
	    echo "Message could not be sent to ".$email.". Mailer Error: {$mail->ErrorInfo}\n" . PHP_EOL;
	    return array('returnCode' => '2', 'message' => $mail->ErrorInfo);
	} else {
  	    echo "Message has been sent to ".$email."\n" . PHP_EOL;
  	    return array('returnCode' => '0', 'message' => $mail->ErrorInfo);
	} 
}


function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
  	return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "sendEmail":
    	return sendRegistrationEmail($request['username']);
  }
  case "sendgroupemail":
    	return sendReadingGroupEmail($request['username, groupname']);
  }  
  case "sendAuthenticaion":
  	return sendTwoFactorAuthEmail($request['username']);	
  }
}

$server = new rabbitMQServer("../rabbitMQ.ini","emailServer");
$server->process_requests('requestProcessor');
exit();
?>
