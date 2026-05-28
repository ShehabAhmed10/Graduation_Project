<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'مراجعة التقييم - Yemen Tourism';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error','معرف التقييم غير محدد');
    header('Location: ' . admin_url('reviews/index.php'));
    exit();
}

$id = intval($_GET['id']);
$review = db_fetch("SELECT r.*, u.name as user_name, a.name as attraction_name, h.name as hotel_name
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN attractions a ON r.attraction_id = a.id
    LEFT JOIN hotels h ON r.hotel_id = h.id
    WHERE r.id = ?", [$id]);
if (!$review) {
    set_flash_message('error','التقييم غير موجود');
    header('Location: ' . admin_url('reviews/index.php'));
    exit();
}

$images = db_select("SELECT * FROM review_images WHERE review_id = ? ORDER BY id", [$id]);

// إجراءات الموافقة/الرفض
// إمكانية حذف صورة واحدة عن طريق GET
if (isset($_GET['action']) && $_GET['action'] === 'delete_image' && isset($_GET['img_id'])) {
    $img_id = intval($_GET['img_id']);
    $row = db_fetch("SELECT * FROM review_images WHERE id = ? AND review_id = ?", [$img_id, $id]);
    if ($row) {
        require_once __DIR__ . '/../../core/upload.php';
        if (!empty($row['image_url'])) delete_file($row['image_url']);
        db_execute("DELETE FROM review_images WHERE id = ?", [$img_id]);
        set_flash_message('success','تم حذف صورة المراجعة');
    }
    header('Location: ' . admin_url('reviews/moderate.php?id=' . $id));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'approve') {
        db_execute("UPDATE reviews SET is_approved = 1 WHERE id = ?", [$id]);
        db_execute("UPDATE review_images SET is_approved = 1 WHERE review_id = ?", [$id]);
        // إنشاء إشعار للمستخدم
        if (!empty($review['user_id'])) {
            $title = 'تمت الموافقة على تقييمك';
            $body = 'تمت الموافقة على تقييمك المرقم #' . $review['id'] . ' وسيظهر الآن في التطبيق.';
            create_notification($review['user_id'], $title, $body);
        }
        set_flash_message('success','تمت الموافقة على التقييم والصور');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'reject') {
        db_execute("UPDATE reviews SET is_approved = 0 WHERE id = ?", [$id]);
        db_execute("UPDATE review_images SET is_approved = 0 WHERE review_id = ?", [$id]);
        if (!empty($review['user_id'])) {
            $title = 'تم رفض تقييمك';
            $body = 'تم رفض تقييمك المرقم #' . $review['id'] . '. يمكنك التحقق من سياسة النشر وإعادة المحاولة.';
            create_notification($review['user_id'], $title, $body);
        }
        set_flash_message('success','تم رفض التقييم/الصور');
    }
    header('Location: ' . admin_url('reviews/index.php'));
    exit();
}

include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>
        <main class="main-content">
            <div class="container-fluid">
                <?php echo display_flash_messages(); ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">مراجعة التقييم #<?php echo $review['id']; ?></h5>
                        <a href="<?php echo admin_url('reviews/index.php'); ?>" class="btn btn-outline-secondary">رجوع</a>
                    </div>
                    <div class="card-body">
                        <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($review['user_name'] ?? '-'); ?></p>
                        <p><strong>الهدف:</strong> <?php echo htmlspecialchars($review['attraction_name'] ?? $review['hotel_name'] ?? '-'); ?></p>
                        <p><strong>التقييم:</strong> <?php echo (int)$review['rating']; ?></p>
                        <p><strong>نص التقييم:</strong><br><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>

                        <hr>
                        <h6>صور المراجعة</h6>
                        <div class="row mt-2">
                            <?php foreach ($images as $img): $s = $img['image_url']; $d = preg_match('#^https?://#i',$s) ? $s : (defined('UPLOADS_URL')?rtrim(UPLOADS_URL,'/'):rtrim(BASE_URL,'/')).'/'.ltrim($s,'/'); ?>
                                <div class="col-3 mb-3">
                                        <div class="card">
                                            <img src="<?php echo htmlspecialchars($d); ?>" class="card-img-top" style="height:140px;object-fit:cover;">
                                            <div class="card-body p-2 text-center">
                                                <?php if ($img['is_approved']): ?><span class="badge bg-success">موافق</span><?php else: ?><span class="badge bg-secondary">قيد الانتظار</span><?php endif; ?>
                                                <div class="mt-2">
                                                    <a href="<?php echo admin_url('reviews/moderate.php?id=' . $review['id'] . '&action=delete_image&img_id=' . $img['id']); ?>" class="btn btn-sm btn-danger">حذف الصورة</a>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST" class="mt-3">
                            <button name="action" value="approve" class="btn btn-success">الموافقة على التقييم والصور</button>
                            <button name="action" value="reject" class="btn btn-danger">رفض</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
