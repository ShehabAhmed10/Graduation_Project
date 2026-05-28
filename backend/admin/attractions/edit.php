<?php
/**
 * تعديل معلم
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'تعديل معلم - Yemen Tourism';

if (!isset($_GET['id']) || empty($_GET['id'])) {
	set_flash_message('error', 'معرف المعلم غير محدد');
	header('Location: ' . admin_url('attractions/index.php'));
	exit();
}

$attraction_id = intval($_GET['id']);
$attraction = db_fetch("SELECT * FROM attractions WHERE id = ?", [$attraction_id]);
if (!$attraction) {
	set_flash_message('error', 'لم يتم العثور على المعلم');
	header('Location: ' . admin_url('attractions/index.php'));
	exit();
}

$errors = [];
$form = [
	'name' => $attraction['name'],
	'short_description' => $attraction['short_description'],
	'description' => $attraction['description'],
	'location_id' => $attraction['location_id'],
	'attraction_type_id' => $attraction['attraction_type_id'],
	'is_active' => $attraction['is_active']
];

$locations = db_select("SELECT l.id, l.name, c.name as city_name FROM locations l LEFT JOIN cities c ON l.city_id = c.id ORDER BY c.name, l.name");
$types = db_select("SELECT id, type_name FROM attraction_types WHERE is_active = 1 ORDER BY type_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$form['name'] = trim($_POST['name'] ?? '');
	$form['short_description'] = trim($_POST['short_description'] ?? '');
	$form['description'] = trim($_POST['description'] ?? '');
	$form['location_id'] = intval($_POST['location_id'] ?? 0);
	$form['attraction_type_id'] = intval($_POST['attraction_type_id'] ?? 0);
	$form['is_active'] = isset($_POST['is_active']) ? 1 : 0;

	if (empty($form['name'])) {
		$errors['name'] = 'اسم المعلم مطلوب';
	}

	// رفع صورة بديلة — نخزن المسار النسبي
	if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
		require_once __DIR__ . '/../../core/upload.php';
		$upload_res = upload_attraction_image($_FILES['main_image']);
		if ($upload_res['success']) {
			$form_main_image_url = $upload_res['relative_path'] ?? ltrim($upload_res['url'] ?? '', '/');
		} else {
			$errors['main_image'] = implode(', ', $upload_res['errors']);
		}
	} else {
		$form_main_image_url = $attraction['main_image_url'];
	}

	if (empty($errors)) {
		try {
			$update_sql = "UPDATE attractions SET location_id = ?, attraction_type_id = ?, name = ?, short_description = ?, description = ?, main_image_url = ?, is_active = ? WHERE id = ?";
			$result = db_execute($update_sql, [
				$form['location_id'],
				$form['attraction_type_id'],
				$form['name'],
				$form['short_description'],
				$form['description'],
				$form_main_image_url,
				$form['is_active'],
				$attraction_id
			]);

			if ($result !== false) {
				set_flash_message('success', 'تم تحديث المعلم');
				header('Location: ' . admin_url('attractions/index.php'));
				exit();
			} else {
				$errors['general'] = 'لم يتم تحديث أي بيانات';
			}
		} catch (PDOException $e) {
			$errors['general'] = 'حدث خطأ: ' . $e->getMessage();
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
						<h5 class="card-title mb-0">تعديل معلم: <?php echo htmlspecialchars($attraction['name']); ?></h5>
						<a href="<?php echo admin_url('attractions/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
					</div>
					<div class="card-body">
						<?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div><?php endif; ?>

						<form method="POST" action="" enctype="multipart/form-data">
							<div class="mb-3">
								<label class="form-label">اسم المعلم</label>
								<input type="text" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($form['name']); ?>" required>
								<?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?php echo $errors['name']; ?></div><?php endif; ?>
							</div>

							<div class="mb-3">
								<label class="form-label">الموقع</label>
								<select name="location_id" class="form-select">
									<?php foreach ($locations as $loc): ?>
										<option value="<?php echo $loc['id']; ?>" <?php echo ($form['location_id'] == $loc['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc['city_name'] . ' - ' . $loc['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="mb-3">
								<label class="form-label">نوع المعلم</label>
								<select name="attraction_type_id" class="form-select">
									<?php foreach ($types as $t): ?>
										<option value="<?php echo $t['id']; ?>" <?php echo ($form['attraction_type_id'] == $t['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['type_name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="mb-3">
								<label class="form-label">وصف مختصر</label>
								<input type="text" name="short_description" class="form-control" value="<?php echo htmlspecialchars($form['short_description']); ?>">
							</div>

							<div class="mb-3">
								<label class="form-label">الوصف التفصيلي</label>
								<textarea name="description" class="form-control" rows="6"><?php echo htmlspecialchars($form['description']); ?></textarea>
							</div>

							<div class="mb-3">
								<label class="form-label">الصورة الرئيسية الحالية</label>
								<div class="mb-2">
									<?php if (!empty($attraction['main_image_url'])): ?>
										<?php
											$stored_img = $attraction['main_image_url'];
											if (preg_match('#^https?://#i', $stored_img)) {
												$display_img = $stored_img;
											} else {
												$display_img = (defined('UPLOADS_URL') ? rtrim(UPLOADS_URL, '/') : rtrim(BASE_URL, '/')) . '/' . ltrim($stored_img, '/');
											}
										?>
										<img src="<?php echo htmlspecialchars($display_img); ?>" style="width:120px;height:80px;object-fit:cover;border-radius:4px;">
									<?php else: ?>
										<div class="default-image" style="width:120px;height:80px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;border-radius:4px;color:#6c757d;"><i class="fas fa-image"></i></div>
									<?php endif; ?>
								</div>
								<label class="form-label">تغيير الصورة الرئيسية (اختياري)</label>
								<input type="file" name="main_image" class="form-control">
								<?php if (isset($errors['main_image'])): ?><div class="text-danger mt-1"><?php echo htmlspecialchars($errors['main_image']); ?></div><?php endif; ?>
							</div>

							<div class="mb-3 form-check form-switch">
								<input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $form['is_active'] ? 'checked' : ''; ?>>
								<label class="form-check-label" for="is_active">نشط (Active)</label>
							</div>

                            <div class="mb-3 form-check form-switch">
								<input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?php echo (isset($attraction['is_featured']) && $attraction['is_featured']) ? 'checked' : ''; ?> disabled>
								<label class="form-check-label" for="is_featured">
                                    معلم مميز (Featured) 
                                    <small class="text-muted">- للتحكم في هذا الخيار وترتيبه، انتقل إلى <a href="<?php echo admin_url('attractions/featured.php'); ?>">صفحة المعالم المميزة</a></small>
                                </label>
							</div>

							<div>
								<button class="btn btn-primary" type="submit">حفظ التعديلات</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>

<?php include '../includes/footer.php'; ?>

