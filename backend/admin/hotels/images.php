<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'صور الفندق - Yemen Tourism';

if (!isset($_GET['hotel_id']) || empty($_GET['hotel_id'])) {
    set_flash_message('error','معرف الفندق غير محدد');
    header('Location: ' . admin_url('hotels/index.php'));
    exit();
}

$hotel_id = intval($_GET['hotel_id']);
$hotel = db_fetch("SELECT * FROM hotels WHERE id = ?", [$hotel_id]);
if (!$hotel) {
    set_flash_message('error','الفندق غير موجود');
    header('Location: ' . admin_url('hotels/index.php'));
    exit();
}

// حذف صورة
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $img_id = intval($_GET['id']);
    $row = db_fetch("SELECT * FROM hotel_images WHERE id = ? AND hotel_id = ?", [$img_id, $hotel_id]);
    if ($row) {
        require_once __DIR__ . '/../../core/upload.php';
        if (!empty($row['image_url'])) delete_file($row['image_url']);
        db_execute("DELETE FROM hotel_images WHERE id = ?", [$img_id]);
        set_flash_message('success','تم حذف الصورة');
    }
    header('Location: ' . admin_url('hotels/images.php?hotel_id=' . $hotel_id));
    exit();
}

// رفع صور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    require_once __DIR__ . '/../../core/upload.php';
    $files = $_FILES['images'];
    $count = count($files['name']);
    $errors = [];
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        $single = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        $res = upload_hotel_image($single);
        if ($res['success']) {
            $rel = $res['relative_path'] ?? ltrim($res['url'] ?? '', '/');
            db_execute("INSERT INTO hotel_images (hotel_id, image_url, created_at) VALUES (?, ?, NOW())", [$hotel_id, $rel]);
        } else {
            $errors[] = implode(', ', $res['errors']);
        }
    }
    if (empty($errors)) set_flash_message('success','تم رفع الصور'); else set_flash_message('error',implode(' | ', $errors));
    header('Location: ' . admin_url('hotels/images.php?hotel_id=' . $hotel_id));
    exit();
}

$images = db_select("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC", [$hotel_id]);

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
                        <h5 class="card-title mb-0">صور الفندق: <?php echo htmlspecialchars($hotel['name']); ?></h5>
                        <div>
                            <a href="<?php echo admin_url('hotels/show.php?id=' . $hotel_id); ?>" class="btn btn-outline-secondary">عرض التفاصيل</a>
                            <a href="<?php echo admin_url('hotels/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">اختر صوراً (متعدد)</label>
                                <input type="file" name="images[]" multiple class="form-control">
                            </div>
                            <button class="btn btn-primary">رفع</button>
                        </form>

                        <hr>
                        <div class="row">
                            <?php foreach ($images as $img):
                                $s = $img['image_url'];
                                $d = preg_match('#^https?://#i',$s) ? $s : (defined('UPLOADS_URL')?rtrim(UPLOADS_URL,'/'):rtrim(BASE_URL,'/')).'/'.ltrim($s,'/');
                            ?>
                            <div class="col-3 mb-3">
                                <div class="card">
                                    <img src="<?php echo htmlspecialchars($d); ?>" class="card-img-top" style="height:160px;object-fit:cover;">
                                    <div class="card-body p-2 text-center">
                                        <a href="<?php echo admin_url('hotels/images.php?hotel_id=' . $hotel_id . '&action=delete&id=' . $img['id']); ?>" class="btn btn-sm btn-danger">حذف</a>
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
