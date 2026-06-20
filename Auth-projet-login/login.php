<?php
session_start();
require_once 'config.php';
require_once 'lang.php';

$current_lang = $_SESSION['lang'] ?? 'en';

$host = "localhost\\SQLEXPRESS";
$db = "projet_login";
$user = "";
$pass = "";
$dsn = "sqlsrv:Server=$host;Database=$db";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } elseif ($_SESSION['role'] === 'manager') {
            header('Location: manager/dashboard.php');
        } elseif ($_SESSION['role'] === 'staff') {
            header('Location: staff/my-forms.php');
        } else {
            header('Location: dashboard.php');
        }
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = __('fill_fields');
    } else {
        $sql = "SELECT id, username, email, password, role, organization_id FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['org_id'] = $user['organization_id'] ?? 1;
            
            
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } elseif ($user['role'] === 'manager') {
                header('Location: manager/dashboard.php');
            } elseif ($user['role'] === 'staff') {
                header('Location: staff/my-forms.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $error = __('invalid_credentials');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('login'); ?> - <?php echo __('aviation_portal'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #1a4d6b;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 30px; color: #1a4d6b; font-size: 1.8em; }
        input {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #c0d4e8;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #1a4d6b;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 16px;
        }
        button:hover { background: #2c6e9e; }
        .error { color: #c0392b; margin: 10px 0; text-align: center; }
        .link { text-align: center; margin-top: 25px; }
        a { color: #1a4d6b; text-decoration: none; }
        .roles-info {
            background: #f0f5f8;
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
            color: #5a7a9a;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php echo language_switcher(); ?>
    
    <div class="container">
        <h2><?php echo __('login'); ?></h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="login" placeholder="<?php echo __('username'); ?>" required>
            <input type="password" name="password" placeholder="<?php echo __('password'); ?>" required>
            <button type="submit"><?php echo __('login'); ?></button>
        </form>
        <div class="link">
            <?php echo __('no_account'); ?> <a href="register.php"><?php echo __('register'); ?></a>
        </div>
        <div class="roles-info">
            🔐 Comptes de test:<br>
            Admin: Manon / password123<br>
            Manager: MarieManager / password<br>
            Staff: JeanStaff / password
        </div>
    </div>
</body>
</html>