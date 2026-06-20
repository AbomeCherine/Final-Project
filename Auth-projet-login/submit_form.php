<?php
session_start();
require_once 'config.php';
require_once 'lang.php';

$form_id = $_GET['id'] ?? 0;

// Récupérer le formulaire
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ? AND is_published = 1");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

if (!$form) {
    die("Formulaire non trouvé ou non publié.");
}

// Récupérer les champs du formulaire avec fillable_by
$stmt = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order");
$stmt->execute([$form_id]);
$fields = $stmt->fetchAll();

$success = '';
$error = '';
$isStaff = (isset($_SESSION['role']) && $_SESSION['role'] === 'staff');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responses = [];
    foreach ($fields as $field) {
        $value = $_POST['field_' . $field['id']] ?? '';
        if ($value === 'Autre' && isset($_POST['field_' . $field['id'] . '_preciser'])) {
            $value = $_POST['field_' . $field['id'] . '_preciser'];
        }
        $responses[$field['label']] = $value;
    }
    
    $response_data = json_encode($responses);
    $submitted_by = $_SESSION['user_id'] ?? null;
    $respondent_type = $isStaff ? 'staff' : 'public';
    
    $sql = "INSERT INTO submissions (form_id, response_data, submitted_by_user_id, respondent_type) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$form_id, $response_data, $submitted_by, $respondent_type])) {
        $submission_id = $pdo->lastInsertId();
        
        // === NOTIFICATIONS ===
        $org_id = $_SESSION['org_id'] ?? $form['organization_id'];
        $respondent_name = $_SESSION['username'] ?? ($_POST['nom'] ?? 'Un répondant');
        $message = "Nouvelle soumission reçue pour le formulaire : " . $form['title'] . " de la part de " . $respondent_name;
        
        // Notifier les managers
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'manager' AND organization_id = ?");
        $stmt->execute([$org_id]);
        $managers = $stmt->fetchAll();
        
        foreach ($managers as $manager) {
            $stmt2 = $pdo->prepare("INSERT INTO notifications (user_id, type, entity_id, message) VALUES (?, 'new_submission', ?, ?)");
            $stmt2->execute([$manager['id'], $submission_id, $message]);
        }
        
        // Notifier les admins
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' AND organization_id = ?");
        $stmt->execute([$org_id]);
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            $stmt2 = $pdo->prepare("INSERT INTO notifications (user_id, type, entity_id, message) VALUES (?, 'new_submission', ?, ?)");
            $stmt2->execute([$admin['id'], $submission_id, $message]);
        }
        
        auditLog($pdo, 'submit_form', 'submission', $submission_id, null, ['form_id' => $form_id]);
        
        $success = "Formulaire envoyé avec succès !";
    } else {
        $error = "Erreur lors de l'envoi.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($form['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            padding: 40px;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #1a4d6b;
            color: white;
            padding: 24px 32px;
        }
        .header h1 { font-size: 24px; margin-bottom: 8px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .question {
            padding: 24px 32px;
            border-bottom: 1px solid #e0e0e0;
        }
        .question label {
            display: block;
            font-weight: 500;
            color: #1a4d6b;
            margin-bottom: 12px;
        }
        .required { color: #d32f2f; font-size: 12px; }
        input[type="text"], input[type="email"], input[type="number"], textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
        }
        .radio-group, .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 8px;
        }
        .radio-group label, .checkbox-group label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            cursor: pointer;
        }
        .radio-group input, .checkbox-group input { width: auto; }
        .preciser-field { margin-top: 15px; }
        .preciser-field input { width: 100%; padding: 10px; border: 1px solid #dadce0; border-radius: 4px; }
        button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin: 20px 32px 32px auto;
            display: block;
        }
        button:hover { background: #1557b0; }
        .success { background: #d4edda; color: #155724; padding: 12px; margin: 20px 32px; border-radius: 4px; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; margin: 20px 32px; border-radius: 4px; text-align: center; }
        
        .question.locked { background: #fafafa; }
        .staff-only-badge {
            background: #e8f0fe;
            color: #1a73e8;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }
        .locked-field {
            background: #f5f5f5;
            padding: 12px;
            border: 1px dashed #dadce0;
            border-radius: 4px;
            color: #5f6368;
            font-style: italic;
            text-align: center;
        }
    </style>
    <script>
        function showPreciser(radio, fieldId) {
            const preciserDiv = document.getElementById('preciser_' + fieldId);
            if (preciserDiv) {
                if (radio.value === 'Autre' && radio.checked) {
                    preciserDiv.style.display = 'block';
                } else {
                    preciserDiv.style.display = 'none';
                }
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <div class="header">
            <h1><?php echo htmlspecialchars($form['title']); ?></h1>
            <p><?php echo htmlspecialchars($form['description']); ?></p>
        </div>

        <?php if ($success): ?>
            <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <?php foreach ($fields as $index => $field): 
                $isHidden = ($field['fillable_by'] === 'hidden');
                $isStaffOnly = ($field['fillable_by'] === 'staff_only');
                $isStaff = (isset($_SESSION['role']) && $_SESSION['role'] === 'staff');
                
                // Si la question est cachée ET l'utilisateur n'est pas staff, on ne l'affiche pas
                if ($isHidden && !$isStaff) {
                    continue; // Passer à la question suivante sans l'afficher
                }
                
                $isLocked = ($isStaffOnly && !$isStaff);
            ?>
                <div class="question <?php echo $isLocked ? 'locked' : ''; ?>">
                    <label>
                        <?php echo htmlspecialchars($field['label']); ?>
                        <?php if ($field['required']): ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                        <?php if ($isStaffOnly): ?>
                            <span class="staff-only-badge">🔒 Staff uniquement</span>
                        <?php endif; ?>
                    </label>
                    
                    <?php if ($isLocked): ?>
                        <div class="locked-field">
                            🔒 Cette question est réservée au personnel. Connectez-vous avec un compte staff pour y répondre.
                        </div>
                    <?php elseif ($field['question_type'] === 'textarea'): ?>
                        <textarea name="field_<?php echo $field['id']; ?>" rows="4" <?php echo $field['required'] ? 'required' : ''; ?> placeholder="Vos observations..."></textarea>
                    
                    <?php elseif ($field['question_type'] === 'select'): ?>
                        <select name="field_<?php echo $field['id']; ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                            <option value="">-- Choisir --</option>
                            <?php 
                            if ($field['options']) {
                                $options = explode('|', $field['options']);
                                foreach ($options as $opt):
                            ?>
                                <option value="<?php echo trim($opt); ?>"><?php echo trim($opt); ?></option>
                            <?php 
                                endforeach;
                            }
                            ?>
                        </select>
                    
                    <?php elseif ($field['question_type'] === 'radio'): ?>
                        <div class="radio-group">
                            <?php 
                            if ($field['options']) {
                                $options = explode('|', $field['options']);
                                foreach ($options as $opt):
                            ?>
                                <label>
                                    <input type="radio" name="field_<?php echo $field['id']; ?>" 
                                           value="<?php echo trim($opt); ?>" 
                                           <?php echo $field['required'] ? 'required' : ''; ?>
                                           onclick="showPreciser(this, <?php echo $field['id']; ?>)">
                                    <?php echo trim($opt); ?>
                                </label>
                            <?php 
                                endforeach;
                            }
                            ?>
                        </div>
                        <?php if (strpos($field['options'] ?? '', 'Autre') !== false): ?>
                            <div class="preciser-field" id="preciser_<?php echo $field['id']; ?>" style="display: none;">
                                <input type="text" name="field_<?php echo $field['id']; ?>_preciser" placeholder="Si autre, bien vouloir préciser">
                            </div>
                        <?php endif; ?>
                    
                    <?php elseif ($field['question_type'] === 'checkbox'): ?>
                        <div class="checkbox-group">
                            <?php 
                            if ($field['options']) {
                                $options = explode('|', $field['options']);
                                foreach ($options as $opt):
                            ?>
                                <label>
                                    <input type="checkbox" name="field_<?php echo $field['id']; ?>[]" value="<?php echo trim($opt); ?>">
                                    <?php echo trim($opt); ?>
                                </label>
                            <?php 
                                endforeach;
                            }
                            ?>
                        </div>
                    
                    <?php elseif ($field['question_type'] === 'email'): ?>
                        <input type="email" name="field_<?php echo $field['id']; ?>" <?php echo $field['required'] ? 'required' : ''; ?> placeholder="exemple@domaine.com">
                    
                    <?php elseif ($field['question_type'] === 'number'): ?>
                        <input type="number" name="field_<?php echo $field['id']; ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                    
                    <?php elseif ($field['question_type'] === 'date'): ?>
                        <input type="date" name="field_<?php echo $field['id']; ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                    
                    <?php else: ?>
                        <input type="text" name="field_<?php echo $field['id']; ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <button type="submit">Envoyer le formulaire</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>