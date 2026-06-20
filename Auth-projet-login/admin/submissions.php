<?php
session_start();
require_once '../config.php';
require_once '../lang.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$current_lang = $_SESSION['lang'] ?? 'fr';

// Récupérer toutes les soumissions avec le nom du répondant
$stmt = $pdo->prepare("
    SELECT s.id, s.response_data, s.submitted_at, s.score, s.max_score, s.compliance_pct, 
           f.title as form_title,
           CASE 
               WHEN s.respondent_type = 'staff' THEN COALESCE(u.username, 'Staff inconnu')
               ELSE 'Public (OTP)'
           END as submitted_by
    FROM submissions s
    JOIN forms f ON s.form_id = f.id
    LEFT JOIN users u ON s.submitted_by_user_id = u.id
    WHERE f.organization_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$_SESSION['org_id']]);
$submissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('submissions'); ?> - <?php echo __('aviation_portal'); ?></title>
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
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .btn-small {
            padding: 5px 12px;
            font-size: 12px;
            background: #1a4d6b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
        }
        .btn-small:hover { background: #2c6e9e; }
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
        .score-badge {
            background: #27ae60;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .score-low {
            background: #c0392b;
        }
        .score-medium {
            background: #e67e22;
        }
    </style>
</head>
<body>
    <?php echo language_switcher(); ?>
    
    <div class="sidebar">
        <h2>ANAC Aviation</h2>
        <a href="dashboard.php"><?php echo __('dashboard'); ?></a>
        <a href="forms.php"><?php echo __('manage_forms'); ?></a>
        <a href="users.php"><?php echo __('manage_users'); ?></a>
        <a href="submissions.php" class="active"><?php echo __('view_submissions'); ?></a>
        <a href="../logout.php"><?php echo __('logout'); ?></a>
    </div>

    <div class="content">
        <div class="header">
            <h1><?php echo __('submissions'); ?></h1>
            <span><?php echo __('welcome'); ?>, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>

        <div class="card">
            <?php if (count($submissions) > 0): ?>
            </table>
                <thead>
                    <tr>
                        <th><?php echo __('id'); ?></th>
                        <th><?php echo __('form_title'); ?></th>
                        <th>Soumis par</th>
                        <th><?php echo __('date'); ?></th>
                        <th><?php echo __('score'); ?></th>
                        <th><?php echo __('compliance'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td><?php echo $sub['id']; ?></td>
                        <td><?php echo htmlspecialchars($sub['form_title']); ?></td>
                        <td><?php echo htmlspecialchars($sub['submitted_by']); ?></td>
                        <td><?php echo $sub['submitted_at']; ?></td>
                        <td>
                            <?php if ($sub['score'] !== null): ?>
                                <?php 
                                $scoreClass = 'score-badge';
                                if ($sub['compliance_pct'] < 50) $scoreClass .= ' score-low';
                                elseif ($sub['compliance_pct'] < 75) $scoreClass .= ' score-medium';
                                ?>
                                <span class="<?php echo $scoreClass; ?>">
                                    <?php echo $sub['score']; ?>/<?php echo $sub['max_score']; ?>
                                </span>
                            <?php else: ?>
                                <span class="score-badge">Non noté</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($sub['compliance_pct'] !== null): ?>
                                <?php echo round($sub['compliance_pct'], 1); ?>%
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="submission_view.php?id=<?php echo $sub['id']; ?>" class="btn-small"><?php echo __('view'); ?></a>
                        </td>
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