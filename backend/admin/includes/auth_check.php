<?php
/**
 * ملف التحقق من جلسة المشرف
 * يجب تضمينه في كل صفحة تحتاج إلى مصادقة
 */

// تضمين التكوين إذا لم يُضمَّن (يضمن تعريف ADMIN_BASE_URL)
if (!defined('ADMIN_BASE_URL')) {
    require_once __DIR__ . '/../../config/config.php';
}

// بدء الجلسة إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إذا لم يكن مسجل الدخول، توجيه إلى صفحة تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // حفظ الصفحة الحالية للعودة إليها بعد تسجيل الدخول
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // توجيه إلى صفحة تسجيل الدخول (مسار مطلق داخل لوحة الإدارة إن أمكن)
    $login_url = defined('ADMIN_BASE_URL') ? ADMIN_BASE_URL . 'login.php' : '../login.php';
    header('Location: ' . $login_url);
    exit();
}

// التحقق من انتهاء الجلسة (30 دقيقة)
$session_timeout = 30 * 60; // 30 دقيقة بالثواني

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // تدمير الجلسة وتوجيه إلى تسجيل الدخول
    session_unset();
    session_destroy();
    
    $login_url = defined('ADMIN_BASE_URL') ? ADMIN_BASE_URL . 'login.php?expired=1' : '../login.php?expired=1';
    header('Location: ' . $login_url);
    exit();
}

// تحديث وقت النشاط الأخير
$_SESSION['last_activity'] = time();

/**
 * دالة للحصول على بيانات المشرف الحالي
 */
function get_current_admin() {
    if (isset($_SESSION['admin_id'])) {
        return [
            'id' => $_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'] ?? '',
            'email' => $_SESSION['admin_email'] ?? '',
            'role' => $_SESSION['admin_role'] ?? 'admin'
        ];
    }
    return null;
}

/**
 * دالة للتحقق من صلاحيات المشرف
 * يمكن تطويرها حسب الحاجة
 */
function has_permission($required_role = 'admin') {
    $current_admin = get_current_admin();
    
    if (!$current_admin) {
        return false;
    }
    
    // نظام صلاحيات بسيط
    $roles_hierarchy = [
        'super_admin' => 3,
        'admin' => 2,
        'editor' => 1
    ];
    
    $current_role_level = $roles_hierarchy[$current_admin['role']] ?? 0;
    $required_role_level = $roles_hierarchy[$required_role] ?? 0;
    
    return $current_role_level >= $required_role_level;
}

/**
 * دالة للتحقق من صلاحية الوصول إلى صفحة معينة
 */
function check_permission($required_role = 'admin') {
    if (!has_permission($required_role)) {
        // يمكن تغيير هذا إلى صفحة خطأ 403
        header('Location: ../index.php?error=permission_denied');
        exit();
    }
}

/**
 * دالة لإضافة رسالة فلاش (Flash Message)
 */
function set_flash_message($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
    ];
}

/**
 * دالة للحصول على رسائل الفلاش وعرضها
 */
function get_flash_messages() {
    if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
        $messages = $_SESSION['flash_messages'];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    return [];
}

/**
 * دالة لعرض رسائل الفلاش
 */
function display_flash_messages() {
    $messages = get_flash_messages();
    
    if (empty($messages)) {
        return '';
    }
    
    $output = '';
    
    foreach ($messages as $message) {
        $alert_class = '';
        
        switch ($message['type']) {
            case 'success':
                $alert_class = 'alert-success';
                $icon = 'check-circle';
                break;
            case 'error':
                $alert_class = 'alert-danger';
                $icon = 'exclamation-circle';
                break;
            case 'warning':
                $alert_class = 'alert-warning';
                $icon = 'exclamation-triangle';
                break;
            case 'info':
                $alert_class = 'alert-info';
                $icon = 'info-circle';
                break;
            default:
                $alert_class = 'alert-secondary';
                $icon = 'info-circle';
        }
        
        $output .= '
        <div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">
            <i class="fas fa-' . $icon . ' me-2"></i>
            ' . htmlspecialchars($message['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
    
    return $output;
}
?>