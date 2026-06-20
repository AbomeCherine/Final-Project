<?php
session_start();
require_once 'config.php';
require_once 'lang.php';

$current_lang = $_SESSION['lang'] ?? 'en';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

$form_types = [
    'survey' => ['name' => __('form_type_survey'), 'desc' => 'Enquete, feedback, satisfaction', 'icon' => '📋'],
    'checklist' => ['name' => __('form_type_checklist'), 'desc' => 'Inspection, audit, conformite', 'icon' => '✅'],
    'incident' => ['name' => __('form_type_incident'), 'desc' => 'Signalement d\'evenements', 'icon' => '⚠️'],
    'registration' => ['name' => __('form_type_registration'), 'desc' => 'Enregistrement de participants', 'icon' => '📝'],
    'kpi' => ['name' => __('form_type_kpi'), 'desc' => 'Indicateurs de performance', 'icon' => '📊'],
    'poll' => ['name' => __('form_type_poll'), 'desc' => 'Question unique, resultats instantanes', 'icon' => '🗳️']
];

$question_types = [
    'text' => ['name' => __('question_type_text')],
    'textarea' => ['name' => __('question_type_textarea')],
    'email' => ['name' => __('question_type_email')],
    'number' => ['name' => __('question_type_number')],
    'date' => ['name' => __('question_type_date')],
    'select' => ['name' => __('question_type_select'), 'has_options' => true],
    'radio' => ['name' => __('question_type_radio'), 'has_options' => true],
    'checkbox' => ['name' => __('question_type_checkbox'), 'has_options' => true],
    'scale' => ['name' => __('question_type_scale'), 'has_options' => true],
    'rating' => ['name' => __('question_type_rating'), 'has_options' => true],
    'file' => ['name' => __('question_type_file')]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $form_type = $_POST['form_type'];
    
    if (empty($title)) {
        $error = __('fill_fields');
    } else {
        $sql = "INSERT INTO forms (organization_id, title, description, form_type, is_published) VALUES (?, ?, ?, ?, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['org_id'], $title, $description, $form_type]);
        $form_id = $pdo->lastInsertId();
        
        if (isset($_POST['field_label'])) {
            for ($i = 0; $i < count($_POST['field_label']); $i++) {
                if (!empty($_POST['field_label'][$i])) {
                    $options = $_POST['field_options'][$i] ?? '';
                    $required = isset($_POST['field_required'][$i]) ? 1 : 0;
                    $fillable_by = $_POST['field_fillable_by'][$i] ?? 'public';
                    
                    $sql = "INSERT INTO form_fields (form_id, field_order, question_type, label, options, required, fillable_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$form_id, $i, $_POST['field_type'][$i], $_POST['field_label'][$i], $options, $required, $fillable_by]);
                }
            }
        }
        
        $success = __('form_sent');
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('create_form'); ?> - <?php echo __('aviation_portal'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-text { font-weight: 500; font-size: 18px; color: #1a4d6b; }
        .btn-save { background: #1a73e8; color: white; border: none; padding: 10px 24px; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .btn-save:hover { background: #1557b0; }
        .container { max-width: 800px; margin: 32px auto; padding: 0 20px; }
        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .form-header { padding: 32px 32px 20px 32px; border-bottom: 1px solid #e0e0e0; }
        .form-title {
            font-size: 32px;
            font-weight: 500;
            border: none;
            width: 100%;
            padding: 8px 0;
            margin-bottom: 8px;
            font-family: 'Inter', sans-serif;
        }
        .form-title:focus { outline: none; border-bottom: 2px solid #1a73e8; }
        .form-desc {
            font-size: 14px;
            color: #5f6368;
            border: none;
            width: 100%;
            padding: 8px 0;
            font-family: 'Inter', sans-serif;
        }
        .form-desc:focus { outline: none; border-bottom: 1px solid #1a73e8; }
        
        .type-selector { padding: 16px 32px; background: #fafafa; border-bottom: 1px solid #e0e0e0; }
        .type-label { font-size: 12px; color: #5f6368; margin-bottom: 12px; }
        .type-grid { display: flex; gap: 12px; flex-wrap: wrap; }
        .type-chip {
            background: white;
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .type-chip:hover { background: #f1f3f4; }
        .type-chip.selected { background: #e8f0fe; border-color: #1a73e8; color: #1a73e8; }
        
        .question-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .question-header { padding: 24px 32px; display: flex; gap: 20px; align-items: flex-start; }
        .question-input { flex: 1; }
        .question-input input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 0;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
        }
        .question-input input:focus { outline: none; border-bottom-color: #1a73e8; }
        .question-type { width: 180px; padding: 8px 12px; border: 1px solid #dadce0; border-radius: 4px; background: white; font-family: 'Inter', sans-serif; }
        
        .question-options { padding: 0 32px 20px 32px; }
        .option-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .option-input { flex: 1; padding: 8px 12px; border: 1px solid #dadce0; border-radius: 4px; }
        .btn-remove-option { background: none; border: none; cursor: pointer; color: #5f6368; font-size: 18px; }
        .btn-add-option { background: none; border: none; color: #1a73e8; cursor: pointer; font-size: 14px; margin-top: 8px; }
        
        .question-footer {
            padding: 12px 32px 20px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            border-top: 1px solid #f0f0f0;
        }
        .required-check { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #5f6368; }
        .fillable-select { padding: 6px 12px; border-radius: 6px; border: 1px solid #dadce0; background: white; font-size: 12px; cursor: pointer; }
        .btn-delete { background: none; border: none; color: #d32f2f; cursor: pointer; font-size: 13px; }
        
        .btn-add-question {
            background: white;
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 16px;
            width: 100%;
            text-align: center;
            color: #1a73e8;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 24px;
        }
        .btn-add-question:hover { background: #f8f9fa; }
        .success-message { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php echo language_switcher(); ?>
    
    <div class="header">
        <div class="header-content">
            <span class="logo-text"><?php echo __('create_form'); ?></span>
            <button type="submit" form="formBuilder" class="btn-save"><?php echo __('publish'); ?></button>
        </div>
    </div>

    <div class="container">
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?> <a href="admin/forms.php"><?php echo __('back_to_forms'); ?></a></div>
        <?php endif; ?>

        <form id="formBuilder" method="POST">
            <div class="form-card">
                <div class="form-header">
                    <input type="text" name="title" class="form-title" placeholder="<?php echo __('form_title'); ?>" required>
                    <input type="text" name="description" class="form-desc" placeholder="<?php echo __('form_description'); ?>">
                </div>
                <div class="type-selector">
                    <div class="type-label"><?php echo __('select_form_type'); ?></div>
                    <div class="type-grid">
                        <?php foreach ($form_types as $key => $type): ?>
                            <div class="type-chip" onclick="selectFormType('<?php echo $key; ?>')" data-type="<?php echo $key; ?>">
                                <?php echo $type['icon']; ?> <?php echo $type['name']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="form_type" id="form_type" value="survey">
                </div>
            </div>

            <div id="questions-container"></div>

            <button type="button" class="btn-add-question" onclick="addQuestion()">
                + <?php echo __('add_question'); ?>
            </button>
        </form>
    </div>

    <script>
        let questionCount = 0;
        let currentFormType = 'survey';
        
        function selectFormType(type) {
            currentFormType = type;
            document.getElementById('form_type').value = type;
            
            
            document.querySelectorAll('.type-chip').forEach(chip => {
                chip.classList.remove('selected');
                if (chip.dataset.type === type) chip.classList.add('selected');
            });
            
            
            const container = document.getElementById('questions-container');
            container.innerHTML = '';
            questionCount = 0;
            
            switch(type) {
                case 'checklist':
                    addQuestionWithType('Point à vérifier', 'radio');
                    addQuestionWithType('Conforme ?', 'radio');
                    addQuestionWithType('Commentaires', 'textarea');
                    break;
                case 'incident':
                    addQuestionWithType('Date de l\'incident', 'date');
                    addQuestionWithType('Description de l\'incident', 'textarea');
                    addQuestionWithType('Gravité', 'radio');
                    addQuestionWithType('Lieu', 'text');
                    break;
                case 'registration':
                    addQuestionWithType('Nom complet', 'text');
                    addQuestionWithType('Email', 'email');
                    addQuestionWithType('Téléphone', 'text');
                    addQuestionWithType('Organisation', 'text');
                    break;
                case 'kpi':
                    addQuestionWithType('Indicateur', 'text');
                    addQuestionWithType('Valeur cible', 'number');
                    addQuestionWithType('Valeur atteinte', 'number');
                    addQuestionWithType('Commentaires', 'textarea');
                    break;
                case 'poll':
                    addQuestionWithType('Question du sondage', 'radio');
                    break;
                default: 
                    addQuestionWithType('Question 1', 'text');
                    addQuestionWithType('Question 2', 'text');
                    addQuestionWithType('Question 3', 'textarea');
                    break;
            }
        }
        
        function addQuestion() {
            addQuestionWithType('Question', 'text');
        }
        
        function addQuestionWithType(placeholder, type) {
            const container = document.getElementById('questions-container');
            const qid = questionCount;
            
            const div = document.createElement('div');
            div.className = 'question-card';
            div.innerHTML = `
                <div class="question-header">
                    <div class="question-input">
                        <input type="text" name="field_label[${qid}]" placeholder="${placeholder}" required>
                    </div>
                    <select name="field_type[${qid}]" class="question-type" onchange="toggleOptions(this, ${qid})">
                        <option value="text" ${type === 'text' ? 'selected' : ''}>Texte court</option>
                        <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Texte long</option>
                        <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="number" ${type === 'number' ? 'selected' : ''}>Nombre</option>
                        <option value="date" ${type === 'date' ? 'selected' : ''}>Date</option>
                        <option value="radio" ${type === 'radio' ? 'selected' : ''}>Choix unique</option>
                        <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>Choix multiples</option>
                        <option value="select">Liste déroulante</option>
                        <option value="scale">Échelle 1-5</option>
                        <option value="rating">Note sur 5</option>
                        <option value="file">Fichier</option>
                    </select>
                </div>
                <div class="question-options" id="options-${qid}" style="display:none;">
                    <div id="options-list-${qid}">
                        <div class="option-row">
                            <input type="text" name="field_options[${qid}]" class="option-input" placeholder="Option 1" value="">
                        </div>
                    </div>
                    <button type="button" class="btn-add-option" onclick="addOption(${qid})">+ Ajouter une option</button>
                </div>
                <div class="question-footer">
                    <label class="required-check">
                        <input type="checkbox" name="field_required[${qid}]"> Question obligatoire
                    </label>
                    <select name="field_fillable_by[${qid}]" class="fillable-select">
                        <option value="public">Public (tout le monde)</option>
                        <option value="staff_only">Staff uniquement</option>
                        <option value="hidden">Cachée (invisible pour le public)</option>
                    </select>
                    <button type="button" class="btn-delete" onclick="this.closest('.question-card').remove()">Supprimer</button>
                </div>
            `;
            container.appendChild(div);
            questionCount++;
        }
        
        function toggleOptions(select, qid) {
            const optionsDiv = document.getElementById(`options-${qid}`);
            const value = select.value;
            if (value === 'radio' || value === 'checkbox' || value === 'select' || value === 'scale' || value === 'rating') {
                optionsDiv.style.display = 'block';
                if (value === 'scale') {
                    document.querySelector(`input[name="field_options[${qid}]"]`).value = '1|2|3|4|5';
                } else if (value === 'rating') {
                    document.querySelector(`input[name="field_options[${qid}]"]`).value = '1|2|3|4|5';
                }
            } else {
                optionsDiv.style.display = 'none';
            }
        }
        
        function addOption(qid) {
            const container = document.getElementById(`options-list-${qid}`);
            const optionCount = container.children.length;
            const div = document.createElement('div');
            div.className = 'option-row';
            div.innerHTML = `
                <input type="text" name="field_options[${qid}]" class="option-input" placeholder="Option ${optionCount + 1}">
                <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">✕</button>
            `;
            container.appendChild(div);
        }
        
        
        selectFormType('survey');
    </script>
</body>
</html>