<?php
/**
 * ملف المصادقة والصلاحيات
 * خاص بلوحة التحكم الإدارية
 */

// منع الوصول المباشر
if (!defined('PROJECT_ROOT')) {
    die('Direct access not permitted');
}

// بدء الجلسة إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'secure' => false, // ضع true إذا كنت تستخدم HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

/**
 * تسجيل دخول المشرف
 */
function admin_login($admin_id, $admin_name, $admin_email) {
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['admin_name'] = $admin_name;
    $_SESSION['admin_email'] = $admin_email;
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['last_activity'] = time();
}

/**
 * تسجيل خروج المشرف
 */
function admin_logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * التحقق إذا كان المشرف مسجل الدخول
 */
function is_admin_logged_in() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        return false;
    }
    
    // التحقق من انتهاء الجلسة
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        admin_logout();
        return false;
    }
    
    // تحديث وقت النشاط
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * طلب مصادقة المشرف (إجبارية)
 */
function require_admin() {
    if (!is_admin_logged_in()) {
        set_flash_message('error', 'يجب تسجيل الدخول أولاً');
        redirect(ADMIN_BASE_URL . 'login.php');
        exit();
    }
}

/**
 * الحصول على معلومات المشرف الحالي
 */
function get_current_admin() {
    if (!is_admin_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'name' => $_SESSION['admin_name'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null
    ];
}

/**
 * التحقق من الصلاحيات (للتوسع المستقبلي)
 */
function has_permission($permission) {
    // هذه دالة أساسية، يمكن توسيعها حسب الحاجة
    return is_admin_logged_in();
}

/**
 * دالة للمصادقة العامة للمستخدمين (للتطبيق)
 * يمكن استخدامها للتحقق من المستخدمين العاديين
 */
function verify_user_credentials($email, $password) {
    require_once __DIR__ . '/../config/db.php';
    
    $sql = "SELECT id, full_name, email, password_hash, role, is_active 
            FROM users 
            WHERE email = ?";
    
    $user = db_fetch($sql, [$email]);
    
    if (!$user) {
        return false;
    }
    
    // التحقق من حالة الحساب
    if ($user['is_active'] != 1) {
        return false;
    }
    
    // التحقق من كلمة المرور
    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }
    
    // إرجاع بيانات المستخدم بدون كلمة المرور
    unset($user['password_hash']);
    return $user;
}

/**
 * إنشاء رمز API للمستخدم (للتطبيق)
 */
function generate_api_token($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // هنا يمكن تخزين الرمز في قاعدة البيانات إذا أردت
    return [
        'token' => $token,
        'expires_at' => $expires_at
    ];
}
?>