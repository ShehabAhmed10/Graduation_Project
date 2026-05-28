<?php
/**
 * API Endpoint: إضافة/إزالة معلم من المفضلة
 * Method: POST
 * Path: /api/attractions/toggle_favorite.php
 * 
 * JSON Body:
 * - attraction_id: معرف المعلم
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 405, 'يجب استخدام طريقة POST');
}

// قراءة بيانات JSON
$input = get_json_input();
if (!$input || !isset($input['attraction_id'])) {
    json_response(null, 400, 'معرف المعلم مطلوب');
}

$attraction_id = intval($input['attraction_id']);

try {
    // التحقق من مصادقة المستخدم
    $current_user_id = get_current_user_id();
    
    // التحقق من وجود المعلم
    $attraction_check = $pdo->prepare("SELECT id FROM attractions WHERE id = ? AND is_active = 1");
    $attraction_check->execute([$attraction_id]);
    
    if ($attraction_check->rowCount() === 0) {
        json_response(null, 404, 'لم يتم العثور على المعلم');
    }
    
    // التحقق إذا كان المعلم موجوداً في المفضلة
    $check_favorite = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND attraction_id = ?");
    $check_favorite->execute([$current_user_id, $attraction_id]);
    
    if ($check_favorite->rowCount() > 0) {
        // إزالة من المفضلة
        $delete_sql = "DELETE FROM favorites WHERE user_id = ? AND attraction_id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$current_user_id, $attraction_id]);
        
        json_response([
            'action' => 'removed',
            'is_favorited' => false
        ], 200, 'تمت إزالة المعلم من المفضلة');
    } else {
        // إضافة إلى المفضلة
        $insert_sql = "INSERT INTO favorites (user_id, attraction_id) VALUES (?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$current_user_id, $attraction_id]);
        
        json_response([
            'action' => 'added',
            'is_favorited' => true,
            'favorite_id' => $pdo->lastInsertId()
        ], 201, 'تمت إضافة المعلم إلى المفضلة');
    }
    
} catch (PDOException $e) {
    error_log("Toggle favorite error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>