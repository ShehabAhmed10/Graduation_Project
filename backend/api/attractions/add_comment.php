<?php
/**
 * API Endpoint: إضافة تعليق
 * Method: POST
 * Path: /api/attractions/add_comment.php
 * 
 * JSON Body:
 * - attraction_id
 * - comment
 */

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 405, 'يجب استخدام طريقة POST');
}

$input = get_json_input();
if (!$input) {
    json_response(null, 400, 'بيانات غير صحيحة');
}

if (!isset($input['attraction_id']) || !isset($input['comment'])) {
    json_response(null, 400, 'معرف المعلم والتعليق مطلوبان');
}

$attraction_id = intval($input['attraction_id']);
$comment = trim($input['comment']);

if ($comment === '') {
    json_response(null, 400, 'التعليق لا يمكن أن يكون فارغاً');
}

if (mb_strlen($comment) > 1000) {
    json_response(null, 400, 'التعليق يجب أن يكون أقل من 1000 حرف');
}

try {
    $current_user_id = get_current_user_id();

    $attraction_check = $pdo->prepare("SELECT id FROM attractions WHERE id = ? AND is_active = 1");
    $attraction_check->execute([$attraction_id]);
    if ($attraction_check->rowCount() === 0) {
        json_response(null, 404, 'لم يتم العثور على المعلم');
    }

    $insert_sql = "INSERT INTO comments (user_id, attraction_id, comment) VALUES (?, ?, ?)";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->execute([$current_user_id, $attraction_id, $comment]);

    if ($insert_stmt->rowCount() > 0) {
        $comment_id = $pdo->lastInsertId();
        $comment_sql = "SELECT 
                            c.id,
                            c.comment,
                            c.created_at,
                            u.id as user_id,
                            u.full_name as user_name
                        FROM comments c
                        INNER JOIN users u ON c.user_id = u.id
                        WHERE c.id = ?";
        $comment_stmt = $pdo->prepare($comment_sql);
        $comment_stmt->execute([$comment_id]);
        $comment_row = $comment_stmt->fetch(PDO::FETCH_ASSOC);

        if ($comment_row && is_string($comment_row['comment'])) {
            $comment_row['comment'] = mb_convert_encoding($comment_row['comment'], 'UTF-8', 'UTF-8');
        }

        json_response(['comment' => $comment_row], 201, 'تم إضافة التعليق بنجاح');
    }

    json_response(null, 500, 'فشل إضافة التعليق');
} catch (PDOException $e) {
    error_log("Add comment error: " . $e->getMessage());
    json_response(null, 500, 'حدث خطأ في الخادم');
}
?>
