<?php
/**
 * API Endpoint: تسجيل دخول المستخدم
 */

require_once __DIR__ . '/../helpers.php';

// السماح فقط بطريقة POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 405, 'طريقة غير مسموحة');
}

// قراءة بيانات JSON
$input = get_json_input();

// التحقق من الحقول المطلوبة
if (empty($input['email']) || empty($input['password'])) {
    json_response(null, 400, 'البريد الإلكتروني وكلمة المرور مطلوبان');
}

$email = trim($input['email']);
$password = $input['password'];

try {
    global $pdo;
    
    // البحث عن المستخدم
    $sql = "SELECT id, full_name, email, phone, password_hash, role, avatar_url, is_active, created_at 
            FROM users 
            WHERE email = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // التحقق من وجود المستخدم
    if (!$user) {
        json_response(null, 401, 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }
    
    // التحقق من حالة الحساب
    if ($user['is_active'] != 1) {
        json_response(null, 403, 'حسابك غير مفعل');
    }
    
    // التحقق من صحة كلمة المرور
    if (!password_verify($password, $user['password_hash'])) {
        json_response(null, 401, 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }
    
    // إزالة كلمة المرور المشفرة
    unset($user['password_hash']);
    
    // إنشاء رمز API
    $api_token = bin2hex(random_bytes(32));
    
    // تحديث آخر وقت دخول
    $update_sql = "UPDATE users SET updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$user['id']]);
    
    json_response([
        'user' => $user,
        'token' => $api_token
    ], 200, 'تم تسجيل الدخول بنجاح');
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>