<?php
/**
 * API Endpoint: جلب قائمة المعالم السياحية
 * Method: GET
 * Path: /api/attractions/list.php
 * 
 * Query Parameters (اختياري):
 * - city_id: فلترة حسب المدينة
 * - type_id: فلترة حسب النوع
 * - search: بحث بالاسم
 * - featured: 1 للمعالم المميزة فقط
 * - limit: عدد النتائج
 * - page: رقم الصفحة
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

try {
    // جمع معاملات الفلترة
    $city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : null;
    $type_id = isset($_GET['type_id']) ? intval($_GET['type_id']) : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $featured_only = isset($_GET['featured']) && $_GET['featured'] == '1';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;
    
    // بناء الاستعلام الأساسي
    $sql = "SELECT 
                a.id,
                a.name,
                a.short_description,
                a.main_image_url,
                a.avg_rating,
                a.total_reviews,
                a.is_featured,
                a.is_active,
                a.created_at,
                
                c.id as city_id,
                c.name as city_name,
                
                at.id as type_id,
                at.type_name,
                
                l.latitude,
                l.longitude
            FROM attractions a
            INNER JOIN locations l ON a.location_id = l.id
            INNER JOIN cities c ON l.city_id = c.id
            INNER JOIN attraction_types at ON a.attraction_type_id = at.id
            WHERE a.is_active = 1";
    
    $params = [];
    
    // تطبيق الفلاتر
    if ($city_id) {
        $sql .= " AND c.id = ?";
        $params[] = $city_id;
    }
    
    if ($type_id) {
        $sql .= " AND at.id = ?";
        $params[] = $type_id;
    }
    
    if ($search) {
        $sql .= " AND (a.name LIKE ? OR a.short_description LIKE ?)";
        $search_term = "%{$search}%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($featured_only) {
        $sql .= " AND a.is_featured = 1";
    }
    
    // الحصول على العدد الكلي للنتائج
    $count_sql = "SELECT COUNT(*) as total FROM ({$sql}) as temp";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // إضافة الترتيب والحد
    // إذا كان الفلتر للمعالم المميزة فقط، نرتب حسب الترتيب المخصص
    if ($featured_only) {
        $sql .= " ORDER BY a.featured_order ASC";
    } else {
        // الترتيب الافتراضي: المميزة أولاً (حسب ترتيبها)، ثم البقية حسب التقييم
        $sql .= " ORDER BY a.is_featured DESC, a.featured_order ASC, a.avg_rating DESC, a.name ASC";
    }
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // تنفيذ الاستعلام
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $attractions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تحويل النصوص لترميز UTF-8
    foreach ($attractions as &$attraction) {
        $string_fields = ['name', 'short_description', 'city_name', 'type_name'];
        foreach ($string_fields as $field) {
            if (isset($attraction[$field]) && is_string($attraction[$field])) {
                $attraction[$field] = mb_convert_encoding($attraction[$field], 'UTF-8', 'UTF-8');
            }
        }
    }
    
    // إعداد بيانات الاستجابة
    $response_data = [
        'attractions' => $attractions,
        'pagination' => [
            'total' => intval($total_count),
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total_count / $limit)
        ],
        'filters' => [
            'city_id' => $city_id,
            'type_id' => $type_id,
            'search' => $search,
            'featured_only' => $featured_only
        ]
    ];
    
    json_response($response_data, 200, 'تم جلب قائمة المعالم بنجاح');
    
} catch (PDOException $e) {
    error_log("Attractions list error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>