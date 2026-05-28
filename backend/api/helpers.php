<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * دالة خاصة لتنقية النصوص العربية
 */
function sanitize_arabic($text) {
    if (empty($text)) return $text;
    
    // تحويل الترميز إلى UTF-8 إذا لم يكن
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    }
    
    // إزالة المسافات الزائدة
    $text = trim($text);
    
    // إزالة الأحرف الخاصة الخطيرة مع الحفاظ على العربية
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return $text;
}

function json_response($data = null, $status_code = 200, $message = '') {
    http_response_code($status_code);
    echo json_encode([
        'success' => $status_code >= 200 && $status_code < 300,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function get_json_input() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

function verify_user_token($token) {
    global $pdo;
    
    if (empty($token)) {
        return null;
    }
    
    try {
        // في نظام حقيقي، يجب استخدام JWT أو تخزين التوكنات في قاعدة بيانات
        // هنا نستخدم طريقة مبسطة للتحقق من وجود المستخدم
        
        // إذا كان التوكن يبدأ بـ "user_" فهو معرف المستخدم
        if (strpos($token, 'user_') === 0) {
            $user_id = intval(substr($token, 5));
            
            $sql = "SELECT id, full_name, email, phone, role, avatar_url, is_active 
                    FROM users 
                    WHERE id = ? AND is_active = 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
        
    } catch (PDOException $e) {
        error_log("Token verification error: " . $e->getMessage());
        return null;
    }
}

/**
 * طلب المصادقة (للنقاط التي تتطلب تسجيل دخول)
 */
function require_auth() {
    $headers = getallheaders();
    $token = null;
    
    // البحث عن التوكن في الرؤوس
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            if (strpos($value, 'Bearer ') === 0) {
                $token = substr($value, 7);
            } else {
                $token = $value;
            }
            break;
        }
    }
    
    // أو البحث في GET/POST
    if (!$token && isset($_GET['token'])) {
        $token = $_GET['token'];
    }
    
    if (!$token && isset($_POST['token'])) {
        $token = $_POST['token'];
    }
    
    // التحقق من التوكن
    $user = verify_user_token($token);
    
    if (!$user) {
        json_response(null, 401, 'يجب تسجيل الدخول للوصول إلى هذه الخدمة');
    }
    
    return $user;
}

/**
 * دالة مساعدة للحصول على معرف المستخدم من التوكن
 */
function get_current_user_id() {
    $user = require_auth();
    return $user['id'] ?? null;
}

?>