<?php
/**
 * صفحة إضافة معلم جديد
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إضافة معلم - Yemen Tourism';

$errors = [];
$form = [
	'name' => '',
	'short_description' => '',
	'description' => '',
	'location_id' => '',
	'attraction_type_id' => '',
	'is_active' => 1
];

// جلب المواقع وأنواع المعالم
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

	if (empty($form['location_id'])) {
		$errors['location_id'] = 'اختر الموقع المرتبط';
	}

	if (empty($form['attraction_type_id'])) {
		$errors['attraction_type_id'] = 'اختر نوع المعلم';
	}

	// معالجة رفع الصورة الرئيسية (اختياري) — نخزن المسار النسبي
	$main_image_path = null;
	if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
		require_once __DIR__ . '/../../core/upload.php';
		$upload_res = upload_attraction_image($_FILES['main_image']);
		if ($upload_res['success']) {
			$main_image_path = $upload_res['relative_path'] ?? ltrim($upload_res['url'] ?? '', '/');
		} else {
			$errors['main_image'] = implode(', ', $upload_res['errors']);
		}
	}

	if (empty($errors)) {
		try {
			$insert_sql = "INSERT INTO attractions (location_id, attraction_type_id, name, short_description, description, main_image_url, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
			$result = db_execute($insert_sql, [
				$form['location_id'],
				$form['attraction_type_id'],
				$form['name'],
				$form['short_description'],
				$form['description'],
				$main_image_path,
				$form['is_active']
			]);

			if ($result) {
				set_flash_message('success', 'تم إضافة المعلم بنجاح');
				header('Location: ' . admin_url('attractions/index.php'));
				exit();
			} else {
				$errors['general'] = 'تعذر إضافة المعلم';
			}
		} catch (PDOException $e) {
			$errors['general'] = 'حدث خطأ في الخادم: ' . $e->getMessage();
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
						<h5 class="card-title mb-0">إضافة معلم جديد</h5>
						<a href="<?php echo admin_url('attractions/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
					</div>
					<div class="card-body">
						<?php if (isset($errors['general'])): ?>
							<div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
						<?php endif; ?>

						<form method="POST" action="" enctype="multipart/form-data">
							<div class="mb-3">
								<label class="form-label">اسم المعلم</label>
								<input type="text" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($form['name']); ?>" required>
								<?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?php echo $errors['name']; ?></div><?php endif; ?>
							</div>

							<div class="mb-3">
								<label class="form-label">الموقع (Location)</label>
								<select name="location_id" class="form-select <?php echo isset($errors['location_id']) ? 'is-invalid' : ''; ?>" required>
									<option value="">اختر موقعاً</option>
									<?php foreach ($locations as $loc): ?>
										<option value="<?php echo $loc['id']; ?>" <?php echo ($form['location_id'] == $loc['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc['city_name'] . ' - ' . $loc['name']); ?></option>
									<?php endforeach; ?>
								</select>
								<?php if (isset($errors['location_id'])): ?><div class="invalid-feedback"><?php echo $errors['location_id']; ?></div><?php endif; ?>
							</div>

							<div class="mb-3">
								<label class="form-label">نوع المعلم</label>
								<select name="attraction_type_id" class="form-select <?php echo isset($errors['attraction_type_id']) ? 'is-invalid' : ''; ?>" required>
									<option value="">اختر نوعاً</option>
									<?php foreach ($types as $t): ?>
										<option value="<?php echo $t['id']; ?>" <?php echo ($form['attraction_type_id'] == $t['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['type_name']); ?></option>
									<?php endforeach; ?>
								</select>
								<?php if (isset($errors['attraction_type_id'])): ?><div class="invalid-feedback"><?php echo $errors['attraction_type_id']; ?></div><?php endif; ?>
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
								<label class="form-label">الصورة الرئيسية</label>
								<input type="file" name="main_image" class="form-control">
								<?php if (isset($errors['main_image'])): ?><div class="text-danger mt-1"><?php echo htmlspecialchars($errors['main_image']); ?></div><?php endif; ?>
							</div>

							<div class="mb-3 form-check form-switch">
								<input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $form['is_active'] ? 'checked' : ''; ?> >
								<label class="form-check-label" for="is_active">نشط</label>
							</div>

							<div>
								<button class="btn btn-primary" type="submit">حفظ</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>

<?php include '../includes/footer.php'; ?>

