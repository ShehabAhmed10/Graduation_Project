<?php
/**
 * إنشاء نوع معلم
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إضافة نوع معلم - Yemen Tourism';

$errors = [];
$form = ['type_name' => '', 'description' => '', 'icon_name' => '', 'marker_color' => '#2b8af7', 'is_active' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['type_name'] = trim($_POST['type_name'] ?? '');
    $form['description'] = trim($_POST['description'] ?? '');
    $form['icon_name'] = trim($_POST['icon_name'] ?? '');
    $form['marker_color'] = trim($_POST['marker_color'] ?? '#2b8af7');
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if (empty($form['type_name'])) $errors['type_name'] = 'اسم النوع مطلوب';

    // تحقق من التكرار
    $exists = db_fetch("SELECT id FROM attraction_types WHERE type_name = ?", [$form['type_name']]);
    if ($exists) $errors['type_name'] = 'هذا الاسم موجود بالفعل';

    if (empty($errors)) {
        $res = db_execute("INSERT INTO attraction_types (type_name, description, icon_name, marker_color, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())", [
            $form['type_name'], $form['description'], $form['icon_name'], $form['marker_color'], $form['is_active']
        ]);
        if ($res !== false) {
            set_flash_message('success', 'تم إضافة النوع');
            header('Location: ' . admin_url('attraction_types/index.php'));
            exit();
        } else {
            $errors['general'] = 'فشل إضافة النوع';
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
                        <h5 class="card-title mb-0">إضافة نوع معلم</h5>
                        <a href="<?php echo admin_url('attraction_types/index.php'); ?>" class="btn btn-outline-secondary">عودة للقائمة</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div><?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">اسم النوع</label>
                                <input type="text" name="type_name" class="form-control <?php echo isset($errors['type_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($form['type_name']); ?>">
                                <?php if (isset($errors['type_name'])): ?><div class="invalid-feedback"><?php echo $errors['type_name']; ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control"><?php echo htmlspecialchars($form['description']); ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">أيقونة (اسم أيقونة)</label>
                                    <input type="text" name="icon_name" id="icon_name" class="form-control" value="<?php echo htmlspecialchars($form['icon_name']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">لون العلامة</label>
                                    <input type="color" name="marker_color" id="marker_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($form['marker_color']); ?>">
                                </div>
                            </div>

                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $form['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">نشط</label>
                            </div>

                            <div class="mb-3">
                                <div id="preview" style="display:inline-block;padding:8px;border:1px solid #eee;border-radius:6px;">معاينة: <span id="preview-swatch" style="display:inline-block;width:18px;height:18px;border-radius:4px;background:<?php echo htmlspecialchars($form['marker_color']); ?>;margin-left:8px;vertical-align:middle"></span> <span id="preview-icon" style="margin-right:8px;"><?php echo htmlspecialchars($form['icon_name']); ?></span></div>
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
<script src="<?php echo admin_asset('js/attraction_types.js'); ?>"></script>
