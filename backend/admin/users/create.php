<?php
/**
 * إضافة مستخدم جديد
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إضافة مستخدم - Yemen Tourism';

$errors = [];
$form = [
    'full_name' => '',
    'email' => '',
    'role' => 'user',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['role'] = trim($_POST['role'] ?? 'user');
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    if (empty($form['full_name'])) $errors['full_name'] = 'الاسم مطلوب';
    if (empty($form['email']) || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'بريد إلكتروني صالح مطلوب';
    if (empty($password)) $errors['password'] = 'كلمة المرور مطلوبة';
    if ($password !== $password_confirm) $errors['password_confirm'] = 'تأكيد كلمة المرور لا يتطابق';

    // تحقق من عدم وجود البريد مسبقاً
    $existing = db_fetch("SELECT id FROM users WHERE email = ?", [$form['email']]);
    if ($existing) $errors['email'] = 'هذا البريد مستخدم بالفعل';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $res = db_execute("INSERT INTO users (full_name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())", [
            $form['full_name'],
            $form['email'],
            $hash,
            $form['role'],
            $form['is_active']
        ]);

        if ($res !== false) {
            set_flash_message('success', 'تم إنشاء المستخدم بنجاح');
            header('Location: ' . admin_url('users/index.php'));
            exit();
        } else {
            $errors['general'] = 'فشل إنشاء المستخدم';
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
                        <h5 class="card-title mb-0">إضافة مستخدم جديد</h5>
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
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
                                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">تأكيد كلمة المرور</label>
                                <input type="password" name="password_confirm" class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>">
                                <?php if (isset($errors['password_confirm'])): ?><div class="invalid-feedback"><?php echo $errors['password_confirm']; ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الدور</label>
                                <select name="role" class="form-select">
                                    <option value="user" <?php echo ($form['role'] === 'user') ? 'selected' : ''; ?>>مستخدم</option>
                                    <option value="admin" <?php echo ($form['role'] === 'admin') ? 'selected' : ''; ?>>مشرف</option>
                                </select>
                            </div>

                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $form['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">نشط</label>
                            </div>

                            <div>
                                <button class="btn btn-primary" type="submit">إنشاء المستخدم</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
