<?php
/**
 * API Endpoint: جلب تفاصيل معلم سياحي
 * Method: GET
 * Path: /api/attractions/details.php
 * 
 * Query Parameters:
 * - id: معرف المعلم (مطلوب)
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

// التحقق من وجود معرف المعلم
if (!isset($_GET['id']) || empty($_GET['id'])) {
    json_response(null, 400, 'معرف المعلم مطلوب');
}

$attraction_id = intval($_GET['id']);

try {
    $headers = getallheaders();
    $token = null;
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
    $user = $token ? verify_user_token($token) : null;
    $current_user_id = $user['id'] ?? null;
    // جلب المعلومات الأساسية للمعلم
    $sql = "SELECT 
                a.id,
                a.name,
                a.short_description,
                a.description,
                a.main_image_url,
                a.avg_rating,
                a.total_reviews,
                a.is_featured,
                a.is_active,
                a.created_at,
                a.updated_at,
                
                c.id as city_id,
                c.name as city_name,
                c.description as city_description,
                
                at.id as type_id,
                at.type_name,
                at.icon_name,
                at.marker_color,
                
                l.id as location_id,
                l.name as location_name,
                l.street,
                l.latitude,
                l.longitude
            FROM attractions a
            INNER JOIN locations l ON a.location_id = l.id
            INNER JOIN cities c ON l.city_id = c.id
            INNER JOIN attraction_types at ON a.attraction_type_id = at.id
            WHERE a.id = ? AND a.is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$attraction_id]);
    $attraction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$attraction) {
        json_response(null, 404, 'لم يتم العثور على المعلم');
    }
    
    // تحويل النصوص لترميز UTF-8
    $string_fields = ['name', 'short_description', 'description', 'city_name', 
                     'city_description', 'type_name', 'location_name', 'street'];
    foreach ($string_fields as $field) {
        if (isset($attraction[$field]) && is_string($attraction[$field])) {
            $attraction[$field] = mb_convert_encoding($attraction[$field], 'UTF-8', 'UTF-8');
        }
    }
    
    // جلب الصور الإضافية للمعلم
    $images_sql = "SELECT id, image_url, sort_order, created_at 
                   FROM attraction_images 
                   WHERE attraction_id = ? 
                   ORDER BY sort_order ASC, created_at ASC";
    $images_stmt = $pdo->prepare($images_sql);
    $images_stmt->execute([$attraction_id]);
    $images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب آخر 5 تقييمات مقبولة
    $reviews_sql = "SELECT 
                        r.id,
                        r.rating,
                        r.comment,
                        r.status,
                        r.created_at,
                        u.id as user_id,
                        u.full_name as user_name
                    FROM reviews r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE r.attraction_id = ? AND r.status = 'approved'
                    ORDER BY r.created_at DESC
                    LIMIT 5";
    $reviews_stmt = $pdo->prepare($reviews_sql);
    $reviews_stmt->execute([$attraction_id]);
    $reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

    $comments_sql = "SELECT 
                        c.id,
                        c.comment,
                        c.created_at,
                        u.id as user_id,
                        u.full_name as user_name
                    FROM comments c
                    INNER JOIN users u ON c.user_id = u.id
                    WHERE c.attraction_id = ?
                    ORDER BY c.created_at DESC
                    LIMIT 20";
    $comments_stmt = $pdo->prepare($comments_sql);
    $comments_stmt->execute([$attraction_id]);
    $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

    $user_review = null;
    if ($current_user_id) {
        $user_review_stmt = $pdo->prepare("SELECT id, rating, comment, status, created_at
                                           FROM reviews
                                           WHERE user_id = ? AND attraction_id = ?
                                           LIMIT 1");
        $user_review_stmt->execute([$current_user_id, $attraction_id]);
        $user_review = $user_review_stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    // جلب الفنادق القريبة (في نفس المدينة)
    $hotels_sql = "SELECT 
                        h.id,
                        h.name,
                        h.description,
                        h.phone,
                        h.main_image_url,
                        h.stars,
                        l.latitude,
                        l.longitude,
                        ROUND(
                            6371 * ACOS(
                                COS(RADIANS(?)) * COS(RADIANS(l.latitude)) * 
                                COS(RADIANS(l.longitude) - RADIANS(?)) + 
                                SIN(RADIANS(?)) * SIN(RADIANS(l.latitude))
                            ), 2
                        ) as distance_km
                    FROM hotels h
                    INNER JOIN locations l ON h.location_id = l.id
                    WHERE l.city_id = ? AND h.is_active = 1
                    ORDER BY distance_km ASC
                    LIMIT 10";
    $hotels_stmt = $pdo->prepare($hotels_sql);
    $hotels_stmt->execute([
        $attraction['latitude'], 
        $attraction['longitude'], 
        $attraction['latitude'],
        $attraction['city_id']
    ]);
    $hotels = $hotels_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comments as &$comment) {
        if (is_string($comment['comment'])) {
            $comment['comment'] = mb_convert_encoding($comment['comment'], 'UTF-8', 'UTF-8');
        }
    }
    
    // إعداد بيانات الاستجابة
    $response_data = [
        'attraction' => $attraction,
        'images' => $images,
        'reviews' => $reviews,
        'comments' => $comments,
        'user_review' => $user_review,
        'nearby_hotels' => $hotels,
        'metadata' => [
            'images_count' => count($images),
            'reviews_count' => count($reviews),
            'comments_count' => count($comments),
            'hotels_count' => count($hotels)
        ]
    ];
    
    json_response($response_data, 200, 'تم جلب تفاصيل المعلم بنجاح');
    
} catch (PDOException $e) {
    error_log("Attraction details error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>
