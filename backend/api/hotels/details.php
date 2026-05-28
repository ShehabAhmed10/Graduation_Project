<?php
/**
 * API Endpoint: جلب تفاصيل الفندق
 * Method: GET
 * Path: /api/hotels/details.php
 * 
 * Query Parameters:
 * - id: معرف الفندق (مطلوب)
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

// التحقق من وجود معرف الفندق
if (!isset($_GET['id']) || empty($_GET['id'])) {
    json_response(null, 400, 'معرف الفندق مطلوب');
}

$hotel_id = intval($_GET['id']);

try {
    // بناء الاستعلام لجلب تفاصيل الفندق
    $sql = "SELECT 
                h.id,
                h.name,
                h.description,
                h.phone,
                h.contact_email,
                h.whatsapp,
                h.website_url,
                h.main_image_url,
                h.stars,
                h.is_active,
                h.created_at,
                h.updated_at,
                
                l.id as location_id,
                l.name as location_name,
                l.street,
                l.latitude,
                l.longitude,
                
                c.id as city_id,
                c.name as city_name,
                c.description as city_description
            FROM hotels h
            INNER JOIN locations l ON h.location_id = l.id
            INNER JOIN cities c ON l.city_id = c.id
            WHERE h.id = ? AND h.is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hotel) {
        json_response(null, 404, 'لم يتم العثور على الفندق');
    }
    
    // جلب صور الفندق
    $images_sql = "SELECT id, image_url, sort_order, created_at 
                   FROM hotel_images 
                   WHERE hotel_id = ? 
                   ORDER BY sort_order ASC, created_at ASC";
    $images_stmt = $pdo->prepare($images_sql);
    $images_stmt->execute([$hotel_id]);
    $images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب المعالم القريبة (في نفس المدينة)
    $attractions_sql = "SELECT 
                            a.id,
                            a.name,
                            a.short_description,
                            a.main_image_url,
                            a.avg_rating,
                            l.latitude,
                            l.longitude,
                            ROUND(
                                6371 * ACOS(
                                    COS(RADIANS(?)) * COS(RADIANS(l.latitude)) * 
                                    COS(RADIANS(l.longitude) - RADIANS(?)) + 
                                    SIN(RADIANS(?)) * SIN(RADIANS(l.latitude))
                                ), 2
                            ) as distance_km
                        FROM attractions a
                        INNER JOIN locations l ON a.location_id = l.id
                        WHERE l.city_id = ? AND a.is_active = 1
                        ORDER BY distance_km ASC
                        LIMIT 5";
    $attractions_stmt = $pdo->prepare($attractions_sql);
    $attractions_stmt->execute([
        $hotel['latitude'], 
        $hotel['longitude'], 
        $hotel['latitude'],
        $hotel['city_id']
    ]);
    $nearby_attractions = $attractions_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تحويل النصوص لترميز UTF-8
    $string_fields = ['name', 'description', 'location_name', 'street', 'city_name', 'city_description'];
    foreach ($string_fields as $field) {
        if (isset($hotel[$field]) && is_string($hotel[$field])) {
            $hotel[$field] = mb_convert_encoding($hotel[$field], 'UTF-8', 'UTF-8');
        }
    }
    
    // تحويل أسماء المعالم القريبة
    foreach ($nearby_attractions as &$attraction) {
        if (is_string($attraction['name'])) {
            $attraction['name'] = mb_convert_encoding($attraction['name'], 'UTF-8', 'UTF-8');
        }
        if (is_string($attraction['short_description'])) {
            $attraction['short_description'] = mb_convert_encoding($attraction['short_description'], 'UTF-8', 'UTF-8');
        }
    }
    
    // إعداد بيانات الاستجابة
    $response_data = [
        'hotel' => $hotel,
        'images' => $images,
        'nearby_attractions' => $nearby_attractions,
        'metadata' => [
            'images_count' => count($images),
            'attractions_count' => count($nearby_attractions)
        ]
    ];
    
    json_response($response_data, 200, 'تم جلب تفاصيل الفندق بنجاح');
    
} catch (PDOException $e) {
    error_log("Hotel details error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>