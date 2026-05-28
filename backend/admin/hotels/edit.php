<?php
/**
 * تعديل فندق
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'تعديل فندق - Yemen Tourism';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'معرف الفندق غير محدد');
    header('Location: ' . admin_url('hotels/index.php'));
    exit();
}

$id = intval($_GET['id']);
$hotel = db_fetch("SELECT * FROM hotels WHERE id = ?", [$id]);
if (!$hotel) {
    set_flash_message('error', 'الفندق غير موجود');
    header('Location: ' . admin_url('hotels/index.php'));
    exit();
}

$errors = [];
$form = [
    'name' => $hotel['name'],
    'location_id' => $hotel['location_id'],
    'phone' => $hotel['phone'],
    'email' => $hotel['contact_email'],
    'website' => $hotel['website_url'],
    'stars' => $hotel['stars'],
    'is_active' => $hotel['is_active']
];

$locations = db_select("SELECT l.id, l.name, c.name as city_name FROM locations l LEFT JOIN cities c ON l.city_id = c.id ORDER BY c.name, l.name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name'] = trim($_POST['name'] ?? '');
    $form['location_id'] = intval($_POST['location_id'] ?? 0);
    $form['street'] = trim($_POST['street'] ?? '');
    $form['phone'] = trim($_POST['phone'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['website'] = trim($_POST['website'] ?? '');
    $form['stars'] = intval($_POST['stars'] ?? 3);
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if (empty($form['name'])) $errors['name'] = 'ادخل اسم الفندق';

    // رفع صورة جديدة
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        require_once __DIR__ . '/../../core/upload.php';
        $res = upload_hotel_image($_FILES['main_image']);
        if ($res['success']) $form_main_image = $res['relative_path'] ?? ltrim($res['url'] ?? '', '/');
        else $errors['main_image'] = implode(', ', $res['errors']);
    } else {
        $form_main_image = $hotel['main_image_url'];
    }

    if (empty($errors)) {
        $upd = db_execute("UPDATE hotels SET location_id = ?, name = ?, phone = ?, contact_email = ?, website_url = ?, stars = ?, main_image_url = ?, is_active = ? WHERE id = ?", [
            $form['location_id'],$form['name'],$form['phone'],$form['email'],$form['website'],$form['stars'],$form_main_image,$form['is_active'],$id
        ]);
        if ($upd !== false) {
            set_flash_message('success','تم تحديث الفندق');
            header('Location: ' . admin_url('hotels/index.php'));
            exit();
        } else $errors['general']='فشل التحديث';
    }
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
                        <h5 class="card-title mb-0">تعديل الفندق: <?php echo htmlspecialchars($hotel['name']); ?></h5>
                        <a href="<?php echo admin_url('hotels/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div><?php endif; ?>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">الاسم</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($form['name']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الموقع</label>
                                <select name="location_id" class="form-select">
                                    <option value="">اختر موقعاً</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?php echo $loc['id']; ?>" <?php echo ($form['location_id']==$loc['id'])?'selected':''; ?>><?php echo htmlspecialchars($loc['city_name'] . ' - ' . $loc['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الصورة الرئيسية الحالية</label>
                                <div class="mb-2">
                                    <?php if (!empty($hotel['main_image_url'])): ?>
                                        <?php $s = $hotel['main_image_url']; $d = preg_match('#^https?://#i',$s) ? $s : (defined('UPLOADS_URL')?rtrim(UPLOADS_URL,'/'):rtrim(BASE_URL,'/')).'/'.ltrim($s,'/'); ?>
                                        <img src="<?php echo htmlspecialchars($d); ?>" style="width:120px;height:80px;object-fit:cover;border-radius:4px;">
                                    <?php else: ?>
                                        <div class="default-image" style="width:120px;height:80px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;border-radius:4px;color:#6c757d;"><i class="fas fa-hotel"></i></div>
                                    <?php endif; ?>
                                </div>
                                <label class="form-label">تغيير الصورة (اختياري)</label>
                                <input type="file" name="main_image" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">عدد النجوم</label>
                                <input type="number" name="stars" class="form-control" min="1" max="5" value="<?php echo htmlspecialchars($form['stars']); ?>">
                            </div>
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" <?php echo $form['is_active'] ? 'checked' : ''; ?>><label class="form-check-label">نشط</label>
                            </div>
                            <div><button class="btn btn-primary" type="submit">حفظ</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
