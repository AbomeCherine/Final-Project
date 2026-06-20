<?php
require_once 'config.php';
require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';
require_once 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendFormEmail($to, $formTitle, $formLink) {
    $mail = new PHPMailer(true);
    try {
        // Configuration SMTP (à modifier avec tes identifiants)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';        // Serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'ton.email@gmail.com'; // Ton email
        $mail->Password = 'tonmotdepasse';       // Mot de passe
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Destinataires
        $mail->setFrom('ton.email@gmail.com', 'Aviation Portal');
        $mail->addAddress($to);
        
        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Formulaire à remplir : ' . $formTitle;
        $mail->Body = "
            <h2>Formulaire : $formTitle</h2>
            <p>Veuillez cliquer sur le lien ci-dessous pour remplir le formulaire :</p>
            <p><a href='$formLink'>$formLink</a></p>
            <p>Merci de votre participation.</p>
            <hr>
            <p style='font-size:12px;color:#888;'>Ce message a été envoyé automatiquement depuis la plateforme Aviation Portal.</p>
        ";
        $mail->AltBody = "Formulaire : $formTitle\n\nLien : $formLink";
        
        $mail->send();
        return ['success' => true, 'message' => 'Email envoyé avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Erreur: {$mail->ErrorInfo}"];
    }
}
?>