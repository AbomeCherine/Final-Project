<?php
session_start();
require_once '../config.php';
require_once '../lang.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$current_lang = $_SESSION['lang'] ?? 'fr';

$stats = [];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM forms WHERE organization_id = ?");
$stmt->execute([$_SESSION['org_id']]);
$stats['forms'] = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM submissions s JOIN forms f ON s.form_id = f.id WHERE f.organization_id = ?");
$stmt->execute([$_SESSION['org_id']]);
$stats['submissions'] = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE organization_id = ? AND role = 'staff'");
$stmt->execute([$_SESSION['org_id']]);
$stats['staff'] = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE organization_id = ? AND role = 'manager'");
$stmt->execute([$_SESSION['org_id']]);
$stats['manager'] = $stmt->fetch()['count'];

// Nouvelle requête modifiée
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
    OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY
");
$stmt->execute([$_SESSION['org_id']]);
$recentSubmissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('admin_dashboard'); ?> - <?php echo __('aviation_portal'); ?></title>
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
            font-size: 1.3em;
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
        .sidebar a:hover, .sidebar a.active {
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-number {
            font-size: 2em;
            font-weight: 700;
            color: #1a4d6b;
        }
        .stat-label {
            color: #5a7a9a;
            margin-top: 8px;
            font-size: 12px;
        }
        .section-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e8f0;
        }
        .btn {
            display: inline-block;
            padding: 8px 20px;
            background: #1a4d6b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
        }
        .btn:hover { background: #2c6e9e; }
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
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php echo language_switcher(); ?>
    
    <div class="sidebar">
        <h2>ANAC Aviation</h2>
        <a href="dashboard.php" class="active"><?php echo __('dashboard'); ?></a>
        <a href="forms.php"><?php echo __('manage_forms'); ?></a>
        <a href="users.php"><?php echo __('manage_users'); ?></a>
        <a href="submissions.php"><?php echo __('view_submissions'); ?></a>
        <a href="../logout.php"><?php echo __('logout'); ?></a>
    </div>

    <div class="content">
        <div class="header">
            <h1><?php echo __('admin_dashboard'); ?></h1>
            <span><?php echo __('welcome'); ?>, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['forms']; ?></div>
                <div class="stat-label"><?php echo __('stat_forms'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['submissions']; ?></div>
                <div class="stat-label"><?php echo __('stat_submissions'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['staff']; ?></div>
                <div class="stat-label">Staff</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['manager']; ?></div>
                <div class="stat-label">Manager</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">1</div>
                <div class="stat-label"><?php echo __('stat_organizations'); ?></div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3><?php echo __('quick_actions'); ?></h3>
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="../create_form.php" class="btn"><?php echo __('create_form'); ?></a>
                <a href="users.php" class="btn"><?php echo __('manage_users'); ?></a>
                <a href="submissions.php" class="btn"><?php echo __('view_submissions'); ?></a>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3><?php echo __('recent_submissions'); ?></h3>
                <a href="submissions.php" class="btn"><?php echo __('view_all'); ?></a>
            </div>
            <?php if (count($recentSubmissions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('id'); ?></th>
                        <th><?php echo __('form_title'); ?></th>
                        <th>Soumis par</th>
                        <th><?php echo __('date'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSubmissions as $sub): ?>
                    <tr>
                        <td><?php echo $sub['id']; ?></td>
                        <td><?php echo htmlspecialchars($sub['form_title']); ?></td>
                        <td><?php echo htmlspecialchars($sub['submitted_by'] ?? 'Anonyme'); ?></td>
                        <td><?php echo $sub['submitted_at']; ?></td>
                        <td><a href="submission_view.php?id=<?php echo $sub['id']; ?>" class="btn"><?php echo __('view'); ?></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color: #5a7a9a; text-align: center;"><?php echo __('no_data'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>