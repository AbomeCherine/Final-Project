<?php
session_start();
require_once '../config.php';
require_once '../lang.php';


$current_lang = $_SESSION['lang'] ?? 'fr';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM forms WHERE organization_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['org_id']]);
$forms = $stmt->fetchAll();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ? AND organization_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['org_id']]);
    header('Location: forms.php');
    exit();
}

if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE forms SET is_published = ~is_published WHERE id = ? AND organization_id = ?");
    $stmt->execute([$_GET['toggle'], $_SESSION['org_id']]);
    header('Location: forms.php');
    exit();
}

$question_counts = [];
$stmt = $pdo->prepare("SELECT form_id, COUNT(*) as count FROM form_fields GROUP BY form_id");
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    $question_counts[$row['form_id']] = $row['count'];
}

$form_type_names = [
    'survey' => 'Questionnaire',
    'checklist' => 'Checklist',
    'incident' => 'Incident',
    'registration' => 'Inscription',
    'kpi' => 'KPI',
    'poll' => 'Sondage'
];
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('manage_forms'); ?> - <?php echo __('aviation_portal'); ?></title>
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1a4d6b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .btn:hover { background: #2c6e9e; }
        .btn-small {
            padding: 5px 12px;
            font-size: 12px;
            margin: 2px;
            display: inline-block;
            border-radius: 6px;
            text-decoration: none;
        }
        .btn-danger { background: #c0392b; color: white; }
        .btn-warning { background: #e67e22; color: white; }
        .btn-info { background: #2980b9; color: white; }
        .btn-email { background: #27ae60; color: white; }
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
        .badge-published {
            background: #27ae60;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .badge-draft {
            background: #e67e22;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .badge-type {
            background: #8e44ad;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }
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
            width: 400px;
            max-width: 90%;
        }
        .modal-content input {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #c0d4e8;
            border-radius: 8px;
        }
        .modal-content button {
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-send {
            background: #27ae60;
            color: white;
        }
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <?php echo language_switcher(); ?>
    
    <div class="sidebar">
        <h2>ANAC Aviation</h2>
        <a href="dashboard.php"><?php echo __('dashboard'); ?></a>
        <a href="forms.php" class="active"><?php echo __('manage_forms'); ?></a>
        <a href="users.php"><?php echo __('manage_users'); ?></a>
        <a href="submissions.php"><?php echo __('view_submissions'); ?></a>
        <a href="../logout.php"><?php echo __('logout'); ?></a>
    </div>

    <div class="content">
        <div class="header">
            <h1><?php echo __('manage_forms'); ?></h1>
            <span><?php echo __('welcome'); ?>, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>

        <div class="card">
            <a href="../create_form.php" class="btn">+ <?php echo __('create_form'); ?></a>
            
            <?php if (count($forms) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo __('type'); ?></th>
                        <th><?php echo __('title'); ?></th>
                        <th><?php echo __('questions'); ?></th>
                        <th><?php echo __('date'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                    <tr>
                        <td><?php echo $form['id']; ?></td>
                        <td>
                            <span class="badge-type">
                                <?php echo $form_type_names[$form['form_type']] ?? $form['form_type']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($form['title']); ?></td>
                        <td><?php echo $question_counts[$form['id']] ?? 0; ?></td>
                        <td><?php echo $form['created_at']; ?></td>
                        <td>
                            <?php if ($form['is_published']): ?>
                                <span class="badge-published"><?php echo __('published'); ?></span>
                            <?php else: ?>
                                <span class="badge-draft"><?php echo __('draft'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-small btn-email" onclick="openEmailModal(<?php echo $form['id']; ?>, '<?php echo addslashes($form['title']); ?>')">📧 <?php echo __('send'); ?></button>
                            <a href="../submit_form.php?id=<?php echo $form['id']; ?>" class="btn-small btn-info"><?php echo __('fill_form'); ?></a>
                            <a href="edit_form.php?id=<?php echo $form['id']; ?>" class="btn-small"><?php echo __('edit_form'); ?></a>
                            <a href="?toggle=<?php echo $form['id']; ?>" class="btn-small btn-warning">
                                <?php echo $form['is_published'] ? __('unpublish') : __('publish'); ?>
                            </a>
                            <a href="?delete=<?php echo $form['id']; ?>" class="btn-small btn-danger" onclick="return confirm('<?php echo __('confirm_delete'); ?>')"><?php echo __('delete'); ?></a>
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

    <!-- Modal pour envoyer un email -->
    <div id="emailModal" class="modal">
        <div class="modal-content">
            <h3>Envoyer le formulaire par email</h3>
            <p id="modalFormTitle"></p>
            <input type="email" id="recipientEmail" placeholder="Email du destinataire" required>
            <input type="hidden" id="modalFormId">
            <div>
                <button class="btn-send" onclick="sendEmail()">Envoyer</button>
                <button class="btn-cancel" onclick="closeModal()">Annuler</button>
            </div>
        </div>
    </div>

    <script>
        let currentFormId = null;
        let currentFormTitle = null;

        function openEmailModal(formId, formTitle) {
            currentFormId = formId;
            currentFormTitle = formTitle;
            document.getElementById('modalFormTitle').innerHTML = '<strong>' + formTitle + '</strong>';
            document.getElementById('modalFormId').value = formId;
            document.getElementById('recipientEmail').value = '';
            document.getElementById('emailModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('emailModal').style.display = 'none';
        }

        function sendEmail() {
            const email = document.getElementById('recipientEmail').value;
            const formId = currentFormId;
            const formTitle = currentFormTitle;

            if (!email || !email.includes('@')) {
                alert('Veuillez entrer une adresse email valide.');
                return;
            }

            fetch('../send_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(email) + '&form_id=' + formId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    closeModal();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Erreur lors de l\'envoi: ' + error);
            });
        }

        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('emailModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>