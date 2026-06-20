<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config.php';
require_once '../lang.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT f.*, (SELECT COUNT(*) FROM form_fields WHERE form_id = f.id) as nb_questions
    FROM forms f
    WHERE f.organization_id = ? AND f.is_published = 1
    ORDER BY f.created_at DESC
");
$stmt->execute([$_SESSION['org_id']]);
$forms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes formulaires - Aviation Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
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
        .header h1 { color: #1a4d6b; }
        .logout-btn {
            background: #e8d0d0;
            color: #8b5e5e;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        .form-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
        }
        .form-card h3 { color: #1a4d6b; margin-bottom: 10px; }
        .btn {
            display: inline-block;
            padding: 8px 20px;
            background: #1a4d6b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .badge {
            background: #e8f0fe;
            color: #1a73e8;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Mes formulaires</h1>
        <div>
            <span>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>

    <div class="cards-grid">
        <?php if (count($forms) > 0): ?>
            <?php foreach ($forms as $form): ?>
            <div class="form-card">
                <h3><?php echo htmlspecialchars($form['title']); ?></h3>
                <p><?php echo htmlspecialchars($form['description']); ?></p>
                <div class="badge"><?php echo $form['nb_questions']; ?> questions</div>
                <a href="../submit_form.php?id=<?php echo $form['id']; ?>" class="btn">Remplir</a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun formulaire disponible.</p>
        <?php endif; ?>
    </div>
</body>
</html>