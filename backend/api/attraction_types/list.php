<?php
/**
 * API Endpoint: جلب قائمة أنواع المعالم السياحية
 * Method: GET
 * Path: /api/attraction_types/list.php
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 405, 'يجب استخدام طريقة GET');
}

try {
    // بناء الاستعلام لجلب أنواع المعالم
    $sql = "SELECT 
                id, 
                type_name, 
                description, 
                icon_name, 
                marker_color,
                is_active,
                created_at
            FROM attraction_types 
            WHERE is_active = 1 
            ORDER BY type_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إذا كانت القائمة فارغة، نضيف أنواعاً افتراضية
    if (empty($types)) {
        $sample_types = [
            [
                'id' => 1,
                'type_name' => 'تاريخي',
                'description' => 'معالم تاريخية وأثرية',
                'icon_name' => 'history',
                'marker_color' => '#8B4513',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'type_name' => 'ديني',
                'description' => 'مساجد ومزارات دينية',
                'icon_name' => 'mosque',
                'marker_color' => '#006400',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'type_name' => 'طبيعي',
                'description' => 'مناظر طبيعية وجبال وسواحل',
                'icon_name' => 'mountain',
                'marker_color' => '#228B22',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'type_name' => 'ثقافي',
                'description' => 'متاحف ومراكز ثقافية',
                'icon_name' => 'museum',
                'marker_color' => '#8A2BE2',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'type_name' => 'ترفيهي',
                'description' => 'منتزهات وأماكن ترفيهية',
                'icon_name' => 'park',
                'marker_color' => '#FF8C00',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        // يمكن إدراج هذه الأنواع في قاعدة البيانات هنا
        // ولكن لأغراض الاختبار، نعيدها مباشرة
        json_response($sample_types, 200, 'تم جلب قائمة أنواع المعالم (بيانات تجريبية)');
    }
    
    // ضمان ترميز UTF-8 للنصوص
    foreach ($types as &$type) {
        if (is_string($type['type_name'])) {
            $type['type_name'] = mb_convert_encoding($type['type_name'], 'UTF-8', 'UTF-8');
        }
        if (is_string($type['description'])) {
            $type['description'] = mb_convert_encoding($type['description'], 'UTF-8', 'UTF-8');
        }
    }
    
    json_response($types, 200, 'تم جلب قائمة أنواع المعالم بنجاح');
    
} catch (PDOException $e) {
    error_log("Attraction types list error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>