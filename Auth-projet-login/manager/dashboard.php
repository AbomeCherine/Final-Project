<?php
session_start();
require_once '../config.php';
require_once '../lang.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: ../login.php');
    exit();
}

$current_lang = $_SESSION['lang'] ?? 'fr';

// Récupérer les notifications non lues
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Marquer toutes les notifications comme lues
$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);

// Récupérer les statistiques
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_submissions 
    FROM submissions s
    JOIN forms f ON s.form_id = f.id
    WHERE f.organization_id = ?
");
$stmt->execute([$_SESSION['org_id']]);
$stats = $stmt->fetch();

// Récupérer les dernières soumissions
$stmt = $pdo->prepare("
    SELECT s.id, s.submitted_at, f.title as form_title,
           CASE 
               WHEN s.respondent_type = 'staff' THEN COALESCE(u.username, 'Staff inconnu')
               ELSE 'Public (OTP)'
           END as submitted_by
    FROM submissions s
    JOIN forms f ON s.form_id = f.id
    LEFT JOIN users u ON s.submitted_by_user_id = u.id
    WHERE f.organization_id = ?
    ORDER BY s.submitted_at DESC
    OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY
");
$stmt->execute([$_SESSION['org_id']]);
$recentSubmissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard - <?php echo __('aviation_portal'); ?></title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #1a4d6b;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .notification-item {
            padding: 12px;
            border-bottom: 1px solid #e0e8f0;
            background: #f0f8ff;
            margin-bottom: 8px;
            border-radius: 8px;
        }
        .notification-item p {
            margin: 0;
            font-size: 14px;
        }
        .notification-item small {
            color: #5a7a9a;
            font-size: 11px;
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
        .btn {
            display: inline-block;
            padding: 6px 15px;
            background: #1a4d6b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php echo language_switcher(); ?>
    
    <div class="sidebar">
        <h2>Manager</h2>
        <a href="dashboard.php" class="active"><?php echo __('dashboard'); ?></a>
        <a href="../logout.php"><?php echo __('logout'); ?></a>
    </div>

    <div class="content">
        <div class="header">
            <h1>Tableau de bord Manager</h1>
            <span><?php echo __('welcome'); ?>, <?php echo htmlspecialchars($_SESSION['username']); ?> (Manager)</span>
        </div>

        <!-- Notifications -->
        <div class="card">
            <h3>Notifications</h3>
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item">
                        <p>🔔 <?php echo htmlspecialchars($notif['message']); ?></p>
                        <small><?php echo $notif['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune nouvelle notification.</p>
            <?php endif; ?>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_submissions']; ?></div>
                <div class="stat-label">Total soumissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($recentSubmissions); ?></div>
                <div class="stat-label">Dernières soumissions</div>
            </div>
        </div>

        <div class="card">
            <h3>Dernières soumissions</h3>
            <?php if (count($recentSubmissions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Formulaire</th>
                        <th>Soumis par</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSubmissions as $sub): ?>
                    <tr>
                        <td><?php echo $sub['id']; ?></td>
                        <td><?php echo htmlspecialchars($sub['form_title']); ?></td>
                        <td><?php echo htmlspecialchars($sub['submitted_by']); ?></td>
                        <td><?php echo $sub['submitted_at']; ?></td>
                        <td><a href="../admin/submission_view.php?id=<?php echo $sub['id']; ?>" class="btn">Voir</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>Aucune soumission pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>