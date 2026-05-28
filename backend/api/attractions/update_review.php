<?php
/**
 * API Endpoint: تحديث التقييم
 * Method: POST
 * Path: /api/attractions/update_review.php
 * 
 * JSON Body:
 * - attraction_id
 * - rating
 * - comment (optional)
 */

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_response(null, 405, 'يجب استخدام طريقة POST أو PUT');
}

$input = get_json_input();
if (!$input) {
    json_response(null, 400, 'بيانات غير صحيحة');
}

if (!isset($input['attraction_id']) || !isset($input['rating'])) {
    json_response(null, 400, 'معرف المعلم والتقييم مطلوبان');
}

$attraction_id = intval($input['attraction_id']);
$rating = intval($input['rating']);
$comment = isset($input['comment']) ? trim($input['comment']) : null;

if ($rating < 1 || $rating > 5) {
    json_response(null, 400, 'التقييم يجب أن يكون بين 1 و 5');
}

if ($comment !== null && mb_strlen($comment) > 1000) {
    json_response(null, 400, 'التعليق يجب أن يكون أقل من 1000 حرف');
}

try {
    $current_user_id = get_current_user_id();

    $review_stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND attraction_id = ? LIMIT 1");
    $review_stmt->execute([$current_user_id, $attraction_id]);
    $review = $review_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        json_response(null, 404, 'لا يوجد تقييم سابق لتحديثه');
    }

    $update_sql = "UPDATE reviews SET rating = ?, comment = ? WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$rating, $comment, $review['id']]);

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

    json_response(null, 200, 'تم تحديث التقييم بنجاح');
} catch (PDOException $e) {
    error_log("Update review error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>
