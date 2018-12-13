<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {
	public static function sendEmail($to, $subject, $body) {
		//
		if ( !is_array($to) ) {
			$to = array( $to );
		}

		// try to send email
		$mail = new PHPMailer(true);
		try {
			// server settings for the mail
			$mail->SMTPDebug = 0;                                       // TODO Enable verbose debug output
			//$mail->SMTPDebug = 3;                                       // TODO Enable verbose debug output
			$mail->isSMTP();                                            // Set mailer to use SMTP

			// fix for certificate problem
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);

			$mail->Host = trim(Settings::get('mail_host'));                   // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                                     // Enable SMTP authentication
			$mail->Username = trim(Settings::get('mail_username'));           // SMTP username
			$mail->Password = trim(Settings::get('mail_password'));           // SMTP password
//			$mail->SMTPSecure = 'tls';                                // Enable TLS encryption, `ssl` also accepted
//			$mail->SMTPSecure = 'ssl';                                // Enable TLS encryption, `ssl` also accepted
			$mail->Port = trim(Settings::get('mail_port'));                   // TCP port to connect to

			// set recipients and senders on the mail
			$mail->setFrom(trim(Settings::get('email_sender_email')), trim(Settings::get('email_sender_name')));
			foreach ($to as $rec) {
				$mail->addAddress($rec);
			}
			$mail->isHTML(false);
			$mail->Subject = $subject;
			$mail->Body = $body;

			if (!$mail->send()) {
				$m = 'Error 954278: failed sending mail to: ' . implode(',', $to);
				print_r($m);
				// log error
				error_log($m);
			} else {
			}
		} catch (Exception $e) {
			$m = 'Cannot send email (error 347893): ' . $e->getMessage();
			print_r( $m );
			// log exception
			error_log($m );
		}
	}
}
