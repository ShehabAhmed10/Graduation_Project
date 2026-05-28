<?php
/**
 * إدارة صور المعلم (رفع / حذف / عرض)
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'صور المعلم - Yemen Tourism';

// استلام معرف المعلم (يدعم الحقلين `id` أو `attraction_id`)
$attraction_id = null;
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $attraction_id = intval($_GET['id']);
} elseif (isset($_GET['attraction_id']) && $_GET['attraction_id'] !== '') {
    $attraction_id = intval($_GET['attraction_id']);
}

if (!$attraction_id) {
    set_flash_message('error', 'معرف المعلم غير محدد');
    header('Location: ' . admin_url('attractions/index.php'));
    exit();
}

$attraction = db_fetch("SELECT id, name FROM attractions WHERE id = ?", [$attraction_id]);
if (!$attraction) {
    set_flash_message('error', 'لم يتم العثور على المعلم');
    header('Location: ' . admin_url('attractions/index.php'));
    exit();
}

$errors = [];
$success = [];

// حذف صورة
if (isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['image_id'])) {
    $img_id = intval($_POST['image_id']);
    // محاولة الحصول على المسار للحذف من الديسك إذا أمكن
    $img = db_fetch("SELECT * FROM attraction_images WHERE id = ? AND attraction_id = ?", [$img_id, $attraction_id]);
    if ($img) {
        // حاول حذف الملف من القرص إن أمكن
        $stored = $img['image_url'];
        // إذا كان مساراً نسبياً في DB، ابنه على UPLOADS_PATH
        $local_path = null;
        if (preg_match('#^https?://#i', $stored)) {
            // تحويل URL إلى مسار محلي إن كان داخل UPLOADS_URL
            if (defined('UPLOADS_URL') && strpos($stored, rtrim(UPLOADS_URL, '/')) === 0 && defined('UPLOADS_PATH')) {
                $relative = ltrim(substr($stored, strlen(rtrim(UPLOADS_URL, '/'))), '/');
                $local_path = rtrim(UPLOADS_PATH, '/') . '/' . $relative;
            }
        } else {
            // تخزين نسبي أو مسار داخلي
            if (defined('UPLOADS_PATH')) {
                $local_path = rtrim(UPLOADS_PATH, '/') . '/' . ltrim($stored, '/');
            } elseif (defined('PROJECT_ROOT')) {
                $local_path = rtrim(PROJECT_ROOT, '/') . '/' . ltrim($stored, '/');
            }
        }

        if ($local_path && file_exists($local_path) && is_file($local_path)) {
            @unlink($local_path);
        }

        $deleted = db_execute("DELETE FROM attraction_images WHERE id = ?", [$img_id]);
        if ($deleted !== false) {
            $success[] = 'تم حذف الصورة';
        } else {
            $errors[] = 'فشل حذف السجل من قاعدة البيانات';
        }
    } else {
        $errors[] = 'الصورة غير موجودة';
    }
    header('Location: ' . admin_url('attractions/images.php?id=' . $attraction_id));
    exit();
}

// رفع صور (دعم رفع متعدد)
if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    require_once __DIR__ . '/../../core/upload.php';
    $uploaded_count = 0;
    $file_errors = [];

    $files = reformat_files_array($_FILES['images']);
    foreach ($files as $f) {
        if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
        $res = upload_attraction_image($f);
        if (!empty($res) && isset($res['success']) && $res['success']) {
            // store relative path in DB for portability
            $relative = $res['relative_path'] ?? ltrim($res['url'] ?? '', '/');
            $ins = db_execute("INSERT INTO attraction_images (attraction_id, image_url, created_at) VALUES (?, ?, NOW())", [$attraction_id, $relative]);
            if ($ins !== false) {
                $uploaded_count++;
            }
        } else {
            $file_errors[] = isset($res['errors']) ? implode(', ', $res['errors']) : 'خطأ في الرفع';
        }
    }

    if ($uploaded_count > 0) {
        set_flash_message('success', "تم رفع $uploaded_count صورة");
    }
    if (!empty($file_errors)) {
        set_flash_message('error', implode(' | ', $file_errors));
    }
    header('Location: ' . admin_url('attractions/images.php?id=' . $attraction_id));
    exit();
}

// جلب الصور الحالية
    $images = db_select("SELECT * FROM attraction_images WHERE attraction_id = ? ORDER BY id DESC", [$attraction_id]);

include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>
        <main class="main-content">
            <div class="container-fluid">
                <?php echo display_flash_messages(); ?>

                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">صور المعلم: <?php echo htmlspecialchars($attraction['name']); ?></h5>
                        <div>
                            <a href="<?php echo admin_url('attractions/index.php'); ?>" class="btn btn-outline-secondary">قائمة المعالم</a>
                            <a href="<?php echo admin_url('attractions/edit.php?id=' . $attraction_id); ?>" class="btn btn-outline-primary">تعديل المعلم</a>
                        </div>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="" enctype="multipart/form-data" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label">رفع صور جديدة (يمكن اختيار متعدد)</label>
                                <input type="file" name="images[]" multiple class="form-control">
                            </div>
                            <div>
                                <button class="btn btn-success" type="submit">رفع الصور</button>
                            </div>
                        </form>

                        <div class="row g-3">
                            <?php if (empty($images)): ?>
                                <div class="col-12"><div class="alert alert-info">لا توجد صور حاليا</div></div>
                            <?php endif; ?>
                            <?php foreach ($images as $img): ?>
                                <div class="col-6 col-md-3">
                                    <div class="card">
                                        <?php
                                            // build image URL for display
                                            $stored = $img['image_url'];
                                            if (preg_match('#^https?://#i', $stored)) {
                                                $img_url = $stored;
                                            } else {
                                                $img_url = (defined('UPLOADS_URL') ? rtrim(UPLOADS_URL, '/') : rtrim(BASE_URL, '/')) . '/' . ltrim($stored, '/');
                                            }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($img_url); ?>" class="card-img-top" style="height:160px;object-fit:cover;">
                                        <div class="card-body p-2 text-center">
                                            <form method="POST" action="" onsubmit="return confirm('هل تريد حذف هذه الصورة؟');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php
// مساعد لإعادة تشكيل مصفوفة $_FILES إلى قائمة ملفات مفهومة
function reformat_files_array(array $files): array {
    $result = [];
    $count = is_array($files['name']) ? count($files['name']) : 0;
    if ($count === 0) return [];
    for ($i = 0; $i < $count; $i++) {
        $result[] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
    }
    return $result;
}

?>
