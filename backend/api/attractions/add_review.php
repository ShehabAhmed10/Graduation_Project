<?php
/**
 * API Endpoint: إضافة تقييم جديد للمعلم
 * Method: POST
 * Path: /api/attractions/add_review.php
 * 
 * JSON Body:
 * - attraction_id: معرف المعلم
 * - rating: التقييم (1-5)
 * - comment: التعليق (اختياري)
 */

require_once __DIR__ . '/../helpers.php';

// التحقق من أن الطريقة POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 405, 'يجب استخدام طريقة POST');
}

// قراءة بيانات JSON
$input = get_json_input();
if (!$input) {
    json_response(null, 400, 'بيانات غير صحيحة');
}

// التحقق من الحقول المطلوبة
if (!isset($input['attraction_id']) || !isset($input['rating'])) {
    json_response(null, 400, 'معرف المعلم والتقييم مطلوبان');
}

$attraction_id = intval($input['attraction_id']);
$rating = intval($input['rating']);
$comment = isset($input['comment']) ? trim($input['comment']) : null;

// التحقق من صحة التقييم
if ($rating < 1 || $rating > 5) {
    json_response(null, 400, 'التقييم يجب أن يكون بين 1 و 5');
}

// التحقق من طول التعليق
if ($comment && mb_strlen($comment) > 1000) {
    json_response(null, 400, 'التعليق يجب أن يكون أقل من 1000 حرف');
}

try {
    // التحقق من مصادقة المستخدم
    $current_user_id = get_current_user_id();
    
    // التحقق من وجود المعلم
    $attraction_check = $pdo->prepare("SELECT id FROM attractions WHERE id = ? AND is_active = 1");
    $attraction_check->execute([$attraction_id]);
    
    if ($attraction_check->rowCount() === 0) {
        json_response(null, 404, 'لم يتم العثور على المعلم');
    }
    
    // التحقق إذا كان المستخدم قد قيم هذا المعلم مسبقاً
    $existing_review = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND attraction_id = ?");
    $existing_review->execute([$current_user_id, $attraction_id]);
    
    if ($existing_review->rowCount() > 0) {
        json_response(null, 409, 'لقد قيمت هذا المعلم مسبقاً');
    }
    
    // إضافة التقييم
    $insert_sql = "INSERT INTO reviews (user_id, attraction_id, rating, comment, status) VALUES (?, ?, ?, ?, 'approved')";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->execute([$current_user_id, $attraction_id, $rating, $comment]);
    
    if ($insert_stmt->rowCount() > 0) {
        $review_id = $pdo->lastInsertId();

        // Recalculate attraction rating stats after inserting an approved review.
        $update_stmt = $pdo->prepare(
            "UPDATE attractions
             SET avg_rating = (
                    SELECT COALESCE(AVG(rating), 0)
                    FROM reviews
                    WHERE attraction_id = ? AND status = 'approved'
                ),
                total_reviews = (
                    SELECT COUNT(*)
                    FROM reviews
                    WHERE attraction_id = ? AND status = 'approved'
                )
             WHERE id = ?"
        );
        $update_stmt->execute([$attraction_id, $attraction_id, $attraction_id]);
        
        // جلب بيانات التقييم المضاف
        $review_sql = "SELECT 
                        r.id,
                        r.rating,
                        r.comment,
                        r.status,
                        r.created_at,
                        u.full_name as user_name
                    FROM reviews r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE r.id = ?";
        
        $review_stmt = $pdo->prepare($review_sql);
        $review_stmt->execute([$review_id]);
        $review = $review_stmt->fetch(PDO::FETCH_ASSOC);
        
        // تحويل النصوص لترميز UTF-8
        if ($review['comment']) {
            $review['comment'] = mb_convert_encoding($review['comment'], 'UTF-8', 'UTF-8');
        }
        
        json_response([
            'review' => $review,
            'message' => 'تم إضافة التقييم بنجاح. سيتم عرضه بعد المراجعة.'
        ], 201, 'تم إضافة التقييم بنجاح');
    } else {
        json_response(null, 500, 'فشل إضافة التقييم');
    }
    
} catch (PDOException $e) {
    error_log("Add review error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>
