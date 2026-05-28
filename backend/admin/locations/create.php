<?php
/**
 * إنشاء موقع جديد
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إضافة موقع - Yemen Tourism';

$errors = [];
$form = [
    'city_id' => '',
    'name' => '',
    'street' => '',
    'latitude' => '',
    'longitude' => '',
    'zoom_level' => 12
];

$cities = db_select("SELECT id, name FROM cities ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['city_id'] = intval($_POST['city_id'] ?? 0);
    $form['name'] = trim($_POST['name'] ?? '');
    $form['street'] = trim($_POST['street'] ?? '');
    $form['latitude'] = trim($_POST['latitude'] ?? '');
    $form['longitude'] = trim($_POST['longitude'] ?? '');
    $form['zoom_level'] = intval($_POST['zoom_level'] ?? 12);

    if (empty($form['city_id'])) $errors['city_id'] = 'اختر مدينة';
    if (empty($form['name'])) $errors['name'] = 'ادخل اسم الموقع';

    if (empty($errors)) {
        $res = db_execute("INSERT INTO locations (city_id, name, street, latitude, longitude, zoom_level, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())", [
            $form['city_id'], $form['name'], $form['street'], $form['latitude'], $form['longitude'], $form['zoom_level']
        ]);
        if ($res !== false) {
            set_flash_message('success', 'تم إضافة الموقع');
            header('Location: ' . admin_url('locations/index.php'));
            exit();
        } else {
            $errors['general'] = 'فشل إضافة الموقع';
        }
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
                        <h5 class="card-title mb-0">إضافة موقع جديد</h5>
                        <a href="<?php echo admin_url('locations/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div><?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">المدينة</label>
                                <select name="city_id" class="form-select <?php echo isset($errors['city_id']) ? 'is-invalid' : ''; ?>">
                                    <option value="">-- اختر المدينة --</option>
                                    <?php foreach ($cities as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($form['city_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">اسم الموقع</label>
                                <input type="text" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($form['name']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">شارع / عنوان</label>
                                <input type="text" name="street" class="form-control" value="<?php echo htmlspecialchars($form['street']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الإحداثيات (انقر على الخريطة لتحديد)</label>
                                <div id="map" style="height:320px;margin-bottom:10px;"></div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="latitude" id="latitude" class="form-control" placeholder="latitude" value="<?php echo htmlspecialchars($form['latitude']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="longitude" id="longitude" class="form-control" placeholder="longitude" value="<?php echo htmlspecialchars($form['longitude']); ?>">
                                    </div>
                                </div>
                                <input type="hidden" name="zoom_level" id="zoom_level" value="<?php echo htmlspecialchars($form['zoom_level']); ?>">
                            </div>

                            <div>
                                <button class="btn btn-primary" type="submit">حفظ الموقع</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Leaflet CSS & JS (CDN) and page script -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?php echo admin_asset('js/locations.js'); ?>"></script>
