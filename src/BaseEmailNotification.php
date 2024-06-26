<?php
namespace Simpluity\Simpluity;

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class BaseEmailNotifications {

    public $email;
    public $password;
    public function __construct($email, $pass) {
        $this->email = $email;
        $this->password = $pass;
    }
    // $emailList = > array [["email" => "noncre123@gmail.com", "name" => 'EHEHE']]
    // $attachmentList => [["link" => "noncre123@gmail.com", "name" => 'EHEHE']]
    public function sendGmail($emailList, $subject,$body) {

        try {
            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = 0 ;                      //Enable verbose debug output -- SMTP::DEBUG_SERVER
            $mail->isSMTP();                                        
            $mail->Host       = 'smtp.gmail.com';                   
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = $this->email;                                  //SMTP username
            $mail->Password   = $this->password;                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('from@example.com', 'Mailer');
            foreach ($emailList as $contact) {
                $mail->addAddress($contact["email"], $contact["name"]);
            }

            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }


    }
    public function sendGmailWithAttachments($emailList, $attachmentList,$body, $subject) {

        try {
            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = 0 ;                      //Enable verbose debug output -- SMTP::DEBUG_SERVER
            $mail->isSMTP();                                        
            $mail->Host       = 'smtp.gmail.com';                   
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = $this->email;                                  //SMTP username
            $mail->Password   = $this->password;                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('from@example.com', 'Mailer');
            foreach ($emailList as $contact) {
                $mail->addAddress($contact["email"], $contact["name"]);
            }
            foreach ($attachmentList as $attachments) {
                // Assuming you want to echo or do something with each email address
                if(isset($attachments[1])){
                    $mail->addAttachment($attachments["link"], $attachments["name"]); 
                }else{
                    $mail->addAttachment($attachments["link"]); 
                }
            }

            $mail->isHTML(true);                                  
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }


    }
}