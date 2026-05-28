<?php
/**
 * API Endpoint: حذف التقييم
 * Method: POST
 * Path: /api/attractions/delete_review.php
 * 
 * JSON Body:
 * - attraction_id
 */

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    json_response(null, 405, 'يجب استخدام طريقة POST أو DELETE');
}

$input = get_json_input();
if (!$input) {
    json_response(null, 400, 'بيانات غير صحيحة');
}

if (!isset($input['attraction_id'])) {
    json_response(null, 400, 'معرف المعلم مطلوب');
}

$attraction_id = intval($input['attraction_id']);

try {
    $current_user_id = get_current_user_id();

    $review_stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND attraction_id = ? LIMIT 1");
    $review_stmt->execute([$current_user_id, $attraction_id]);
    $review = $review_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        json_response(null, 404, 'لا يوجد تقييم لحذفه');
    }

    $delete_stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $delete_stmt->execute([$review['id']]);

    $stats_stmt = $pdo->prepare(
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
    $stats_stmt->execute([$attraction_id, $attraction_id, $attraction_id]);

    json_response(null, 200, 'تم حذف التقييم بنجاح');
} catch (PDOException $e) {
    error_log("Delete review error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>
