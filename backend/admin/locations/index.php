<?php
/**
 * قائمة المواقع
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إدارة المواقع - Yemen Tourism';

// حذف موقع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $res = db_execute("DELETE FROM locations WHERE id = ?", [$id]);
    if ($res !== false) {
        set_flash_message('success', 'تم حذف الموقع');
    } else {
        set_flash_message('error', 'فشل حذف الموقع');
    }
    header('Location: ' . admin_url('locations/index.php'));
    exit();
}

$locations = db_select("SELECT l.*, c.name as city_name FROM locations l LEFT JOIN cities c ON l.city_id = c.id ORDER BY c.name, l.name");

include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>
        <main class="main-content">
            <div class="container-fluid">
                <?php echo display_flash_messages(); ?>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>قائمة المواقع</h4>
                    <a href="<?php echo admin_url('locations/create.php'); ?>" class="btn btn-primary">إضافة موقع جديد</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>المدينة</th>
                                        <th>الاسم</th>
                                        <th>الشارع</th>
                                        <th>lat</th>
                                        <th>lng</th>
                                        <th>zoom</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($locations as $loc): ?>
                                        <tr>
                                            <td><?php echo $loc['id']; ?></td>
                                            <td><?php echo htmlspecialchars($loc['city_name']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['name']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['street']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['latitude']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['longitude']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['zoom_level']); ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('locations/edit.php?id=' . $loc['id']); ?>" class="btn btn-sm btn-outline-primary">تعديل</a>
                                                <form method="POST" action="" style="display:inline-block;" onsubmit="return confirm('حذف الموقع؟');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
                                                    <button class="btn btn-sm btn-danger">حذف</button>
                                                </form>
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
