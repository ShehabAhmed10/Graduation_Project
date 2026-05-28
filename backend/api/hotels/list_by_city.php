<?php
/**
 * API Endpoint: جلب قائمة الفنادق حسب المدينة
 * Method: GET
 * Path: /api/hotels/list_by_city.php
 * 
 * Query Parameters:
 * - city_id: معرف المدينة (مطلوب)
 * - min_stars: الحد الأدنى للنجوم (1-5) - اختياري
 * - limit: عدد النتائج - اختياري
 * - page: رقم الصفحة - اختياري
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

// التحقق من وجود معرف المدينة
if (!isset($_GET['city_id']) || empty($_GET['city_id'])) {
    json_response(null, 400, 'معرف المدينة مطلوب');
}

$city_id = intval($_GET['city_id']);
$min_stars = isset($_GET['min_stars']) ? intval($_GET['min_stars']) : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// التحقق من صحة الحد الأدنى للنجوم
if ($min_stars && ($min_stars < 1 || $min_stars > 5)) {
    json_response(null, 400, 'عدد النجوم يجب أن يكون بين 1 و 5');
}

try {
    // التحقق من وجود المدينة
    $city_check = $pdo->prepare("SELECT id, name FROM cities WHERE id = ? AND is_active = 1");
    $city_check->execute([$city_id]);
    
    if ($city_check->rowCount() === 0) {
        json_response(null, 404, 'لم يتم العثور على المدينة');
    }
    
    $city = $city_check->fetch(PDO::FETCH_ASSOC);
    
    // بناء الاستعلام
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
                
                (SELECT COUNT(*) FROM hotel_images hi WHERE hi.hotel_id = h.id) as images_count
            FROM hotels h
            INNER JOIN locations l ON h.location_id = l.id
            WHERE l.city_id = ? AND h.is_active = 1";
    
    $params = [$city_id];
    
    // إضافة فلتر النجوم إذا كان محدداً
    if ($min_stars) {
        $sql .= " AND h.stars >= ?";
        $params[] = $min_stars;
    }
    
    // الحصول على العدد الكلي
    $count_sql = "SELECT COUNT(*) as total FROM ({$sql}) as temp";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // إضافة الترتيب والحد
    $sql .= " ORDER BY h.stars DESC, h.name ASC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // تنفيذ الاستعلام
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تحويل النصوص لترميز UTF-8
    foreach ($hotels as &$hotel) {
        $string_fields = ['name', 'description', 'location_name', 'street'];
        foreach ($string_fields as $field) {
            if (isset($hotel[$field]) && is_string($hotel[$field])) {
                $hotel[$field] = mb_convert_encoding($hotel[$field], 'UTF-8', 'UTF-8');
            }
        }
    }
    
    // تحويل اسم المدينة
    $city['name'] = mb_convert_encoding($city['name'], 'UTF-8', 'UTF-8');
    
    // إعداد بيانات الاستجابة
    $response_data = [
        'city' => $city,
        'hotels' => $hotels,
        'pagination' => [
            'total' => intval($total_count),
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total_count / $limit)
        ],
        'filters' => [
            'min_stars' => $min_stars
        ]
    ];
    
    json_response($response_data, 200, 'تم جلب قائمة الفنادق بنجاح');
    
} catch (PDOException $e) {
    error_log("Hotels list by city error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>