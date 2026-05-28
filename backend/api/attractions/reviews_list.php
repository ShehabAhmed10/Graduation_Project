<?php
/**
 * API Endpoint: عرض جميع التقييمات للمعلم
 * Method: GET
 * Path: /api/attractions/reviews_list.php
 * 
 * Query Parameters:
 * - attraction_id: معرف المعلم (مطلوب)
 * - status: حالة التقييم (pending/approved/rejected) - اختياري
 * - limit: عدد النتائج - اختياري
 * - page: رقم الصفحة - اختياري
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

// التحقق من وجود معرف المعلم
if (!isset($_GET['attraction_id']) || empty($_GET['attraction_id'])) {
    json_response(null, 400, 'معرف المعلم مطلوب');
}

$attraction_id = intval($_GET['attraction_id']);
$status = isset($_GET['status']) ? $_GET['status'] : 'approved';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// التحقق من صحة حالة التقييم
$allowed_statuses = ['pending', 'approved', 'rejected'];
if (!in_array($status, $allowed_statuses)) {
    json_response(null, 400, 'حالة التقييم غير صالحة');
}

try {
    // التحقق من وجود المعلم
    $attraction_check = $pdo->prepare("SELECT id, name FROM attractions WHERE id = ? AND is_active = 1");
    $attraction_check->execute([$attraction_id]);
    
    if ($attraction_check->rowCount() === 0) {
        json_response(null, 404, 'لم يتم العثور على المعلم');
    }
    
    $attraction = $attraction_check->fetch(PDO::FETCH_ASSOC);
    
    // بناء الاستعلام
    $sql = "SELECT 
                r.id,
                r.rating,
                r.comment,
                r.status,
                r.created_at,
                u.id as user_id,
                u.full_name as user_name,
                u.avatar_url as user_avatar
            FROM reviews r
            INNER JOIN users u ON r.user_id = u.id
            WHERE r.attraction_id = ? AND r.status = ?";
    
    $params = [$attraction_id, $status];
    
    // الحصول على العدد الكلي
    $count_sql = "SELECT COUNT(*) as total FROM ({$sql}) as temp";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // إضافة الترتيب والحد
    $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // تنفيذ الاستعلام
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // حساب متوسط التقييم
    $avg_sql = "SELECT 
                    AVG(rating) as avg_rating,
                    COUNT(*) as total_reviews
                FROM reviews 
                WHERE attraction_id = ? AND status = 'approved'";
    
    $avg_stmt = $pdo->prepare($avg_sql);
    $avg_stmt->execute([$attraction_id]);
    $stats = $avg_stmt->fetch(PDO::FETCH_ASSOC);
    
    // تحويل النصوص لترميز UTF-8
    foreach ($reviews as &$review) {
        if (is_string($review['user_name'])) {
            $review['user_name'] = mb_convert_encoding($review['user_name'], 'UTF-8', 'UTF-8');
        }
        if (is_string($review['comment'])) {
            $review['comment'] = mb_convert_encoding($review['comment'], 'UTF-8', 'UTF-8');
        }
    }
    
    // تحويل اسم المعلم
    $attraction['name'] = mb_convert_encoding($attraction['name'], 'UTF-8', 'UTF-8');
    
    // إعداد بيانات الاستجابة
    $response_data = [
        'attraction' => [
            'id' => $attraction['id'],
            'name' => $attraction['name']
        ],
        'stats' => [
            'avg_rating' => round(floatval($stats['avg_rating']), 2),
            'total_reviews' => intval($stats['total_reviews']),
            'current_status_count' => intval($total_count)
        ],
        'reviews' => $reviews,
        'pagination' => [
            'total' => intval($total_count),
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total_count / $limit)
        ],
        'filters' => [
            'status' => $status
        ]
    ];
    
    json_response($response_data, 200, 'تم جلب التقييمات بنجاح');
    
} catch (PDOException $e) {
    error_log("Reviews list error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>