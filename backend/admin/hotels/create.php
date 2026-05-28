<?php
/**
 * إضافة فندق
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إضافة فندق - Yemen Tourism';

$errors = [];
$form = ['name'=>'','location_id'=>'','street'=>'','phone'=>'','email'=>'','website'=>'','stars'=>3,'is_active'=>1];

$cities = db_select("SELECT id, name FROM cities ORDER BY name");
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

    if (empty($form['name'])) $errors['name']='ادخل اسم الفندق';
    if (empty($form['location_id'])) $errors['location_id']='اختر موقعا';

    // main image
    $main_image = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        require_once __DIR__ . '/../../core/upload.php';
        $res = upload_hotel_image($_FILES['main_image']);
        if ($res['success']) $main_image = $res['relative_path'] ?? ltrim($res['url'] ?? '', '/');
        else $errors['main_image'] = implode(', ', $res['errors']);
    }

    if (empty($errors)) {
        $ins = db_execute("INSERT INTO hotels (location_id, name, phone, contact_email, website_url, stars, main_image_url, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())", [
            $form['location_id'],$form['name'],$form['phone'],$form['email'],$form['website'],$form['stars'],$main_image,$form['is_active']
        ]);
        if ($ins !== false) {
            set_flash_message('success','تم إضافة الفندق');
            header('Location: ' . admin_url('hotels/index.php'));
            exit();
        } else $errors['general']='فشل الإضافة';
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
                        <h5 class="card-title mb-0">إضافة فندق</h5>
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
                                <label class="form-label">شارع / عنوان</label>
                                <input type="text" name="street" class="form-control" value="<?php echo htmlspecialchars($form['street']); ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3"><label class="form-label">هاتف</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($form['phone']); ?>"></div>
                                <div class="col-md-4 mb-3"><label class="form-label">بريد</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($form['email']); ?>"></div>
                                <div class="col-md-4 mb-3"><label class="form-label">موقع</label><input type="text" name="website" class="form-control" value="<?php echo htmlspecialchars($form['website']); ?>"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الصورة الرئيسية</label>
                                <input type="file" name="main_image" class="form-control">
                                <?php if (isset($errors['main_image'])): ?><div class="text-danger"><?php echo htmlspecialchars($errors['main_image']); ?></div><?php endif; ?>
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
