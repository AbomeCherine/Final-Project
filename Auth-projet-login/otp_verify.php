<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['otp_contact'])) {
    header('Location: otp_request.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $contact = $_SESSION['otp_contact'];
    
    $stmt = $pdo->prepare("SELECT * FROM otp_sessions WHERE contact = ? AND used = 0 AND expires_at > GETDATE() ORDER BY id DESC");
    $stmt->execute([$contact]);
    $otp = $stmt->fetch();
    
    if ($otp && password_verify($code, $otp['code_hash'])) {
        $stmt = $pdo->prepare("UPDATE otp_sessions SET used = 1 WHERE id = ?");
        $stmt->execute([$otp['id']]);
        
        $_SESSION['respondent_id'] = $contact;
        $_SESSION['respondent_type'] = $otp['contact_type'];
        
        // Marquer l'invitation comme soumise si un token existe
        if (isset($_SESSION['invitation_token'])) {
            $stmt = $pdo->prepare("UPDATE form_invitations SET submitted_at = GETDATE(), status = 'submitted', submitted_by_email = ? WHERE token = ?");
            $stmt->execute([$contact, $_SESSION['invitation_token']]);
            unset($_SESSION['invitation_token']);
        }
        
        $form_id = $_GET['form_id'] ?? 1;
        header("Location: submit_form.php?id=$form_id");
        exit();
    } else {
        $error = "Code invalide ou expiré.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification - Aviation Portal</title>
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
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 { color: #1a4d6b; margin-bottom: 10px; }
        p { color: #5a7a9a; margin-bottom: 20px; font-size: 14px; }
        input {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #c0d4e8;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            letter-spacing: 5px;
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
            font-weight: 500;
            margin-top: 10px;
        }
        button:hover { background: #2c6e9e; }
        .error { color: #c0392b; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Vérification</h2>
        <p>Un code à 6 chiffres a été envoyé à <?php echo htmlspecialchars($_SESSION['otp_contact']); ?></p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="code" placeholder="000000" maxlength="6" autofocus required>
            <button type="submit">Vérifier</button>
        </form>
    </div>
</body>
</html>