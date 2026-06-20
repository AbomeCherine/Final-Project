<?php
session_start();
require_once '../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../login.php');
    exit();
}


if (isset($_GET['toggle'])) {
    $orgId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("
        UPDATE organizations 
        SET can_create_forms = ~can_create_forms 
        WHERE id = ?
    ");
    $stmt->execute([$orgId]);
    header('Location: organizations.php');
    exit();
}

// Récupérer toutes les organisations
$stmt = $pdo->prepare("SELECT id, name, slug, can_create_forms, created_at FROM organizations ORDER BY id");
$stmt->execute();
$organizations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des organisations - Super Admin</title>
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
        .sidebar h2 { color: white; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #1d4a6e; }
        .sidebar a {
            display: block;
            color: #a0c0d8;
            padding: 12px 15px;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 10px;
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
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { color: #1a4d6b; font-size: 1.5em; }
        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
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
        .badge-allowed {
            background: #27ae60;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .badge-denied {
            background: #c0392b;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .btn-toggle {
            background: #1a4d6b;
            color: white;
            padding: 6px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
        }
        .btn-toggle:hover {
            background: #2c6e9e;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Super Admin</h2>
        <a href="dashboard.php">Tableau de bord</a>
        <a href="organizations.php" class="active">Organisations</a>
        <a href="users.php">Utilisateurs</a>
        <a href="../logout.php">Déconnexion</a>
    </div>

    <div class="content">
        <div class="header">
            <h1>Gestion des organisations</h1>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?> (Super Admin)</span>
        </div>

        <div class="card">
            <h3>Liste des organisations</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Slug</th>
                        <th>Peut créer des formulaires</th>
                        <th>Date création</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($organizations as $org): ?>
                    <tr>
                        <td><?php echo $org['id']; ?></td>
                        <td><?php echo htmlspecialchars($org['name']); ?></td>
                        <td><?php echo htmlspecialchars($org['slug']); ?></td>
                        <td>
                            <?php if ($org['can_create_forms'] == 1): ?>
                                <span class="badge-allowed">Autorisé</span>
                            <?php else: ?>
                                <span class="badge-denied">Non autorisé</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $org['created_at']; ?></td>
                        <td>
                            <a href="?toggle=<?php echo $org['id']; ?>" class="btn-toggle">
                                <?php echo $org['can_create_forms'] == 1 ? 'Désactiver' : 'Activer'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>