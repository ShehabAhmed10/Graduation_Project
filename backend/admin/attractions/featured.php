<?php
/**
 * إدارة المعالم المميزة
 */

require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'المعالم المميزة - Yemen Tourism';

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // إضافة معلم للمميزة
    if (isset($_POST['action']) && $_POST['action'] === 'add_featured' && !empty($_POST['attraction_id'])) {
        $id = intval($_POST['attraction_id']);
        // نحصل على أعلى ترتيب حالي
        $max_order = db_fetch("SELECT MAX(featured_order) as max_val FROM attractions WHERE is_featured = 1");
        $next_order = ($max_order['max_val'] ?? 0) + 1;
        
        $res = db_execute("UPDATE attractions SET is_featured = 1, featured_order = ? WHERE id = ?", [$next_order, $id]);
        
        if ($res) set_flash_message('success', 'تم إضافة المعلم للمميزة');
        else set_flash_message('error', 'حدث خطأ أثناء الإضافة');
        
        header('Location: ' . admin_url('attractions/featured.php'));
        exit();
    }
    
    // إزالة من المميزة
    if (isset($_POST['action']) && $_POST['action'] === 'remove_featured' && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $res = db_execute("UPDATE attractions SET is_featured = 0, featured_order = 0 WHERE id = ?", [$id]);
        
        if ($res) set_flash_message('success', 'تم إزالة المعلم من المميزة');
        else set_flash_message('error', 'حدث خطأ أثناء الإزالة');
        
        header('Location: ' . admin_url('attractions/featured.php'));
        exit();
    }
    
    // تحديث الترتيب
    if (isset($_POST['action']) && $_POST['action'] === 'update_order' && !empty($_POST['orders'])) {
        $orders = $_POST['orders']; // Array [id => order]
        foreach ($orders as $id => $order) {
            db_execute("UPDATE attractions SET featured_order = ? WHERE id = ?", [intval($order), intval($id)]);
        }
        set_flash_message('success', 'تم تحديث الترتيب بنجاح');
        header('Location: ' . admin_url('attractions/featured.php'));
        exit();
    }
}

// جلب المعالم المميزة
$featured_attractions = db_select("SELECT a.*, c.name as city_name 
                                   FROM attractions a 
                                   LEFT JOIN locations l ON a.location_id = l.id 
                                   LEFT JOIN cities c ON l.city_id = c.id 
                                   WHERE a.is_featured = 1 
                                   ORDER BY a.featured_order ASC");

// جلب المعالم غير المميزة للإضافة
$non_featured = db_select("SELECT a.id, a.name, c.name as city_name 
                           FROM attractions a 
                           LEFT JOIN locations l ON a.location_id = l.id 
                           LEFT JOIN cities c ON l.city_id = c.id 
                           WHERE a.is_featured = 0 AND a.is_active = 1
                           ORDER BY c.name, a.name");

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
                        <h1 class="h3 mb-2">المعالم المميزة</h1>
                        <p class="text-muted">تحكم في المعالم التي تظهر في واجهة "المميزة" بالتطبيق</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFeaturedModal">
                            <i class="fas fa-plus me-2"></i>إضافة معلم مميز
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">قائمة المعالم المميزة (سحب وإفلات غير مدعوم حالياً، استخدم الأرقام للترتيب)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($featured_attractions)): ?>
                            <form action="" method="POST" id="orderForm">
                                <input type="hidden" name="action" value="update_order">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th width="80">الترتيب</th>
                                                <th width="100">الصورة</th>
                                                <th>اسم المعلم</th>
                                                <th>المدينة</th>
                                                <th>التقييم</th>
                                                <th width="100">إجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($featured_attractions as $a): ?>
                                                <tr>
                                                    <td>
                                                        <input type="number" name="orders[<?php echo $a['id']; ?>]" value="<?php echo $a['featured_order']; ?>" class="form-control form-control-sm text-center" style="width: 70px;">
                                                    </td>
                                                    <td>
                                                        <?php echo display_image($a['main_image_url'], $a['name'], 'fas fa-landmark', '50px'); ?>
                                                    </td>
                                                    <td class="fw-bold"><?php echo htmlspecialchars($a['name']); ?></td>
                                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($a['city_name']); ?></span></td>
                                                    <td><?php echo display_rating_stars($a['avg_rating']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmRemove(<?php echo $a['id']; ?>, '<?php echo addslashes($a['name']); ?>')">
                                                            <i class="fas fa-times"></i> إزالة
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>حفظ الترتيب
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا يوجد معالم مميزة حالياً</p>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFeaturedModal">
                                    إضافة أول معلم
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal إضافة معلم -->
<div class="modal fade" id="addFeaturedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="action" value="add_featured">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة معلم للمميزة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اختر المعلم</label>
                        <select name="attraction_id" class="form-select select2-modal" required style="width: 100%;">
                            <option value="">-- اختر --</option>
                            <?php foreach ($non_featured as $nf): ?>
                                <option value="<?php echo $nf['id']; ?>">
                                    <?php echo htmlspecialchars($nf['city_name'] . ' - ' . $nf['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form للإزالة -->
<form id="removeForm" action="" method="POST" style="display:none;">
    <input type="hidden" name="action" value="remove_featured">
    <input type="hidden" name="id" id="removeId">
</form>

<?php include '../includes/footer.php'; ?>

<script>
function confirmRemove(id, name) {
    if (confirm('هل أنت متأكد من إزالة "' + name + '" من قائمة المميزة؟')) {
        document.getElementById('removeId').value = id;
        document.getElementById('removeForm').submit();
    }
}

// تفعيل Select2 داخل المودال إذا كانت المكتبة موجودة
document.addEventListener("DOMContentLoaded", function() {
    // نستخدم setTimeout لضمان تحميل المكتبات في الفوتر
    setTimeout(function() {
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            $('.select2-modal').select2({
                dropdownParent: $('#addFeaturedModal'),
                dir: "rtl",
                width: '100%'
            });
        }
    }, 100);
});
</script>
