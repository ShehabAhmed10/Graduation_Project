<?php
/**
 * ملف الدوال المساعدة العامة
 * يستخدم في كل من API ولوحة التحكم
 */

// منع الوصول المباشر
if (!defined('PROJECT_ROOT')) {
    die('Direct access not permitted');
}

/**
 * تحويل النصوص الخاصة إلى كيانات HTML
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * إعادة توجيه المستخدم إلى صفحة أخرى
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * التحقق إذا كان الطلب هو AJAX
 */
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * إعداد رسالة فلاش (Flash Message)
 */
function set_flash_message($type, $message) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * عرض رسالة الفلاش
 */
function display_flash_message() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        $alert_class = '';
        switch ($message['type']) {
            case 'success':
                $alert_class = 'alert-success';
                break;
            case 'error':
                $alert_class = 'alert-danger';
                break;
            case 'warning':
                $alert_class = 'alert-warning';
                break;
            case 'info':
                $alert_class = 'alert-info';
                break;
        }
        
        return '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">
                    ' . sanitize($message['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
    
    return '';
}

/**
 * تنسيق التاريخ
 */
function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    
    $date_obj = new DateTime($date);
    return $date_obj->format($format);
}

/**
 * إنشاء slug من النص
 */
function create_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

/**
 * إظهار قيمة حقل في النموذج بعد الإرسال
 */
function old($field_name, $default = '') {
    if (isset($_POST[$field_name])) {
        return sanitize($_POST[$field_name]);
    }
    
    return $default;
}

/**
 * التحقق إذا كان الملف صورة
 */
function is_image($file_path) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    return in_array($mime_type, $allowed_types);
}

/**
 * تقليم النص مع إضافة نقاط إذا كان طويلاً
 */
function truncate_text($text, $max_length = 100) {
    if (mb_strlen($text) <= $max_length) {
        return $text;
    }
    
    return mb_substr($text, 0, $max_length) . '...';
}

/**
 * إنشاء رمز عشوائي
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $random_string;
}
?>