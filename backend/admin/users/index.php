<?php
/**
 * قائمة المستخدمين
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'المستخدمون - Yemen Tourism';

// حذف مستخدم عبر ?delete=
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $del_id = intval($_GET['delete']);

    // منع حذف نفس الحساب الذي يعمل الآن
    $current_admin_id = $_SESSION['admin_id'] ?? null;
    if ($current_admin_id && $del_id == $current_admin_id) {
        set_flash_message('error', 'لا يمكن حذف المستخدم الحالي المسجل الدخول');
        header('Location: ' . admin_url('users/index.php'));
        exit();
    }

    $res = db_execute("DELETE FROM users WHERE id = ?", [$del_id]);
    if ($res !== false) {
        set_flash_message('success', 'تم حذف المستخدم بنجاح');
    } else {
        set_flash_message('error', 'تعذر حذف المستخدم');
    }
    header('Location: ' . admin_url('users/index.php'));
    exit();
}

$users = db_select("SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC");

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
                    <h4 class="mb-0">المستخدمون</h4>
                    <div>
                        <?php echo create_add_button('users/create.php', 'إضافة مستخدم'); ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم الكامل</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الدور</th>
                                        <th>نشط</th>
                                        <th>انضم</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td><?php echo $u['id']; ?></td>
                                            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                            <td><?php echo htmlspecialchars($u['role']); ?></td>
                                            <td><?php echo $u['is_active'] ? 'نعم' : 'لا'; ?></td>
                                            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo admin_url('users/edit.php?id=' . $u['id']); ?>" class="btn btn-sm btn-outline-primary">تعديل</a>
                                                    <?php
                                                    $current_admin_id = $_SESSION['admin_id'] ?? null;
                                                    if (!($current_admin_id && $current_admin_id == $u['id'])): ?>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger delete-btn"
                                                                data-url="<?php echo admin_url('users/index.php?delete=' . $u['id']); ?>"
                                                                data-name="<?php echo htmlspecialchars($u['full_name']); ?>">
                                                            حذف
                                                        </button>
                                                    <?php endif; ?>
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
