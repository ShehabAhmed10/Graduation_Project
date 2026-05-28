<?php
/**
 * API Endpoint: جلب قائمة إشعارات المستخدم
 * Method: GET
 * Path: /api/notifications/list.php
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

try {
    // التحقق من مصادقة المستخدم
    $current_user_id = get_current_user_id();
    
    // معالجة معاملات الاستعلام
    $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;
    
    // بناء الاستعلام
    $sql = "SELECT 
                id,
                title,
                body,
                is_read,
                created_at
            FROM notifications 
            WHERE user_id = ?";
    
    $params = [$current_user_id];
    
    // فلتر الإشعارات غير المقروءة
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    
    // الحصول على العدد الكلي
    $count_sql = "SELECT COUNT(*) as total FROM ({$sql}) as temp";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // إضافة الترتيب والحد
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // تنفيذ الاستعلام
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تحويل النصوص لترميز UTF-8
    foreach ($notifications as &$notification) {
        if (is_string($notification['title'])) {
            $notification['title'] = mb_convert_encoding($notification['title'], 'UTF-8', 'UTF-8');
        }
        if (is_string($notification['body'])) {
            $notification['body'] = mb_convert_encoding($notification['body'], 'UTF-8', 'UTF-8');
        }
    }
    
    // إعداد بيانات الاستجابة
    $response_data = [
        'notifications' => $notifications,
        'pagination' => [
            'total' => intval($total_count),
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total_count / $limit)
        ],
        'filters' => [
            'unread_only' => $unread_only
        ]
    ];
    
    json_response($response_data, 200, 'تم جلب قائمة الإشعارات بنجاح');
    
} catch (PDOException $e) {
    error_log("Notifications list error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>