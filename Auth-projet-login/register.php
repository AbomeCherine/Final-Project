<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (strlen($password) < 6) {
        $error = "Mot de passe (min 6 caractères).";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        
        if ($check->rowCount() > 0) {
            $error = "Nom ou email déjà utilisé.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, role, organization_id) VALUES (?, ?, ?, 'admin', 1)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$username, $email, $hashed])) {
                $success = "Inscription réussie ! Vous pouvez vous connecter.";
            } else {
                $error = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Aviation Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0a1928 0%, #1d4a6e 50%, #2c6e9e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 100%;
        }
        h2 { text-align: center; color: #1a4d6b; margin-bottom: 10px; }
        input {
            width: 100%;
            padding: 14px;
            margin: 8px 0;
            border: 1px solid #c0d4e8;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #1a4d6b;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 15px;
        }
        .error { color: #c0392b; margin: 10px 0; }
        .success { color: #27ae60; margin: 10px 0; }
        .link { text-align: center; margin-top: 20px; }
        a { color: #1a4d6b; text-decoration: none; }
        .skiptranslate { display: none !important; }
        body { top: 0px !important; }
        .goog-te-gadget { font-family: 'Poppins', sans-serif !important; background: white; padding: 5px 10px; border-radius: 30px; }
        .goog-te-combo { padding: 6px 12px; border-radius: 20px; border: 1px solid #c0d4e8; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe (min 6 caractères)" required>
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>
        <div class="link">
            Déjà un compte ? <a href="login.php">Se connecter</a>
        </div>
    </div>

    
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>