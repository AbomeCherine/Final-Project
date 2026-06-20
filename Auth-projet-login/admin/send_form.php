<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$form_id = $_GET['id'] ?? 0;
$error = '';
$success = '';
$recipients = [];

// Récupérer le formulaire
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ? AND organization_id = ?");
$stmt->execute([$form_id, $_SESSION['org_id']]);
$form = $stmt->fetch();

if (!$form) {
    die("Formulaire non trouvé.");
}

// Récupérer la liste des répondants déjà invités
$stmt = $pdo->prepare("SELECT * FROM form_invitations WHERE form_id = ? ORDER BY sent_at DESC");
$stmt->execute([$form_id]);
$invitations = $stmt->fetchAll();

// Traitement de l'envoi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipients_raw = trim($_POST['recipients']);
    $method = $_POST['method'];
    
    if (empty($recipients_raw)) {
        $error = "Veuillez entrer au moins un destinataire.";
    } else {
        // Séparer les destinataires (par ligne ou par virgule)
        $recipients = preg_split('/[\n,]+/', $recipients_raw);
        
        foreach ($recipients as $contact) {
            $contact = trim($contact);
            if (empty($contact)) continue;
            
            // Générer un token unique
            $token = bin2hex(random_bytes(16));
            $form_url = "http://localhost:8000/otp_request.php?form_id=" . $form_id . "&token=" . $token;
            
            // Déterminer le type (phone ou email)
            $contact_type = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            
            // Sauvegarder l'invitation
            $stmt = $pdo->prepare("
                INSERT INTO form_invitations (form_id, contact, contact_type, token, status) 
                VALUES (?, ?, ?, ?, 'sent')
            ");
            $stmt->execute([$form_id, $contact, $contact_type, $token]);
            
            // Ici : envoyer réellement par email ou SMS
            // Pour l'instant, on simule
            if ($contact_type === 'email') {
                // Envoyer email (à implémenter avec mail() ou PHPMailer)
                $subject = "Formulaire: " . $form['title'];
                $message = "Bonjour,\n\nVous êtes invité à remplir le formulaire suivant:\n\n";
                $message .= "Titre: " . $form['title'] . "\n";
                $message .= "Description: " . $form['description'] . "\n\n";
                $message .= "Lien: " . $form_url . "\n\n";
                $message .= "Merci de votre participation.";
                // mail($contact, $subject, $message);
            } else {
                // Envoyer SMS (à implémenter avec API SMS)
                // Ici on ne fait rien pour l'instant
            }
        }
        
        $success = count($recipients) . " invitation(s) envoyée(s).";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Envoyer le formulaire - <?php echo htmlspecialchars($form['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: #0a1928;
            position: fixed;
            height: 100vh;
            padding: 25px;
        }
        .sidebar h2 {
            color: white;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #1d4a6e;
        }
        .sidebar a {
            display: block;
            color: #a0c0d8;
            padding: 12px 15px;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 8px;
        }
        .sidebar a:hover {
            background: #1a4d6b;
            color: white;
        }
        .content {
            margin-left: 280px;
            padding: 30px;
        }
        .header {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { color: #1a4d6b; font-size: 1.5em; }
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #1a4d6b;
        }
        textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #c0d4e8;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 20px;
        }
        textarea { height: 150px; }
        .btn {
            background: #1a4d6b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn:hover { background: #2c6e9e; }
        .btn-back {
            background: #5a7a9a;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e8f0;
        }
        th {
            background: #f0f5f8;
            color: #1a4d6b;
        }
        .status-sent { color: #27ae60; }
        .status-opened { color: #2980b9; }
        .status-submitted { color: #8e44ad; }
        .error { color: #c0392b; margin-bottom: 15px; }
        .success { color: #27ae60; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ANAC Aviation</h2>
        <a href="dashboard.php">Tableau de bord</a>
        <a href="forms.php">Formulaires</a>
        <a href="users.php">Utilisateurs</a>
        <a href="submissions.php">Soumissions</a>
        <a href="../logout.php">Déconnexion</a>
    </div>

    <div class="content">
        <div class="header">
            <h1>Envoyer : <?php echo htmlspecialchars($form['title']); ?></h1>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>

        <a href="forms.php" class="btn btn-back">← Retour aux formulaires</a>

        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Envoyer des invitations</h3>
            <form method="POST">
                <label>Destinataires (un par ligne ou séparés par des virgules)</label>
                <textarea name="recipients" placeholder="exemple@gmail.com&#10;0612345678&#10;autre@domaine.com" required></textarea>
                
                <label>Mode d'envoi</label>
                <select name="method">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="both">Email + SMS</option>
                </select>
                
                <button type="submit" class="btn">Envoyer les invitations</button>
            </form>
        </div>

        <?php if (count($invitations) > 0): ?>
        <div class="card">
            <h3>Historique des invitations</h3>
            <table>
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invitations as $inv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inv['contact']); ?></td>
                        <td><?php echo $inv['contact_type']; ?></td>
                        <td><?php echo $inv['sent_at']; ?></td>
                        <td>
                            <span class="status-<?php echo $inv['status']; ?>">
                                <?php echo $inv['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>