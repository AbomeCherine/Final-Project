<?php
session_start();
require_once 'config.php';

$form_id = $_GET['form_id'] ?? 0;
$token = $_GET['token'] ?? '';

// Vérifier si le token est valide
$invitation = null;
if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM form_invitations WHERE token = ? AND form_id = ? AND status != 'submitted'");
    $stmt->execute([$token, $form_id]);
    $invitation = $stmt->fetch();
    
    if (!$invitation) {
        die("Lien invalide ou déjà utilisé.");
    }
    
    // Marquer comme ouvert
    if (!$invitation['opened_at']) {
        $stmt = $pdo->prepare("UPDATE form_invitations SET opened_at = GETDATE() WHERE id = ?");
        $stmt->execute([$invitation['id']]);
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = trim($_POST['contact']);
    $contact_type = $_POST['contact_type'];
    
    if (empty($contact)) {
        $error = "Veuillez entrer votre " . ($contact_type === 'phone' ? 'numéro' : 'email');
    } else {
        // Vérifier si c'est une invitation avec token
        if ($invitation && $invitation['contact'] === $contact) {
            // C'est le bon contact, on continue
            $code = sprintf("%06d", mt_rand(1, 999999));
            $code_hash = password_hash($code, PASSWORD_DEFAULT);
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            
            $stmt = $pdo->prepare("DELETE FROM otp_sessions WHERE contact = ? AND used = 0");
            $stmt->execute([$contact]);
            
            $stmt = $pdo->prepare("INSERT INTO otp_sessions (contact, contact_type, code_hash, expires_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$contact, $contact_type, $code_hash, $expires_at]);
            
            $_SESSION['otp_contact'] = $contact;
            $_SESSION['otp_type'] = $contact_type;
            $_SESSION['invitation_token'] = $token;
            
            header("Location: otp_verify.php?form_id=$form_id");
            exit();
        } else {
            $error = "Le contact ne correspond pas à l'invitation.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Authentification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        h2 { color: #1a4d6b; margin-bottom: 10px; }
        p { color: #5a7a9a; margin-bottom: 30px; }
        .info-box {
            background: #e8f0f5;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #c0d4e8;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #1a4d6b;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        .error { color: #c0392b; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Authentification</h2>
        
        <?php if ($invitation): ?>
            <div class="info-box">
                <p>Formulaire invité pour : <strong><?php echo htmlspecialchars($invitation['contact']); ?></strong></p>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="contact_type" value="email">
            <input type="email" name="contact" placeholder="Votre email" value="<?php echo htmlspecialchars($invitation['contact'] ?? ''); ?>" required>
            <button type="submit">Recevoir le code</button>
        </form>
    </div>
</body>
</html>