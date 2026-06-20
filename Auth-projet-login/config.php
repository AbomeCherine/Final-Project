<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost\\SQLEXPRESS";  
$dbname = "projet_login";          
$username = "";                    
$password = "";                    

try {
    $pdo = new PDO("sqlsrv:Server=$host;Database=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Charger les informations de l'utilisateur connecté
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT u.*, o.id as org_id, o.name as org_name, o.slug as org_slug
        FROM users u
        LEFT JOIN organizations o ON u.organization_id = o.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();
    
    if ($userData) {
        $_SESSION['org_id'] = $userData['org_id'] ?? 1;
        $_SESSION['org_name'] = $userData['org_name'] ?? 'ANAC Gabon';
        $_SESSION['org_slug'] = $userData['org_slug'] ?? 'anac-gabon';
        $_SESSION['role'] = $userData['role'] ?? 'staff';
        $_SESSION['username'] = $userData['username'];
        $_SESSION['email'] = $userData['email'];
    }
}

/**
 * Check if current user is admin.
 *
 * @return bool
 */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if current user is staff.
 *
 * @return bool
 */
function isStaff(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

/**
 * Check if current user is manager.
 *
 * @return bool
 */
function isManager(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
}

/**
 * Send a notification to a user.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param string $type
 * @param int|null $entityId
 * @param string $message
 * @return void
 */
function sendNotification(PDO $pdo, int $userId, string $type, ?int $entityId, string $message): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, entity_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $type, $entityId, $message]);
}

/**
 * Get unread notifications for a user.
 *
 * @param PDO $pdo
 * @param int $userId
 * @return array
 */
function getUnreadNotifications(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Mark all notifications as read for a user.
 *
 * @param PDO $pdo
 * @param int $userId
 * @return void
 */
function markNotificationsAsRead(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
}

/**
 * Send a notification to all managers of an organization.
 *
 * @param PDO $pdo
 * @param int $organizationId
 * @param string $type
 * @param int|null $entityId
 * @param string $message
 * @return void
 */
function notifyManagers(PDO $pdo, int $organizationId, string $type, ?int $entityId, string $message): void {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'manager' AND organization_id = ?");
    $stmt->execute([$organizationId]);
    $managers = $stmt->fetchAll();
    
    foreach ($managers as $manager) {
        sendNotification($pdo, $manager['id'], $type, $entityId, $message);
    }
}

/**
 * Send a notification to all admins of an organization.
 *
 * @param PDO $pdo
 * @param int $organizationId
 * @param string $type
 * @param int|null $entityId
 * @param string $message
 * @return void
 */
function notifyAdmins(PDO $pdo, int $organizationId, string $type, ?int $entityId, string $message): void {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' AND organization_id = ?");
    $stmt->execute([$organizationId]);
    $admins = $stmt->fetchAll();
    
    foreach ($admins as $admin) {
        sendNotification($pdo, $admin['id'], $type, $entityId, $message);
    }
}

function auditLog(PDO $pdo, string $action, ?string $entityType = null, ?int $entityId = null, mixed $oldValues = null, mixed $newValues = null): void {
    if (!isset($_SESSION['user_id'])) return;
    
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, organization_id, action, entity_type, entity_id, old_values, new_values, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['org_id'] ?? null,
        $action,
        $entityType,
        $entityId,
        $oldValues ? json_encode($oldValues) : null,
        $newValues ? json_encode($newValues) : null,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}
?>