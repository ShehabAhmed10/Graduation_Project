<?php
/**
 * تعديل مستخدم
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'تعديل مستخدم - Yemen Tourism';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'معرف المستخدم غير محدد');
    header('Location: ' . admin_url('users/index.php'));
    exit();
}

$id = intval($_GET['id']);
$user = db_fetch("SELECT * FROM users WHERE id = ?", [$id]);
if (!$user) {
    set_flash_message('error', 'المستخدم غير موجود');
    header('Location: ' . admin_url('users/index.php'));
    exit();
}

$errors = [];
$form = [
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'is_active' => $user['is_active']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['role'] = trim($_POST['role'] ?? 'user');
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $new_password = trim($_POST['new_password'] ?? '');

    if (empty($form['full_name'])) $errors['full_name'] = 'الاسم مطلوب';
    if (empty($form['email']) || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'بريد إلكتروني صالح مطلوب';

    if (empty($errors)) {
        $params = [$form['full_name'], $form['email'], $form['role'], $form['is_active'], $id];
        $sql = "UPDATE users SET full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?";
        $res = db_execute($sql, $params);
        if ($res !== false) {
            if (!empty($new_password)) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                db_execute("UPDATE users SET password_hash = ? WHERE id = ?", [$hash, $id]);
            }
            set_flash_message('success', 'تم تحديث بيانات المستخدم');
            header('Location: ' . admin_url('users/index.php'));
            exit();
        } else {
            $errors['general'] = 'فشل تحديث المستخدم';
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
                        <h5 class="card-title mb-0">تعديل المستخدم: <?php echo htmlspecialchars($user['full_name']); ?></h5>
                        <a href="<?php echo admin_url('users/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div><?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">الاسم الكامل</label>
                                <input type="text" name="full_name" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($form['full_name']); ?>">
                                <?php if (isset($errors['full_name'])): ?><div class="invalid-feedback"><?php echo $errors['full_name']; ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($form['email']); ?>">
                                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الدور</label>
                                <select name="role" class="form-select">
                                    <option value="user" <?php echo ($form['role'] === 'user') ? 'selected' : ''; ?>>مستخدم</option>
                                    <option value="admin" <?php echo ($form['role'] === 'admin') ? 'selected' : ''; ?>>مشرف</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">كلمة مرور جديدة (اختياري)</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>

                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $form['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">نشط</label>
                            </div>

                            <div>
                                <button class="btn btn-primary" type="submit">حفظ التغييرات</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
