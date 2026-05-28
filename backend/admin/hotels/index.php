<?php
/**
 * قائمة الفنادق
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إدارة الفنادق - Yemen Tourism';

// حذف فندق
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $res = db_execute("DELETE FROM hotels WHERE id = ?", [$id]);
    if ($res !== false) set_flash_message('success', 'تم حذف الفندق');
    else set_flash_message('error', 'فشل حذف الفندق');
    header('Location: ' . admin_url('hotels/index.php'));
    exit();
}

$hotels = db_select("SELECT h.*, l.name as location_name, c.name as city_name FROM hotels h LEFT JOIN locations l ON h.location_id = l.id LEFT JOIN cities c ON l.city_id = c.id ORDER BY h.created_at DESC");

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
                    <h4>قائمة الفنادق</h4>
                    <a href="<?php echo admin_url('hotels/create.php'); ?>" class="btn btn-primary">إضافة فندق جديد</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الصورة</th>
                                        <th>اسم الفندق</th>
                                        <th>المدينة</th>
                                        <th>الموقع</th>
                                        <th>النجوم</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإضافة</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hotels as $h): ?>
                                        <tr>
                                            <td><?php echo $h['id']; ?></td>
                                            <td>
                                                <?php if (!empty($h['main_image_url'])): ?>
                                                    <?php
                                                        $stored = $h['main_image_url'];
                                                        if (preg_match('#^https?://#i', $stored)) $img_src = $stored;
                                                        else $img_src = (defined('UPLOADS_URL') ? rtrim(UPLOADS_URL, '/') : rtrim(BASE_URL, '/')) . '/' . ltrim($stored, '/');
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($img_src); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">
                                                <?php else: ?>
                                                    <div class="default-image" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;border-radius:4px;color:#6c757d;"><i class="fas fa-hotel"></i></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($h['name']); ?></td>
                                            <td><?php echo htmlspecialchars($h['city_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($h['location_name'] ?? ''); ?></td>
                                            <td><?php echo (int)($h['stars'] ?? 0); ?></td>
                                            <td><?php echo display_status_badge($h['is_active']); ?></td>
                                            <td><?php echo format_date_arabic($h['created_at']); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?php echo admin_url('hotels/edit.php?id=' . $h['id']); ?>" class="btn btn-outline-warning" title="تعديل"><i class="fas fa-edit"></i></a>
                                                    <a href="<?php echo admin_url('hotels/images.php?id=' . $h['id']); ?>" class="btn btn-outline-info" title="الصور"><i class="fas fa-images"></i></a>
                                                    <a href="<?php echo admin_url('hotels/show.php?id=' . $h['id']); ?>" class="btn btn-outline-secondary" title="عرض"><i class="fas fa-eye"></i></a>
                                                    <form method="POST" action="" style="display:inline-block;" onsubmit="return confirm('حذف الفندق؟');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $h['id']; ?>">
                                                        <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
