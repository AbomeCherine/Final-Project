<?php
session_start();

error_log("=== users.php ===");
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'vide'));
error_log("Session username: " . ($_SESSION['username'] ?? 'vide'));
require_once '../config.php';
require_once '../api/send_access_code.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';


function generateAccessCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $company_name = trim($_POST['company_name'] ?? '');
    $password = $_POST['password'] ?? '';
    
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = "Le prénom, le nom et l'email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif ($role === 'operator' && empty($company_name)) {
        $error = "La compagnie est obligatoire pour le rôle Opérateur.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            
            $access_code = null;
            $hashed_password = null;
            $hashed_code = null;
            
            if ($role === 'operator' || $role === 'manager' || $role === 'staff') {
                $access_code = generateAccessCode(8);
                $hashed_code = password_hash($access_code, PASSWORD_DEFAULT);
            } elseif ($role === 'admin') {
                if (empty($password) || strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                }
            }
            
            if (empty($error)) {
                $sql = "INSERT INTO users (username, email, password, access_code, role, organization_id, first_name, last_name, company_name) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                $username = strtolower($first_name . '.' . $last_name);
                $org_id = $_SESSION['org_id'] ?? 1;
                
                if ($stmt->execute([$username, $email, $hashed_password, $hashed_code, $role, $org_id, $first_name, $last_name, $company_name])) {
                    
                    if ($role !== 'admin' && $access_code) {
                        sendAccessCode($email, $first_name, $access_code);
                    }
                    $success = "Utilisateur ajouté avec succès !";
                } else {
                    $error = "Erreur lors de l'ajout.";
                }
            }
        }
    }
}


if (isset($_GET['change_role'])) {
    $userId = (int)$_GET['change_role'];
    $newRole = $_GET['role'];
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ? AND organization_id = ?");
    $stmt->execute([$newRole, $userId, $_SESSION['org_id']]);
    header('Location: users.php');
    exit();
}


if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    if ($userId !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND organization_id = ?");
        $stmt->execute([$userId, $_SESSION['org_id']]);
    }
    header('Location: users.php');
    exit();
}

$stmt = $pdo->prepare("SELECT id, username, email, role, first_name, last_name, company_name, created_at FROM users WHERE organization_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['org_id']]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
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
        }
        .header h1 { color: #1a4d6b; font-size: 1.5em; }
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #1a4d6b; font-weight: 500; }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #c0d4e8;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        .btn {
            padding: 10px 20px;
            background: #1a4d6b;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn:hover { background: #2c6e9e; }
        .btn-small {
            padding: 5px 12px;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            border-radius: 6px;
        }
        .btn-danger { background: #c0392b; color: white; }
        .btn-warning { background: #e67e22; color: white; }
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
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .badge-admin { background: #c0392b; color: white; }
        .badge-staff { background: #27ae60; color: white; }
        .badge-manager { background: #e67e22; color: white; }
        .badge-operator { background: #2980b9; color: white; }
        .error { color: #c0392b; margin-bottom: 15px; }
        .success { color: #27ae60; margin-bottom: 15px; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-content h3 { margin-bottom: 20px; color: #1a4d6b; }
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-cancel { background: #95a5a6; }
        .btn-cancel:hover { background: #7f8c8d; }
        .company-field { display: none; }
        .company-field.visible { display: block; }
    </style>
    <script>
        function toggleCompanyField() {
            const role = document.getElementById('roleSelect').value;
            const companyField = document.getElementById('companyField');
            const passwordField = document.getElementById('passwordField');
            if (role === 'operator') {
                companyField.classList.add('visible');
            } else {
                companyField.classList.remove('visible');
            }
            
            
            if (role === 'admin') {
                passwordField.style.display = 'block';
            } else {
                passwordField.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>ANAC Aviation</h2>
        <a href="dashboard.php">Tableau de bord</a>
        <a href="forms.php">Formulaires</a>
        <a href="users.php" class="active">Utilisateurs</a>
        <a href="submissions.php">Soumissions</a>
        <a href="../logout.php">Déconnexion</a>
    </div>

    <div class="content">
        <div class="header">
            <h1>Gestion des utilisateurs</h1>
            <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Liste des utilisateurs</h3>
                <button class="btn" onclick="document.getElementById('addUserModal').style.display='flex'">+ Ajouter un utilisateur</button>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Compagnie</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php
                            $badgeClass = 'badge-staff';
                            if ($user['role'] === 'admin') $badgeClass = 'badge-admin';
                            if ($user['role'] === 'manager') $badgeClass = 'badge-manager';
                            if ($user['role'] === 'operator') $badgeClass = 'badge-operator';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $user['role']; ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($user['company_name'] ?? '-'); ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <a href="?change_role=<?php echo $user['id']; ?>&role=staff" class="btn-small btn-warning">Staff</a>
                                <a href="?change_role=<?php echo $user['id']; ?>&role=manager" class="btn-small btn-warning">Manager</a>
                                <a href="?change_role=<?php echo $user['id']; ?>&role=operator" class="btn-small btn-warning">Opérateur</a>
                                <a href="?change_role=<?php echo $user['id']; ?>&role=admin" class="btn-small btn-warning">Admin</a>
                                <a href="?delete=<?php echo $user['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                            <?php else: ?>
                                <span class="badge">Votre compte</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h3>Ajouter un utilisateur</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="last_name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Rôle</label>
                    <select name="role" id="roleSelect" onchange="toggleCompanyField()">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="operator">Opérateur</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group company-field" id="companyField">
                    <label>Compagnie</label>
                    <input type="text" name="company_name" placeholder="Ex: Air Gabon">
                </div>
                <div class="form-group" id="passwordField" style="display: none;">
                    <label>Mot de passe (pour admin uniquement)</label>
                    <input type="password" name="password" placeholder="Minimum 6 caractères">
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn">Ajouter</button>
                    <button type="button" class="btn btn-cancel" onclick="document.getElementById('addUserModal').style.display='none'">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCompanyField() {
            const roleSelect = document.getElementById('roleSelect');
            const companyField = document.getElementById('companyField');
            const passwordField = document.getElementById('passwordField');

            if (roleSelect.value === 'operator' || roleSelect.value === 'manager') {
                companyField.style.display = 'block';
            } else {
                companyField.style.display = 'none';
            }

            if (roleSelect.value === 'admin') {
                passwordField.style.display = 'block';
            } else {
                passwordField.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('addUserModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleCompanyField();
        });
    </script>
</body>
</html>