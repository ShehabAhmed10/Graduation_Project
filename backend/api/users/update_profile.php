<?php
/**
 * API Endpoint: تحديث بيانات الملف الشخصي للمستخدم
 * Method: PUT أو POST
 * Path: /api/users/update_profile.php
 * 
 * JSON Body (اختياري):
 * - full_name: الاسم الكامل
 * - phone: رقم الهاتف
 * - current_password: كلمة المرور الحالية (للتأكيد عند تغيير كلمة المرور)
 * - new_password: كلمة المرور الجديدة
 * - confirm_password: تأكيد كلمة المرور الجديدة
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة POST أو PUT
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    json_response(null, 405, 'يجب استخدام طريقة POST أو PUT');
}

// قراءة بيانات JSON
$input = get_json_input();
if (!$input) {
    json_response(null, 400, 'بيانات غير صحيحة');
}

try {
    // التحقق من مصادقة المستخدم
    $current_user = require_auth();
    $user_id = $current_user['id'];
    
    // التحقق من البيانات المراد تحديثها
    $updates = [];
    $params = [];
    
    // تحديث الاسم الكامل
    if (isset($input['full_name']) && !empty($input['full_name'])) {
        $full_name = mb_convert_encoding(trim($input['full_name']), 'UTF-8', 'UTF-8');
        if (mb_strlen($full_name) >= 2 && mb_strlen($full_name) <= 150) {
            $updates[] = "full_name = ?";
            $params[] = $full_name;
        } else {
            json_response(null, 400, 'الاسم يجب أن يكون بين 2 و 150 حرفاً');
        }
    }
    
    // تحديث رقم الهاتف
    if (isset($input['phone'])) {
        $phone = trim($input['phone']);
        if (empty($phone)) {
            $updates[] = "phone = NULL";
        } else {
            $updates[] = "phone = ?";
            $params[] = $phone;
        }
    }
    
    // تغيير كلمة المرور (إذا طلب)
    if (isset($input['new_password']) && !empty($input['new_password'])) {
        // التحقق من كلمة المرور الحالية
        if (!isset($input['current_password']) || empty($input['current_password'])) {
            json_response(null, 400, 'كلمة المرور الحالية مطلوبة لتغيير كلمة المرور');
        }
        
        if (!isset($input['confirm_password']) || empty($input['confirm_password'])) {
            json_response(null, 400, 'تأكيد كلمة المرور مطلوب');
        }
        
        // التحقق من تطابق كلمتي المرور الجديدة
        if ($input['new_password'] !== $input['confirm_password']) {
            json_response(null, 400, 'كلمتا المرور الجديدتين غير متطابقتين');
        }
        
        // التحقق من قوة كلمة المرور الجديدة
        if (strlen($input['new_password']) < 6) {
            json_response(null, 400, 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل');
        }
        
        // جلب كلمة المرور الحالية من قاعدة البيانات
        $password_check_sql = "SELECT password_hash FROM users WHERE id = ?";
        $password_stmt = $pdo->prepare($password_check_sql);
        $password_stmt->execute([$user_id]);
        $user_data = $password_stmt->fetch(PDO::FETCH_ASSOC);
        
        // التحقق من صحة كلمة المرور الحالية
        if (!password_verify($input['current_password'], $user_data['password_hash'])) {
            json_response(null, 401, 'كلمة المرور الحالية غير صحيحة');
        }
        
        // تشفير كلمة المرور الجديدة
        $new_password_hash = password_hash($input['new_password'], PASSWORD_DEFAULT);
        $updates[] = "password_hash = ?";
        $params[] = $new_password_hash;
    }
    
    // إذا لم توجد تحديثات
    if (empty($updates)) {
        json_response(null, 400, 'لم يتم تحديد بيانات للتحديث');
    }
    
    // بناء وتنفيذ استعلام التحديث
    $sql = "UPDATE users SET " . implode(", ", $updates) . ", updated_at = NOW() WHERE id = ?";
    $params[] = $user_id;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // جلب بيانات المستخدم المحدثة
    $user_sql = "SELECT id, full_name, email, phone, role, avatar_url, is_active, created_at, updated_at 
                 FROM users 
                 WHERE id = ?";
    $user_stmt = $pdo->prepare($user_sql);
    $user_stmt->execute([$user_id]);
    $updated_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    // تحويل النصوص لترميز UTF-8
    if (is_string($updated_user['full_name'])) {
        $updated_user['full_name'] = mb_convert_encoding($updated_user['full_name'], 'UTF-8', 'UTF-8');
    }
    
    json_response([
        'user' => $updated_user,
        'updated_fields' => count($updates)
    ], 200, 'تم تحديث البيانات بنجاح');
    
} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>