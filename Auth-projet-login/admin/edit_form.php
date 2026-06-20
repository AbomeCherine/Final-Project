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

// Récupérer les champs existants
$stmt = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order");
$stmt->execute([$form_id]);
$fields = $stmt->fetchAll();

// Traitement du formulaire
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_form') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $form_type = $_POST['form_type'];
        
        $stmt = $pdo->prepare("UPDATE forms SET title = ?, description = ?, form_type = ? WHERE id = ?");
        $stmt->execute([$title, $description, $form_type, $form_id]);
        $success = "Formulaire mis à jour.";
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'add_field') {
        $label = trim($_POST['label']);
        $question_type = $_POST['question_type'];
        $options = $_POST['options'] ?? '';
        $required = isset($_POST['required']) ? 1 : 0;
        $field_order = count($fields);
        
        $stmt = $pdo->prepare("INSERT INTO form_fields (form_id, field_order, question_type, label, options, required) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$form_id, $field_order, $question_type, $label, $options, $required]);
        header("Location: edit_form.php?id=$form_id");
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_fields') {
        foreach ($_POST['field_order'] as $field_id => $order) {
            $stmt = $pdo->prepare("UPDATE form_fields SET field_order = ? WHERE id = ? AND form_id = ?");
            $stmt->execute([$order, $field_id, $form_id]);
        }
        
        if (isset($_POST['delete_fields'])) {
            foreach ($_POST['delete_fields'] as $field_id) {
                $stmt = $pdo->prepare("DELETE FROM form_fields WHERE id = ? AND form_id = ?");
                $stmt->execute([$field_id, $form_id]);
            }
        }
        
        header("Location: edit_form.php?id=$form_id");
        exit();
    }
}

// Recharger les champs
$stmt = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order");
$stmt->execute([$form_id]);
$fields = $stmt->fetchAll();

$form_types = [
    'survey' => '📋 Questionnaire',
    'checklist' => '✅ Checklist',
    'incident' => '⚠️ Rapport d\'incident',
    'registration' => '📝 Inscription',
    'kpi' => '📊 Évaluation KPI',
    'poll' => '🗳️ Sondage'
];

$question_types = [
    'text' => 'Texte court',
    'textarea' => 'Texte long',
    'email' => 'Email',
    'number' => 'Nombre',
    'date' => 'Date',
    'select' => 'Liste déroulante',
    'radio' => 'Choix unique',
    'checkbox' => 'Choix multiples',
    'scale' => 'Échelle 1-5',
    'rating' => 'Note sur 5',
    'file' => 'Fichier'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le formulaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            padding: 30px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
        }
        h1 { color: #1a4d6b; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 500; margin-bottom: 8px; color: #1a4d6b; }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #c0d4e8;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
        }
        .field-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid #e0e8f0;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .field-item input { flex: 2; width: auto; }
        .field-item select { flex: 1; width: auto; }
        .btn {
            background: #1a4d6b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-add { background: #27ae60; }
        .btn-save { background: #2980b9; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        hr { margin: 20px 0; }
        .order-input { width: 60px !important; text-align: center; }
        .delete-checkbox { display: flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ Modifier le formulaire</h1>
        
        <?php if ($success): ?>
            <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="update_form">
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($form['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?php echo htmlspecialchars($form['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Type de formulaire</label>
                <select name="form_type">
                    <?php foreach ($form_types as $key => $name): ?>
                        <option value="<?php echo $key; ?>" <?php echo $form['form_type'] === $key ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
        
        <hr>
        
        <h3>Questions</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_fields">
            
            <?php foreach ($fields as $field): ?>
            <div class="field-item">
                <input type="number" name="field_order[<?php echo $field['id']; ?>]" value="<?php echo $field['field_order']; ?>" class="order-input" placeholder="Ordre">
                <input type="text" value="<?php echo htmlspecialchars($field['label']); ?>" readonly style="background:#eef2f5;">
                <span style="flex:1;"><?php echo $question_types[$field['question_type']] ?? $field['question_type']; ?></span>
                <label class="delete-checkbox">
                    <input type="checkbox" name="delete_fields[]" value="<?php echo $field['id']; ?>"> Supprimer
                </label>
            </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn btn-save">Sauvegarder l'ordre et les suppressions</button>
        </form>
        
        <hr>
        
        <h3>Ajouter une question</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_field">
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <input type="text" name="label" placeholder="Libellé de la question" style="flex:2;" required>
                <select name="question_type" style="flex:1;">
                    <?php foreach ($question_types as $key => $name): ?>
                        <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="options" placeholder="Options (séparées par |)">
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="required"> Obligatoire
                </label>
                <button type="submit" class="btn btn-add">+ Ajouter</button>
            </div>
        </form>
        
        <br>
        <a href="forms.php" class="btn">← Retour à la liste</a>
    </div>
</body>
</html>