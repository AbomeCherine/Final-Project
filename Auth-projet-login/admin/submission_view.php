<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$submission_id = $_GET['id'] ?? 0;

// Récupérer la soumission
$stmt = $pdo->prepare("
    SELECT s.*, f.title as form_title, f.form_type
    FROM submissions s
    JOIN forms f ON s.form_id = f.id
    WHERE s.id = ? AND f.organization_id = ?
");
$stmt->execute([$submission_id, $_SESSION['org_id']]);
$submission = $stmt->fetch();

if (!$submission) {
    die("Soumission non trouvée.");
}

$response_data = json_decode($submission['response_data'], true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail de la soumission - <?php echo htmlspecialchars($submission['form_title']); ?></title>
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
            transition: all 0.3s;
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .header h1 { color: #1a4d6b; font-size: 1.5em; }
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1a4d6b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-right: 10px;
        }
        .btn:hover { background: #2c6e9e; }
        .btn-back { background: #5a7a9a; }
        .btn-back:hover { background: #4a6a8a; }
        .response-item {
            padding: 15px;
            border-bottom: 1px solid #e0e8f0;
        }
        .response-question {
            font-weight: 600;
            color: #1a4d6b;
            margin-bottom: 8px;
        }
        .response-answer {
            color: #5a7a9a;
            background: #f8fafc;
            padding: 10px;
            border-radius: 8px;
            margin-top: 5px;
        }
        .score-card {
            background: #e8f0f5;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }
        .score-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #1a4d6b;
        }
        .score-label {
            color: #5a7a9a;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ANAC Aviation</h2>
        <a href="dashboard.php">Tableau de bord</a>
        <a href="forms.php">Formulaires</a>
        <a href="users.php">Utilisateurs</a>
        <a href="submissions.php" class="active">Soumissions</a>
        <a href="../logout.php">Déconnexion</a>
    </div>

    <div class="content">
        <div class="header">
            <h1><?php echo htmlspecialchars($submission['form_title']); ?></h1>
            <span>Soumission #<?php echo $submission['id']; ?></span>
        </div>

        <?php if ($submission['score'] !== null): ?>
        <div class="score-card">
            <div class="score-number"><?php echo $submission['score']; ?>/<?php echo $submission['max_score']; ?></div>
            <div class="score-label">Score</div>
            <div class="score-number" style="font-size: 1.5em;"><?php echo round($submission['compliance_pct'], 1); ?>%</div>
            <div class="score-label">Taux de conformité</div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3>Réponses</h3>
            <?php foreach ($response_data as $question => $answer): ?>
            <div class="response-item">
                <div class="response-question"><?php echo htmlspecialchars($question); ?></div>
                <div class="response-answer">
                    <?php 
                    if (is_array($answer)) {
                        echo htmlspecialchars(implode(', ', $answer));
                    } else {
                        echo htmlspecialchars($answer);
                    }
                    ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h3>Informations</h3>
            <p><strong>Soumis le :</strong> <?php echo $submission['submitted_at']; ?></p>
            <p><strong>Type de formulaire :</strong> <?php echo $submission['form_type']; ?></p>
        </div>

        <a href="submissions.php" class="btn btn-back">← Retour aux soumissions</a>
    </div>
</body>
</html>