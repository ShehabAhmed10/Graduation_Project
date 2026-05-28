<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'تفاصيل الفندق - Yemen Tourism';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error','معرف الفندق غير محدد');
    header('Location: ' . admin_url('hotels/index.php'));
    exit();
}

$id = intval($_GET['id']);
$hotel = db_fetch("SELECT h.*, l.name as location_name, c.name as city_name FROM hotels h LEFT JOIN locations l ON h.location_id = l.id LEFT JOIN cities c ON l.city_id = c.id WHERE h.id = ?", [$id]);
if (!$hotel) {
    set_flash_message('error','الفندق غير موجود');
    header('Location: ' . admin_url('hotels/index.php'));
    exit();
}

$images = db_select("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC", [$id]);

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
                        <h5 class="card-title mb-0">تفاصيل الفندق: <?php echo htmlspecialchars($hotel['name']); ?></h5>
                        <div>
                            <a href="<?php echo admin_url('hotels/images.php?hotel_id=' . $id); ?>" class="btn btn-outline-secondary">إدارة الصور</a>
                            <a href="<?php echo admin_url('hotels/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?php if (!empty($hotel['main_image_url'])): $s = $hotel['main_image_url']; $d = preg_match('#^https?://#i',$s) ? $s : (defined('UPLOADS_URL')?rtrim(UPLOADS_URL,'/'):rtrim(BASE_URL,'/')).'/'.ltrim($s,'/'); ?>
                                    <img src="<?php echo htmlspecialchars($d); ?>" style="width:100%;height:220px;object-fit:cover;border-radius:6px;">
                                <?php else: ?>
                                    <div style="width:100%;height:220px;background:#f8f9fa;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#6c757d;">لا توجد صورة</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless">
                                    <tr><th>الاسم</th><td><?php echo htmlspecialchars($hotel['name']); ?></td></tr>
                                    <tr><th>المدينة / الموقع</th><td><?php echo htmlspecialchars($hotel['city_name'] . ' / ' . $hotel['location_name']); ?></td></tr>
                                    <tr><th>العنوان</th><td><?php echo htmlspecialchars($hotel['street']); ?></td></tr>
                                    <tr><th>الهاتف</th><td><?php echo htmlspecialchars($hotel['phone']); ?></td></tr>
                                    <tr><th>البريد</th><td><?php echo htmlspecialchars($hotel['email']); ?></td></tr>
                                    <tr><th>الموقع</th><td><?php echo htmlspecialchars($hotel['website']); ?></td></tr>
                                </table>
                            </div>
                        </div>

                        <hr>
                        <h6>معرض الصور</h6>
                        <div class="row mt-2">
                            <?php foreach ($images as $img): $s = $img['image_url']; $d = preg_match('#^https?://#i',$s) ? $s : (defined('UPLOADS_URL')?rtrim(UPLOADS_URL,'/'):rtrim(BASE_URL,'/')).'/'.ltrim($s,'/'); ?>
                                <div class="col-3 mb-3">
                                    <div class="card">
                                        <img src="<?php echo htmlspecialchars($d); ?>" class="card-img-top" style="height:140px;object-fit:cover;">
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
