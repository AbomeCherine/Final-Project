<?php
session_start();
require_once 'config.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr');
$_SESSION['lang'] = $lang;

$translations = [
    'fr' => [
        'title' => 'Aviation Portal',
        'subtitle' => 'Plateforme de gestion des formulaires et inspections',
        'dashboard' => 'Dashboard',
        'logout' => 'Déconnexion',
        'login' => 'Se connecter',
        'register' => 'S\'inscrire'
    ],
    'en' => [
        'title' => 'Aviation Portal',
        'subtitle' => 'Form and inspection management platform',
        'dashboard' => 'Dashboard',
        'logout' => 'Logout',
        'login' => 'Login',
        'register' => 'Register'
    ],
    'rw' => [
        'title' => 'Icyerekezo cy\'Ubugenzi bwo mu Kirere',
        'subtitle' => 'Urubuga rwo gucungira impapuro n\'igenzura',
        'dashboard' => 'Akadomo',
        'logout' => 'Sohora',
        'login' => 'Injira',
        'register' => 'Iyandikishe'
    ]
];

$t = $translations[$lang] ?? $translations['fr'];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $t['title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #1a4d6b;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        .container {
            background: white;
            padding: 50px;
            border-radius: 16px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 { color: #1a4d6b; margin-bottom: 15px; font-size: 2.2em; }
        p { color: #5a7a9a; margin-bottom: 30px; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary { background: #1a4d6b; color: white; }
        .btn-primary:hover { background: #2c6e9e; }
        .btn-secondary { background: #e8eef3; color: #1a4d6b; }
        .btn-secondary:hover { background: #d0dce8; }

        .lang-selector {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 8px 15px;
            border-radius: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .lang-selector select {
            padding: 8px 15px;
            border-radius: 30px;
            border: 1px solid #c0d4e8;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        .lang-selector select:hover {
            border-color: #1a4d6b;
        }
    </style>
</head>
<body>
    <div class="lang-selector">
        <select onchange="window.location.href='?lang='+this.value">
            <option value="fr" <?php echo $lang === 'fr' ? 'selected' : ''; ?>>🇫🇷 Français</option>
            <option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>🇬🇧 English</option>
            <option value="rw" <?php echo $lang === 'rw' ? 'selected' : ''; ?>>🇷🇼 Kinyarwanda</option>
        </select>
    </div>

    <div class="container">
        <h1><?php echo $t['title']; ?></h1>
        <p><?php echo $t['subtitle']; ?></p>
        
        <?php if (isset($_SESSION['user_id'])): 
            
            $dashboard_link = match($_SESSION['role'] ?? '') {
                'admin' => 'admin/dashboard.php',
                'manager' => 'manager/dashboard.php',
                'staff' => 'staff/my-forms.php',
                default => 'login.php'
            };
        ?>
            <a href="<?php echo $dashboard_link; ?>" class="btn btn-primary"><?php echo $t['dashboard']; ?></a>
            <a href="logout.php" class="btn btn-secondary"><?php echo $t['logout']; ?></a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary"><?php echo $t['login']; ?></a>
            <a href="register.php" class="btn btn-secondary"><?php echo $t['register']; ?></a>
        <?php endif; ?>
    </div>
</body>
</html>