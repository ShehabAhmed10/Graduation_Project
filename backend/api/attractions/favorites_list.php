<?php
/**
 * API Endpoint: عرض قائمة المعالم المفضلة للمستخدم
 * Method: GET
 * Path: /api/attractions/favorites_list.php
 * 
 * Headers:
 * - Authorization: Bearer {token} أو ?token={token}
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

try {
    // التحقق من مصادقة المستخدم
    $current_user_id = get_current_user_id();
    
    // جلب قائمة المفضلة للمستخدم
    $sql = "SELECT 
                f.id as favorite_id,
                f.created_at as favorited_at,
                
                a.id,
                a.name,
                a.short_description,
                a.main_image_url,
                a.avg_rating,
                a.total_reviews,
                a.is_featured,
                
                c.name as city_name,
                at.type_name
            FROM favorites f
            INNER JOIN attractions a ON f.attraction_id = a.id
            INNER JOIN locations l ON a.location_id = l.id
            INNER JOIN cities c ON l.city_id = c.id
            INNER JOIN attraction_types at ON a.attraction_type_id = at.id
            WHERE f.user_id = ? AND a.is_active = 1
            ORDER BY f.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_user_id]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تحويل النصوص لترميز UTF-8
    foreach ($favorites as &$favorite) {
        if (is_string($favorite['name'])) {
            $favorite['name'] = mb_convert_encoding($favorite['name'], 'UTF-8', 'UTF-8');
        }
        if (is_string($favorite['city_name'])) {
            $favorite['city_name'] = mb_convert_encoding($favorite['city_name'], 'UTF-8', 'UTF-8');
        }
        if (is_string($favorite['type_name'])) {
            $favorite['type_name'] = mb_convert_encoding($favorite['type_name'], 'UTF-8', 'UTF-8');
        }
    }
    
    json_response([
        'favorites' => $favorites,
        'total' => count($favorites)
    ], 200, 'تم جلب قائمة المفضلة بنجاح');
    
} catch (PDOException $e) {
    error_log("Favorites list error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>