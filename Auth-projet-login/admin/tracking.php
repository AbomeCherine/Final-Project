<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$form_id = $_GET['id'] ?? 0;

// Récupérer le formulaire
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ? AND organization_id = ?");
$stmt->execute([$form_id, $_SESSION['org_id']]);
$form = $stmt->fetch();

if (!$form) {
    die("Formulaire non trouvé.");
}

// Récupérer les invitations avec leurs statistiques
$stmt = $pdo->prepare("
    SELECT 
        i.*,
        CASE WHEN s.id IS NOT NULL THEN '✅ Soumis' ELSE '⏳ En attente' END as submission_status
    FROM form_invitations i
    LEFT JOIN submissions s ON s.form_id = i.form_id AND s.submitted_by_email = i.contact
    WHERE i.form_id = ?
    ORDER BY i.sent_at DESC
");
$stmt->execute([$form_id]);
$invitations = $stmt->fetchAll();

// Statistiques
$total = count($invitations);
$opened = 0;
$submitted = 0;

foreach ($invitations as $inv) {
    if ($inv['opened_at']) $opened++;
    if ($inv['submitted_at']) $submitted++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi des réponses - <?php echo htmlspecialchars($form['title']); ?></title>
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
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #1a4d6b;
        }
        .stat-label {
            color: #5a7a9a;
            margin-top: 5px;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
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
        .status-sent { color: #e67e22; }
        .status-opened { color: #2980b9; }
        .status-submitted { color: #27ae60; }
        .btn-back {
            background: #5a7a9a;
            color: white;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-resend {
            background: #e67e22;
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
        }
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
            <h1>📊 Suivi : <?php echo htmlspecialchars($form['title']); ?></h1>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>

        <a href="forms.php" class="btn-back">← Retour aux formulaires</a>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Invitations envoyées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $opened; ?></div>
                <div class="stat-label">Ont ouvert</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $submitted; ?></div>
                <div class="stat-label">Ont soumis</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total - $submitted; ?></div>
                <div class="stat-label">En attente</div>
            </div>
        </div>

        <div class="card">
            <h3>Détail des invitations</h3>
            <?php if (count($invitations) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Type</th>
                        <th>Envoyé le</th>
                        <th>Ouvert le</th>
                        <th>Soumis le</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invitations as $inv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inv['contact']); ?></td>
                        <td><?php echo $inv['contact_type']; ?></td>
                        <td><?php echo $inv['sent_at']; ?></td>
                        <td><?php echo $inv['opened_at'] ?? '-'; ?></td>
                        <td><?php echo $inv['submitted_at'] ?? '-'; ?></td>
                        <td>
                            <?php if ($inv['submitted_at']): ?>
                                <span class="status-submitted">✅ Soumis</span>
                            <?php elseif ($inv['opened_at']): ?>
                                <span class="status-opened">👁️ Ouvert</span>
                            <?php else: ?>
                                <span class="status-sent">📧 Envoyé</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$inv['submitted_at']): ?>
                                <a href="resend_invitation.php?id=<?php echo $inv['id']; ?>" class="btn-resend">Renvoyer</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>Aucune invitation envoyée pour ce formulaire.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>