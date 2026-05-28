<?php
/**
 * API Endpoint: جلب قائمة المدن
 * Method: GET
 * Path: /api/cities/list.php
 * 
 * Query Parameters (اختياري):
 * - featured: 1 لجلب المدن المميزة فقط
 * - limit: عدد المدن المراد جلبها
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

try {
    // معالجة معاملات الاستعلام
    $featured_only = isset($_GET['featured']) && $_GET['featured'] == '1';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
    
    // بناء الاستعلام الأساسي
    $sql = "SELECT 
                c.id, 
                c.name, 
                c.description, 
                c.is_active,
                c.created_at,
                (SELECT COUNT(*) FROM attractions a 
                 INNER JOIN locations l ON a.location_id = l.id 
                 WHERE l.city_id = c.id AND a.is_active = 1) as attractions_count
            FROM cities c 
            WHERE c.is_active = 1";
    
    // إضافة فلتر المدن المميزة إذا طلب
    if ($featured_only) {
        // يمكن إضافة حقل is_featured لجدول المدن في المستقبل
        // حالياً نرجع كل المدن النشطة
    }
    
    // إضافة ترتيب
    $sql .= " ORDER BY c.name ASC";
    
    // إضافة حد إذا كان محدداً
    if ($limit && $limit > 0) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    // تنفيذ الاستعلام
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إذا كانت القائمة فارغة، يمكن إضافة بيانات تجريبية
    if (empty($cities)) {
        // بيانات تجريبية للمدن اليمنية
        $sample_cities = [
            [
                'id' => 1,
                'name' => 'صنعاء',
                'description' => 'عاصمة اليمن وأقدم المدن المأهولة في العالم',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'attractions_count' => 5
            ],
            [
                'id' => 2,
                'name' => 'عدن',
                'description' => 'العاصمة الاقتصادية والمدينة الساحلية الرئيسية',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'attractions_count' => 4
            ],
            [
                'id' => 3,
                'name' => 'تعز',
                'description' => 'مدينة الثقافة والتعليم في اليمن',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'attractions_count' => 3
            ],
            [
                'id' => 4,
                'name' => 'حضرموت',
                'description' => 'أكبر محافظات اليمن وموطن وادي حضرموت الشهير',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'attractions_count' => 6
            ]
        ];
        
        // إرجاع البيانات التجريبية
        json_response($sample_cities, 200, 'تم جلب قائمة المدن (بيانات تجريبية)');
    }
    
    // تحويل النصوص للتأكد من ترميز UTF-8
    foreach ($cities as &$city) {
        if (is_string($city['name'])) {
            $city['name'] = mb_convert_encoding($city['name'], 'UTF-8', 'UTF-8');
        }
        if (is_string($city['description'])) {
            $city['description'] = mb_convert_encoding($city['description'], 'UTF-8', 'UTF-8');
        }
    }
    
    json_response($cities, 200, 'تم جلب قائمة المدن بنجاح');
    
} catch (PDOException $e) {
    error_log("Cities list error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>