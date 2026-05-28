<?php
/**
 * صفحة عرض قائمة المدن
 */

// تضمين ملفات المصادقة والاتصال بقاعدة البيانات
require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

// تعيين عنوان الصفحة
$page_title = 'إدارة المدن - Yemen Tourism';

// معالجة حذف مدينة
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $city_id = intval($_GET['delete']);
    
    try {
        // التحقق إذا كانت المدينة مرتبطة بمواقع
        $check_locations = db_count('locations', ['city_id = ?'], [$city_id]);
        
        if ($check_locations > 0) {
            set_flash_message('error', 'لا يمكن حذف المدينة لأنها مرتبطة بمواقع');
        } else {
            // حذف المدينة
            $delete_sql = "DELETE FROM cities WHERE id = ?";
            $result = db_execute($delete_sql, [$city_id]);
            
            if ($result) {
                set_flash_message('success', 'تم حذف المدينة بنجاح');
            } else {
                set_flash_message('error', 'حدث خطأ أثناء حذف المدينة');
            }
        }
        
        // إعادة التوجيه إلى نفس الصفحة (مسار مطلق)
        header('Location: ' . admin_url('cities/index.php'));
        exit();
        
    } catch (PDOException $e) {
        set_flash_message('error', 'حدث خطأ: ' . $e->getMessage());
        header('Location: ' . admin_url('cities/index.php'));
        exit();
    }
}

// الحصول على معلمات البحث والترتيب
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // عدد العناصر في كل صفحة
$offset = ($page - 1) * $limit;

// بناء استعلام SQL مع الفلاتر
$sql = "SELECT * FROM cities WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($status === 'active') {
    $sql .= " AND is_active = 1";
} elseif ($status === 'inactive') {
    $sql .= " AND is_active = 0";
}

// الحصول على العدد الكلي للنتائج
$count_sql = "SELECT COUNT(*) as total FROM cities WHERE 1=1";
$count_params = [];

if (!empty($search)) {
    $count_sql .= " AND (name LIKE ? OR description LIKE ?)";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
}

if ($status === 'active') {
    $count_sql .= " AND is_active = 1";
} elseif ($status === 'inactive') {
    $count_sql .= " AND is_active = 0";
}

$total_result = db_fetch($count_sql, $count_params);
$total_items = $total_result['total'] ?? 0;
$total_pages = ceil($total_items / $limit);

// إضافة الترتيب والحد
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// جلب البيانات
$cities = db_select($sql, $params);

// تضمين الهيدر
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>
        
        <main class="main-content">
            <div class="container-fluid">
                <!-- عرض رسائل الفلاش -->
                <?php echo display_flash_messages(); ?>
                
                <!-- رأس الصفحة مع زر الإضافة -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-2">إدارة المدن</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo admin_url('index.php'); ?>">لوحة التحكم</a></li>
                                <li class="breadcrumb-item active">المدن</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('cities/create.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>إضافة مدينة جديدة
                        </a>
                    </div>
                </div>
                
                <!-- بطاقة البحث والتصفية -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           placeholder="ابحث باسم المدينة أو الوصف..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">بحث</button>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">جميع الحالات</option>
                                    <option value="active" <?php echo ($status === 'active') ? 'selected' : ''; ?>>نشط فقط</option>
                                    <option value="inactive" <?php echo ($status === 'inactive') ? 'selected' : ''; ?>>غير نشط فقط</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('cities/index.php'); ?>" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- بطاقة عرض البيانات -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">قائمة المدن</h5>
                        <span class="badge bg-primary"><?php echo $total_items; ?> مدينة</span>
                    </div>
                    
                    <div class="card-body">
                        <?php if (!empty($cities)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="60">#</th>
                                            <th>اسم المدينة</th>
                                            <th>الوصف</th>
                                            <th>الحالة</th>
                                            <th>تاريخ الإضافة</th>
                                            <th width="120">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cities as $index => $city): 
                                            $row_number = $offset + $index + 1;
                                        ?>
                                            <tr>
                                                <td><?php echo $row_number; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($city['name']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $description = $city['description'] ?? '';
                                                    if (!empty($description)) {
                                                        echo strlen($description) > 100 
                                                            ? substr(htmlspecialchars($description), 0, 100) . '...' 
                                                            : htmlspecialchars($description);
                                                    } else {
                                                        echo '<span class="text-muted">لا يوجد وصف</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo display_status_badge($city['is_active']); ?>
                                                </td>
                                                <td>
                                                    <?php echo format_date_arabic($city['created_at']); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?php echo admin_url('cities/edit.php?id=' . $city['id']); ?>" 
                                                           class="btn btn-outline-warning" 
                                                           data-bs-toggle="tooltip" 
                                                           title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger delete-btn" 
                                                                data-url="<?php echo admin_url('cities/index.php?delete=' . $city['id']); ?>" 
                                                                data-name="<?php echo htmlspecialchars($city['name']); ?>"
                                                                data-bs-toggle="tooltip" 
                                                                title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- الترقيم (Pagination) -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <!-- زر السابق -->
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" 
                                               href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">
                                                السابق
                                            </a>
                                        </li>
                                        
                                        <!-- أرقام الصفحات -->
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link" 
                                                   href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <!-- زر التالي -->
                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" 
                                               href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">
                                                التالي
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <!-- حالة عدم وجود بيانات -->
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-city fa-4x text-muted mb-3"></i>
                                    <h4>لا توجد مدن</h4>
                                    <p class="text-muted mb-4">لم يتم إضافة أي مدينة بعد.</p>
                                    <a href="create.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>إضافة أول مدينة
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- تذييل البطاقة -->
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    عرض <?php echo count($cities); ?> من <?php echo $total_items; ?> مدينة
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">
                                    الصفحة <?php echo $page; ?> من <?php echo $total_pages; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <footer class="main-footer">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start text-center">
                        <p class="mb-0">© 2025 Yemen Tourism Guide System</p>
                    </div>
                    <div class="col-md-6 text-md-end text-center">
                        <p class="mb-0">إدارة المدن</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<?php include '../includes/footer.php'; ?>