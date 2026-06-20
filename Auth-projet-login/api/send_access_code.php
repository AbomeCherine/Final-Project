<?php
require_once '../config.php';

function sendAccessCode($email, $firstName, $accessCode) {
    $subject = "Votre compte ANAC Aviation Portal a été créé";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; }
            .header { background: #1a4d6b; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 20px; }
            .code { background: #f0f2f5; padding: 15px; font-size: 24px; text-align: center; letter-spacing: 5px; border-radius: 8px; margin: 20px 0; }
            .btn { display: inline-block; padding: 12px 24px; background: #1a4d6b; color: white; text-decoration: none; border-radius: 8px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>ANAC Aviation Portal</h1>
            </div>
            <div class='content'>
                <h2>Bonjour $firstName,</h2>
                <p>Votre compte ANAC Aviation Portal a été créé.</p>
                <p><strong>Email :</strong> $email</p>
                <p><strong>Code d'accès :</strong></p>
                <div class='code'>$accessCode</div>
                <p>Connectez-vous sur : <a href='http://localhost:8000/login.php'>ANAC Aviation Portal</a></p>
                <a href='http://localhost:8000/login.php' class='btn'>Se connecter</a>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@anac-gabon.com\r\n";
    
    return mail($email, $subject, $message, $headers);
}
?>