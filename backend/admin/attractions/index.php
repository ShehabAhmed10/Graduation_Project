<?php
/**
 * عرض قائمة المعالم
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إدارة المعالم - Yemen Tourism';

// حذف معلم (إذا طُلب)
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
	$id = intval($_GET['delete']);
	try {
		$result = db_execute("DELETE FROM attractions WHERE id = ?", [$id]);
		if ($result) {
			set_flash_message('success', 'تم حذف المعلم بنجاح');
		} else {
			set_flash_message('error', 'تعذر حذف المعلم');
		}
	} catch (PDOException $e) {
		set_flash_message('error', 'حدث خطأ: ' . $e->getMessage());
	}

	header('Location: ' . admin_url('attractions/index.php'));
	exit();
}

// جلب المعالم
$sql = "SELECT a.*, l.name as location_name, c.name as city_name, t.type_name
		FROM attractions a
		LEFT JOIN locations l ON a.location_id = l.id
		LEFT JOIN cities c ON l.city_id = c.id
		LEFT JOIN attraction_types t ON a.attraction_type_id = t.id
		ORDER BY a.created_at DESC";
$attractions = db_select($sql);

include '../includes/header.php';
?>

<div class="dashboard-layout">
	<?php include '../includes/sidebar.php'; ?>
	<div class="main-wrapper">
		<?php include '../includes/topbar.php'; ?>
		<main class="main-content">
			<div class="container-fluid">
				<?php echo display_flash_messages(); ?>

				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h1 class="h3 mb-2">قائمة المعالم</h1>
					</div>
					<div>
						<a href="<?php echo admin_url('attractions/create.php'); ?>" class="btn btn-primary">
							<i class="fas fa-plus me-2"></i>إضافة معلم جديد
						</a>
					</div>
				</div>

				<div class="card">
					<div class="card-body">
						<?php if (!empty($attractions)): ?>
							<div class="table-responsive">
								<table class="table table-hover datatable">
									<thead>
										<tr>
											<th>#</th>
											<th>الصورة</th>
											<th>اسم المعلم</th>
											<th>المدينة</th>
											<th>النوع</th>
											<th>التقييم</th>
											<th>الحالة</th>
											<th>تاريخ الإضافة</th>
											<th>الإجراءات</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($attractions as $index => $a): ?>
											<tr>
												<td><?php echo $a['id']; ?></td>
												<td>
													<?php if (!empty($a['main_image_url'])): ?>
															<?php
																$stored = $a['main_image_url'];
																if (preg_match('#^https?://#i', $stored)) {
																	$img_src = $stored;
																} else {
																	$img_src = (defined('UPLOADS_URL') ? rtrim(UPLOADS_URL, '/') : rtrim(BASE_URL, '/')) . '/' . ltrim($stored, '/');
																}
															?>
															<img src="<?php echo htmlspecialchars($img_src); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">
														<?php else: ?>
														<div class="default-image" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;border-radius:4px;color:#6c757d;"><i class="fas fa-image"></i></div>
													<?php endif; ?>
												</td>
												<td><?php echo htmlspecialchars($a['name']); ?></td>
												<td><?php echo htmlspecialchars($a['city_name'] ?? ''); ?></td>
												<td><?php echo htmlspecialchars($a['type_name'] ?? ''); ?></td>
												<td><?php echo number_format($a['avg_rating'],2); ?> (<?php echo $a['total_reviews']; ?>)</td>
												<td><?php echo display_status_badge($a['is_active']); ?></td>
												<td><?php echo format_date_arabic($a['created_at']); ?></td>
												<td>
													<div class="btn-group btn-group-sm" role="group">
														<a href="<?php echo admin_url('attractions/edit.php?id=' . $a['id']); ?>" class="btn btn-outline-warning" title="تعديل">
															<i class="fas fa-edit"></i>
														</a>
														<a href="<?php echo admin_url('attractions/images.php?attraction_id=' . $a['id']); ?>" class="btn btn-outline-info" title="الصور">
															<i class="fas fa-images"></i>
														</a>
														<button type="button" class="btn btn-outline-danger delete-btn" data-url="<?php echo admin_url('attractions/index.php?delete=' . $a['id']); ?>" data-name="<?php echo htmlspecialchars($a['name']); ?>">
															<i class="fas fa-trash"></i>
														</button>
													</div>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php else: ?>
							<div class="text-center py-4">
								<i class="fas fa-landmark fa-3x text-muted mb-3"></i>
								<p class="text-muted">لا يوجد معالم بعد</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>

<?php include '../includes/footer.php'; ?>

