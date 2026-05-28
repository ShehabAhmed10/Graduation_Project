<?php
/**
 * API Endpoint: تحديد الإشعار كمقروء
 * Method: POST
 * Path: /api/notifications/mark_read.php
 * 
 * JSON Body:
 * - notification_id: معرف الإشعار (مطلوب للتحديد الفردي)
 * - mark_all: true لتحديد الكل كمقروء (بدون معرف محدد)
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 405, 'يجب استخدام طريقة POST');
}

// قراءة بيانات JSON
$input = get_json_input();
if (!$input) {
    json_response(null, 400, 'بيانات غير صحيحة');
}

try {
    // التحقق من مصادقة المستخدم
    $current_user_id = get_current_user_id();
    
    // التحقق من معاملات الإدخال
    $notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : null;
    $mark_all = isset($input['mark_all']) && $input['mark_all'] === true;
    
    if (!$notification_id && !$mark_all) {
        json_response(null, 400, 'يجب تحديد معرف الإشعار أو وضع mark_all كـ true');
    }
    
    if ($notification_id) {
        // تحديث إشعار محدد
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$notification_id, $current_user_id]);
        
        $affected_rows = $stmt->rowCount();
        $message = $affected_rows > 0 
            ? 'تم تحديد الإشعار كمقروء' 
            : 'لم يتم العثور على الإشعار أو ليس لديك صلاحية لتعديله';
    } else {
        // تحديث جميع إشعارات المستخدم
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$current_user_id]);
        
        $affected_rows = $stmt->rowCount();
        $message = "تم تحديد {$affected_rows} إشعار كمقروء";
    }
    
    json_response([
        'affected_rows' => $affected_rows,
        'marked_all' => $mark_all
    ], 200, $message);
    
} catch (PDOException $e) {
    error_log("Mark notification read error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>