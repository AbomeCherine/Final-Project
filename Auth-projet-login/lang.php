<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}


if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

$current_lang = $_SESSION['lang'];

$translations = [
    'fr' => [
        
        'aviation_portal' => 'Aviation Portal',
        'login' => 'Connexion',
        'register' => 'Inscription',
        'logout' => 'Déconnexion',
        'dashboard' => 'Tableau de bord',
        'welcome' => 'Bienvenue',
        'email' => 'Email',
        'password' => 'Mot de passe',
        'confirm_password' => 'Confirmer le mot de passe',
        'username' => 'Nom d\'utilisateur',
        'no_account' => 'Pas de compte ?',
        'yes_account' => 'Déjà un compte ?',
        'fill_fields' => 'Veuillez remplir tous les champs.',
        'invalid_credentials' => 'Identifiant ou mot de passe incorrect.',
        'registration_success' => 'Inscription réussie ! Vous pouvez vous connecter.',
        'password_mismatch' => 'Les mots de passe ne correspondent pas.',
        'password_too_short' => 'Le mot de passe doit contenir au moins 6 caractères.',
        'invalid_email' => 'Email invalide.',
        'user_exists' => 'Nom d\'utilisateur ou email déjà utilisé.',
        
        
        'admin_dashboard' => 'Tableau de bord Administrateur',
        'manage_forms' => 'Gérer les formulaires',
        'manage_users' => 'Gérer les utilisateurs',
        'view_submissions' => 'Voir les soumissions',
        'create_form' => 'Créer un formulaire',
        'edit_form' => 'Modifier',
        'delete' => 'Supprimer',
        'publish' => 'Publier',
        'unpublish' => 'Dépublier',
        'published' => 'Publié',
        'draft' => 'Brouillon',
        'actions' => 'Actions',
        'title' => 'Titre',
        'description' => 'Description',
        'type' => 'Type',
        'questions' => 'Questions',
        'date' => 'Date',
        'status' => 'Statut',
        'save' => 'Enregistrer',
        'cancel' => 'Annuler',
        'back' => 'Retour',
        'send' => 'Envoyer',
        'track' => 'Suivi',
        'export' => 'Exporter',
        'quick_actions' => 'Actions rapides',
        'recent_submissions' => 'Dernières soumissions',
        'stat_forms' => 'Formulaires',
        'stat_submissions' => 'Soumissions',
        'stat_users' => 'Utilisateurs',
        'stat_organizations' => 'Organisations',
        'view_all' => 'Voir tout',
        'no_data' => 'Aucune donnée disponible.',
        
       
        'fill_form' => 'Remplir le formulaire',
        'submissions' => 'Soumissions',
        'submitted_at' => 'Soumis le',
        'score' => 'Score',
        'compliance' => 'Conformité',
        'responses' => 'Réponses',
        'question' => 'Question',
        'answer' => 'Réponse',
        'required' => 'Obligatoire',
        'optional' => 'Optionnel',
        'add_question' => 'Ajouter une question',
        'add_option' => 'Ajouter une option',
        'form_title' => 'Titre du formulaire',
        'form_description' => 'Description du formulaire',
        'select_form_type' => 'Type de formulaire',
        'submit_form' => 'Envoyer le formulaire',
        'form_sent' => 'Formulaire envoyé avec succès !',
        'back_to_forms' => 'Retour aux formulaires',
        
        
        'form_type_survey' => 'Questionnaire',
        'form_type_checklist' => 'Checklist',
        'form_type_incident' => 'Rapport d\'incident',
        'form_type_registration' => 'Inscription',
        'form_type_kpi' => 'Évaluation KPI',
        'form_type_poll' => 'Sondage',
        
        
        'question_type_text' => 'Texte court',
        'question_type_textarea' => 'Texte long',
        'question_type_email' => 'Email',
        'question_type_number' => 'Nombre',
        'question_type_date' => 'Date',
        'question_type_select' => 'Liste déroulante',
        'question_type_radio' => 'Choix unique',
        'question_type_checkbox' => 'Choix multiples',
        'question_type_scale' => 'Échelle 1-5',
        'question_type_rating' => 'Note sur 5',
        'question_type_file' => 'Fichier',
        
        
        'otp_request' => 'Recevoir un code',
        'otp_verify' => 'Vérifier le code',
        'code' => 'Code de vérification',
        'enter_code' => 'Entrez le code à 6 chiffres',
        'resend_code' => 'Renvoyer le code',
        'otp_sent' => 'Un code a été envoyé à votre',
        'invalid_code' => 'Code invalide ou expiré.',
        'access_form' => 'Accéder au formulaire',
        'identify_yourself' => 'Identifiez-vous pour continuer',
        'phone' => 'Téléphone',
        'enter_phone' => 'Numéro de téléphone',
        'enter_email' => 'Adresse email',
        
        
        'send_invitations' => 'Envoyer des invitations',
        'recipients' => 'Destinataires',
        'recipients_help' => 'Un par ligne ou séparés par des virgules',
        'send_method' => 'Mode d\'envoi',
        'invitation_sent' => 'invitation(s) envoyée(s)',
        'invitation_history' => 'Historique des invitations',
        'contact' => 'Contact',
        'sent_at' => 'Envoyé le',
        'opened_at' => 'Ouvert le',
        'status_sent' => 'Envoyé',
        'status_opened' => 'Ouvert',
        'status_submitted' => 'Soumis',
        'resend' => 'Renvoyer',
        
        
        'export_csv' => 'Exporter en CSV',
        'export_pdf' => 'Exporter en PDF',
        'average_score' => 'Score moyen',
        'high_compliance' => 'Conformité ≥ 75%',
        
       
        'role_admin' => 'Administrateur',
        'role_manager' => 'Manager',
        'role_staff' => 'Membre',
        'role_super_admin' => 'Super Administrateur',
        
        
        'add_user' => 'Ajouter un utilisateur',
        'edit_user' => 'Modifier l\'utilisateur',
        'delete_user' => 'Supprimer l\'utilisateur',
        'change_role' => 'Changer le rôle',
        'user_list' => 'Liste des utilisateurs',
        'id' => 'ID',
        'name' => 'Nom',
        'created_at' => 'Date de création',
        
        
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ?',
        'confirm_unpublish' => 'Êtes-vous sûr de vouloir dépublier ce formulaire ?',
    ],
    'en' => [
        
        'aviation_portal' => 'Aviation Portal',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'dashboard' => 'Dashboard',
        'welcome' => 'Welcome',
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm password',
        'username' => 'Username',
        'no_account' => 'No account?',
        'yes_account' => 'Already have an account?',
        'fill_fields' => 'Please fill all fields.',
        'invalid_credentials' => 'Invalid username or password.',
        'registration_success' => 'Registration successful! You can now log in.',
        'password_mismatch' => 'Passwords do not match.',
        'password_too_short' => 'Password must be at least 6 characters.',
        'invalid_email' => 'Invalid email address.',
        'user_exists' => 'Username or email already exists.',
        
        
        'admin_dashboard' => 'Admin Dashboard',
        'manage_forms' => 'Manage forms',
        'manage_users' => 'Manage users',
        'view_submissions' => 'View submissions',
        'create_form' => 'Create form',
        'edit_form' => 'Edit',
        'delete' => 'Delete',
        'publish' => 'Publish',
        'unpublish' => 'Unpublish',
        'published' => 'Published',
        'draft' => 'Draft',
        'actions' => 'Actions',
        'title' => 'Title',
        'description' => 'Description',
        'type' => 'Type',
        'questions' => 'Questions',
        'date' => 'Date',
        'status' => 'Status',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'back' => 'Back',
        'send' => 'Send',
        'track' => 'Track',
        'export' => 'Export',
        'quick_actions' => 'Quick actions',
        'recent_submissions' => 'Recent submissions',
        'stat_forms' => 'Forms',
        'stat_submissions' => 'Submissions',
        'stat_users' => 'Users',
        'stat_organizations' => 'Organizations',
        'view_all' => 'View all',
        'no_data' => 'No data available.',
        
        
        'fill_form' => 'Fill the form',
        'submissions' => 'Submissions',
        'submitted_at' => 'Submitted at',
        'score' => 'Score',
        'compliance' => 'Compliance',
        'responses' => 'Responses',
        'question' => 'Question',
        'answer' => 'Answer',
        'required' => 'Required',
        'optional' => 'Optional',
        'add_question' => 'Add question',
        'add_option' => 'Add option',
        'form_title' => 'Form title',
        'form_description' => 'Form description',
        'select_form_type' => 'Form type',
        'submit_form' => 'Submit form',
        'form_sent' => 'Form submitted successfully!',
        'back_to_forms' => 'Back to forms',
        
       
        'form_type_survey' => 'Survey',
        'form_type_checklist' => 'Checklist',
        'form_type_incident' => 'Incident report',
        'form_type_registration' => 'Registration',
        'form_type_kpi' => 'KPI evaluation',
        'form_type_poll' => 'Poll',
        
        
        'question_type_text' => 'Short text',
        'question_type_textarea' => 'Long text',
        'question_type_email' => 'Email',
        'question_type_number' => 'Number',
        'question_type_date' => 'Date',
        'question_type_select' => 'Dropdown',
        'question_type_radio' => 'Single choice',
        'question_type_checkbox' => 'Multiple choices',
        'question_type_scale' => 'Scale 1-5',
        'question_type_rating' => 'Rating 1-5',
        'question_type_file' => 'File',
        
        
        'otp_request' => 'Receive a code',
        'otp_verify' => 'Verify code',
        'code' => 'Verification code',
        'enter_code' => 'Enter the 6-digit code',
        'resend_code' => 'Resend code',
        'otp_sent' => 'A code has been sent to your',
        'invalid_code' => 'Invalid or expired code.',
        'access_form' => 'Access the form',
        'identify_yourself' => 'Identify yourself to continue',
        'phone' => 'Phone',
        'enter_phone' => 'Phone number',
        'enter_email' => 'Email address',
        
        
        'send_invitations' => 'Send invitations',
        'recipients' => 'Recipients',
        'recipients_help' => 'One per line or separated by commas',
        'send_method' => 'Send method',
        'invitation_sent' => 'invitation(s) sent',
        'invitation_history' => 'Invitation history',
        'contact' => 'Contact',
        'sent_at' => 'Sent at',
        'opened_at' => 'Opened at',
        'status_sent' => 'Sent',
        'status_opened' => 'Opened',
        'status_submitted' => 'Submitted',
        'resend' => 'Resend',
        
        
        'export_csv' => 'Export to CSV',
        'export_pdf' => 'Export to PDF',
        'average_score' => 'Average score',
        'high_compliance' => 'Compliance ≥ 75%',
        
        
        'role_admin' => 'Administrator',
        'role_manager' => 'Manager',
        'role_staff' => 'Staff',
        'role_super_admin' => 'Super Administrator',
        
        
        'add_user' => 'Add user',
        'edit_user' => 'Edit user',
        'delete_user' => 'Delete user',
        'change_role' => 'Change role',
        'user_list' => 'User list',
        'id' => 'ID',
        'name' => 'Name',
        'created_at' => 'Created at',
        
        
        'confirm_delete' => 'Are you sure you want to delete?',
        'confirm_unpublish' => 'Are you sure you want to unpublish this form?',
    ],
    'rw' => [

        'aviation_portal' => 'Icyerekezo cy\'Ubugenzi bwo mu Kirere',
        'login' => 'Injira',
        'register' => 'Iyandikishe',
        'logout' => 'Sohora',
        'dashboard' => 'Akadomo',
        'welcome' => 'Murakaza neza',
        'email' => 'Imeli',
        'password' => 'Ijambobanga',
        'confirm_password' => 'Emeza ijambobanga',
        'username' => 'Izina',
        'no_account' => 'Nta konte?',
        'yes_account' => 'Konte ibaho?',
        'fill_fields' => 'Uzuzuze ibyaranzwe byose.',
        'invalid_credentials' => 'Izina cyangwa ijambobanga si byo.',
        'registration_success' => 'Iyandikishwa ryakunze! Ushobora noneho kwinjira.',
        'password_mismatch' => 'Ijambobanga ntirihuye.',
        'password_too_short' => 'Ijambobanga rigomba kuba byibuze inyuguti 6.',
        'invalid_email' => 'Imeli si yo.',
        'user_exists' => 'Izina cyangwa imeli bimaze kubaho.',
        
        
        'admin_dashboard' => 'Akadomo k\'Ubuyobozi',
        'manage_forms' => 'Gucungira impapuro',
        'manage_users' => 'Gucungira abakoresha',
        'view_submissions' => 'Reba ibyatanzwe',
        'create_form' => 'Kora urupapuro',
        'edit_form' => 'Hindura',
        'delete' => 'Siba',
        'publish' => 'Tangaza',
        'unpublish' => 'Kuraho',
        'published' => 'Byatangajwe',
        'draft' => 'Igishashwe',
        'actions' => 'Ibikorwa',
        'title' => 'Umutwe',
        'description' => 'Ibisobanuro',
        'type' => 'Ubwoko',
        'questions' => 'Ibibazo',
        'date' => 'Itariki',
        'status' => 'Iriburiro',
        'save' => 'Bika',
        'cancel' => 'Hagarika',
        'back' => 'Subira',
        'send' => 'Ohereza',
        'track' => 'Kurikirana',
        'export' => 'Sohora',
        'quick_actions' => 'Ibikorwa byihuse',
        'recent_submissions' => 'Ibyatanzwe vuba',
        'stat_forms' => 'Impapuro',
        'stat_submissions' => 'Ibyatanzwe',
        'stat_users' => 'Abakoresha',
        'stat_organizations' => 'Amashyirahamwe',
        'view_all' => 'Reba byose',
        'no_data' => 'Nta makuru aboneka.',
        
        
        'fill_form' => 'Uzuzuze urupapuro',
        'submissions' => 'Ibyatanzwe',
        'submitted_at' => 'Byatanzwe ku',
        'score' => 'Amanota',
        'compliance' => 'Kwubahiriza',
        'responses' => 'Ibicuro',
        'question' => 'Ikibazo',
        'answer' => 'Igisubizo',
        'required' => 'Birakenewe',
        'optional' => 'Bishoboka',
        'add_question' => 'Ongera ikibazo',
        'add_option' => 'Ongera umuhitamo',
        'form_title' => 'Umutwe w\'urupapuro',
        'form_description' => 'Ibisobanuro by\'urupapuro',
        'select_form_type' => 'Ubwoko bw\'urupapuro',
        'submit_form' => 'Ohereza urupapuro',
        'form_sent' => 'Urupapuro rwoherejwe neza!',
        'back_to_forms' => 'Subira ku mpapuro',
        
        
        'form_type_survey' => 'Ikibazo',
        'form_type_checklist' => 'Urutonde',
        'form_type_incident' => 'Raporo y\'ibyabaye',
        'form_type_registration' => 'Iyandikisha',
        'form_type_kpi' => 'Isuzuma KPI',
        'form_type_poll' => 'Amajwi',
        
        
        'question_type_text' => 'Inyandiko ngufi',
        'question_type_textarea' => 'Inyandiko ndende',
        'question_type_email' => 'Imeli',
        'question_type_number' => 'Imibare',
        'question_type_date' => 'Itariki',
        'question_type_select' => 'Urutonde',
        'question_type_radio' => 'Guhitamo kumwe',
        'question_type_checkbox' => 'Guhitamo byinshi',
        'question_type_scale' => 'Igipimo 1-5',
        'question_type_rating' => 'Amanota 1-5',
        'question_type_file' => 'Dosiye',
        
        
        'otp_request' => 'Kira kode',
        'otp_verify' => 'Emeza kode',
        'code' => 'Kode y\'emeza',
        'enter_code' => 'Andika kode y\'imibare 6',
        'resend_code' => 'Ongera kohereza kode',
        'otp_sent' => 'Kode yoherejwe kuri',
        'invalid_code' => 'Kode si yo cyangwa yashaje.',
        'access_form' => 'Kwinjira kurupapuro',
        'identify_yourself' => 'Yerekane kugira ngo ukomeze',
        'phone' => 'Telefone',
        'enter_phone' => 'Nomero ya telefone',
        'enter_email' => 'Adresi ya imeli',
        
        
        'send_invitations' => 'Kohereza ubutumire',
        'recipients' => 'Abakiriwe',
        'recipients_help' => 'Umunye kumurongo cyangwa utandukanywe n\'akomo',
        'send_method' => 'Uburyo bwo kohereza',
        'invitation_sent' => 'ubutumire bwoherejwe',
        'invitation_history' => 'Amateka y\'ubutumire',
        'contact' => 'Ihuriro',
        'sent_at' => 'Byoherejwe ku',
        'opened_at' => 'Byakinguwe ku',
        'status_sent' => 'Byoherejwe',
        'status_opened' => 'Byakinguwe',
        'status_submitted' => 'Byatanzwe',
        'resend' => 'Ongera kohereza',
        
        
        'export_csv' => 'Sohora muri CSV',
        'export_pdf' => 'Sohora muri PDF',
        'average_score' => 'Amanota hagati',
        'high_compliance' => 'Kwubahiriza ≥ 75%',
        
        
        'role_admin' => 'Ubuyobozi',
        'role_manager' => 'Umuyobozi',
        'role_staff' => 'Umukozi',
        'role_super_admin' => 'Ubuyobozi Bukuru',
        
        
        'add_user' => 'Ongera umukoresha',
        'edit_user' => 'Hindura umukoresha',
        'delete_user' => 'Siba umukoresha',
        'change_role' => 'Hindura inshingano',
        'user_list' => 'Urutonde rw\'abakoresha',
        'id' => 'ID',
        'name' => 'Izina',
        'created_at' => 'Itariki yaremanywe',
        
      
        'confirm_delete' => 'Urahatiye ko ushaka gusiba?',
        'confirm_unpublish' => 'Urahatiye ko ushaka gukuraho urupapuro?',
    ]
];

function __($key) {
    global $translations, $current_lang;
    return $translations[$current_lang][$key] ?? $translations['fr'][$key] ?? $key;
}

function language_switcher() {
    $current_lang = $_SESSION['lang'] ?? 'fr';
    $languages = [
        'fr' => ['flag' => '🇫🇷', 'name' => 'Français'],
        'en' => ['flag' => '🇬🇧', 'name' => 'English'],
        'rw' => ['flag' => '🇷🇼', 'name' => 'Kinyarwanda']
    ];
    $html = '<div class="lang-switcher" style="position: fixed; top: 20px; right: 20px; z-index: 1000; background: white; padding: 8px 15px; border-radius: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">';
    foreach ($languages as $code => $info) {
        $active = $code === $current_lang ? 'style="font-weight: bold; color: #1a4d6b;"' : '';
        $html .= "<a href='?lang=$code' $active style='margin: 0 8px; text-decoration: none;'>$info[flag] $info[name]</a>";
    }
    $html .= '</div>';
    return $html;
}
?>