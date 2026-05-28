<?php
/**
 * API Endpoint: جلب بيانات الملف الشخصي للمستخدم
 * Method: GET
 * Path: /api/users/profile.php
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

try {
    // التحقق من مصادقة المستخدم
    $user = require_auth();
    
    // جلب إحصائيات المستخدم
    $favorites_count_sql = "SELECT COUNT(*) as count FROM favorites WHERE user_id = ?";
    $favorites_stmt = $pdo->prepare($favorites_count_sql);
    $favorites_stmt->execute([$user['id']]);
    $favorites_count = $favorites_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $reviews_count_sql = "SELECT COUNT(*) as count FROM reviews WHERE user_id = ?";
    $reviews_stmt = $pdo->prepare($reviews_count_sql);
    $reviews_stmt->execute([$user['id']]);
    $reviews_count = $reviews_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // تحويل النصوص لترميز UTF-8
    if (is_string($user['full_name'])) {
        $user['full_name'] = mb_convert_encoding($user['full_name'], 'UTF-8', 'UTF-8');
    }
    
    // إعداد بيانات الاستجابة
    $response_data = [
        'user' => $user,
        'stats' => [
            'favorites_count' => intval($favorites_count),
            'reviews_count' => intval($reviews_count)
        ]
    ];
    
    json_response($response_data, 200, 'تم جلب بيانات الملف الشخصي بنجاح');
    
} catch (PDOException $e) {
    error_log("User profile error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>