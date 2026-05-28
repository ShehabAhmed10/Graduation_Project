<?php
/**
 * قائمة أنواع المعالم
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'أنواع المعالم - Yemen Tourism';

// حذف نوع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $res = db_execute("DELETE FROM attraction_types WHERE id = ?", [$id]);
    if ($res !== false) set_flash_message('success', 'تم حذف النوع');
    else set_flash_message('error', 'فشل حذف النوع');
    header('Location: ' . admin_url('attraction_types/index.php'));
    exit();
}

$types = db_select("SELECT * FROM attraction_types ORDER BY type_name");

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
                    <h4>أنواع المعالم</h4>
                    <a href="<?php echo admin_url('attraction_types/create.php'); ?>" class="btn btn-primary">إضافة نوع جديد</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الوصف</th>
                                        <th>أيقونة</th>
                                        <th>لون العلامة</th>
                                        <th>نشط</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($types as $t): ?>
                                        <tr>
                                            <td><?php echo $t['id']; ?></td>
                                            <td><?php echo htmlspecialchars($t['type_name']); ?></td>
                                            <td><?php echo htmlspecialchars($t['description']); ?></td>
                                            <td><?php echo htmlspecialchars($t['icon_name']); ?></td>
                                            <td><span style="display:inline-block;width:28px;height:18px;background:<?php echo htmlspecialchars($t['marker_color'] ?: '#ccc'); ?>;border-radius:4px;border:1px solid #ddd"></span></td>
                                            <td><?php echo $t['is_active'] ? 'نعم' : 'لا'; ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('attraction_types/edit.php?id=' . $t['id']); ?>" class="btn btn-sm btn-outline-primary">تعديل</a>
                                                <form method="POST" action="" style="display:inline-block;" onsubmit="return confirm('حذف النوع؟');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
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
