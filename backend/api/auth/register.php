<?php
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 405, 'يجب استخدام طريقة POST');
}

$input = get_json_input();
if (!$input) {
    json_response(null, 400, 'بيانات غير صحيحة');
}

// التحقق من الحقول المطلوبة
$required = ['full_name', 'email', 'password'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        json_response(null, 400, "حقل $field مطلوب");
    }
}

$full_name = sanitize_arabic(trim($input['full_name']));
$email = trim($input['email']);
$password = $input['password'];
$phone = isset($input['phone']) ? trim($input['phone']) : null;

// التحقق الأساسي
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(null, 400, 'بريد إلكتروني غير صالح');
}

if (strlen($password) < 6) {
    json_response(null, 400, 'كلمة المرور يجب أن تكون 6 أحرف على الأقل');
}

try {
    // التحقق من وجود البريد مسبقاً
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        json_response(null, 409, 'البريد الإلكتروني مسجل مسبقاً');
    }
    
    // إضافة المستخدم
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, is_active) VALUES (?, ?, ?, ?, 'user', 1)");
    $stmt->execute([$full_name, $email, $phone, $password_hash]);
    
    if ($stmt->rowCount() > 0) {
        $user_id = $pdo->lastInsertId();
        $user = $pdo->query("SELECT id, full_name, email, phone, role, avatar_url, created_at FROM users WHERE id = $user_id")->fetch();
        
        json_response([
            'user' => $user,
            'token' => bin2hex(random_bytes(32))
        ], 201, 'تم إنشاء الحساب بنجاح');
    } else {
        json_response(null, 500, 'فشل إنشاء الحساب');
    }
} catch (PDOException $e) {
    json_response(null, 500, 'خطأ في الخادم: ' . (ENVIRONMENT === 'development' ? $e->getMessage() : ''));
}
?>