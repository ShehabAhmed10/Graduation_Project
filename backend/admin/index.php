 <?php
/**
 * الداشبورد الرئيسي
 */

// تضمين ملفات المصادقة والاتصال بقاعدة البيانات
require_once 'includes/auth_check.php';
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

// الحصول على الإحصائيات
try {
    // عدد المستخدمين
    $users_count = db_count('users', ['is_active = ?'], [1]);
    
    // عدد المعالم
    $attractions_count = db_count('attractions', ['is_active = ?'], [1]);
    
    // عدد التقييمات المقبولة
    $reviews_count = db_count('reviews', ['status = ?'], ['approved']);
    
    // عدد الفنادق
    $hotels_count = db_count('hotels', ['is_active = ?'], [1]);
    
    // عدد المدن
    $cities_count = db_count('cities', ['is_active = ?'], [1]);
    
    // آخر 5 مستخدمين مسجلين
    $recent_users = db_select("SELECT id, full_name, email, created_at 
                               FROM users 
                               WHERE is_active = 1 
                               ORDER BY created_at DESC 
                               LIMIT 5");
    
    // آخر 5 معالم مضافة
    $recent_attractions = db_select("SELECT a.id, a.name, a.created_at, c.name as city_name
                                     FROM attractions a
                                     INNER JOIN locations l ON a.location_id = l.id
                                     INNER JOIN cities c ON l.city_id = c.id
                                     WHERE a.is_active = 1
                                     ORDER BY a.created_at DESC 
                                     LIMIT 5");
    
    // آخر 5 تقييمات
    $recent_reviews = db_select("SELECT r.id, r.rating, r.comment, r.created_at, 
                                        a.name as attraction_name, u.full_name as user_name
                                 FROM reviews r
                                 INNER JOIN attractions a ON r.attraction_id = a.id
                                 INNER JOIN users u ON r.user_id = u.id
                                 WHERE r.status = 'approved'
                                 ORDER BY r.created_at DESC 
                                 LIMIT 5");
    
} catch (PDOException $e) {
    $error_message = 'حدث خطأ في جلب الإحصائيات: ' . $e->getMessage();
}

// تعيين عنوان الصفحة
$page_title = 'لوحة التحكم - Yemen Tourism';

// تضمين الهيدر
include 'includes/header.php';
?>

<!-- البنية الرئيسية -->
<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-wrapper">
        <?php include 'includes/topbar.php'; ?>
        
        <main class="main-content">
            <div class="container-fluid">
                <!-- عرض رسائل الفلاش -->
                <?php 
                if (function_exists('display_flash_messages')) {
                    echo display_flash_messages();
                }
                ?>
                
                <!-- باقي المحتوى ... -->
            
            <!-- الإحصائيات السريعة -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card users">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted mb-1">المستخدمين</h5>
                                    <h2 class="mb-0"><?php echo $users_count ?? 0; ?></h2>
                                    <small class="text-muted">إجمالي المستخدمين المسجلين</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card attractions">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted mb-1">المعالم</h5>
                                    <h2 class="mb-0"><?php echo $attractions_count ?? 0; ?></h2>
                                    <small class="text-muted">معلم سياحي مفعل</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-landmark"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card reviews">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted mb-1">التقييمات</h5>
                                    <h2 class="mb-0"><?php echo $reviews_count ?? 0; ?></h2>
                                    <small class="text-muted">تقييم مقبول</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card hotels">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted mb-1">الفنادق</h5>
                                    <h2 class="mb-0"><?php echo $hotels_count ?? 0; ?></h2>
                                    <small class="text-muted">فندق مفعل</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-hotel"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- الصف الثاني: الجداول والإحصائيات -->
            <div class="row">
                <!-- آخر المستخدمين -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">آخر المستخدمين المسجلين</h5>
                            <a href="<?php echo admin_url('users/index.php'); ?>" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_users)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>تاريخ التسجيل</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_users as $user): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="user-avatar-small me-2">
                                                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                            </div>
                                                            <?php echo htmlspecialchars($user['full_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo format_date_arabic($user['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا يوجد مستخدمين مسجلين بعد</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- آخر المعالم -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">آخر المعالم المضافة</h5>
                            <a href="<?php echo admin_url('attractions/index.php'); ?>" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_attractions)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>اسم المعلم</th>
                                                <th>المدينة</th>
                                                <th>تاريخ الإضافة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_attractions as $attraction): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($attraction['name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($attraction['city_name']); ?></span>
                                                    </td>
                                                    <td><?php echo format_date_arabic($attraction['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-landmark fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا يوجد معالم مضافة بعد</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- الصف الثالث: آخر التقييمات -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">آخر التقييمات</h5>
                            <a href="<?php echo admin_url('reviews/index.php'); ?>" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_reviews)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>المستخدم</th>
                                                <th>المعلم</th>
                                                <th>التقييم</th>
                                                <th>التعليق</th>
                                                <th>التاريخ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_reviews as $review): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($review['user_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($review['attraction_name']); ?></td>
                                                    <td><?php echo display_rating_stars($review['rating']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $comment = htmlspecialchars($review['comment']);
                                                        echo strlen($comment) > 50 ? substr($comment, 0, 50) . '...' : $comment;
                                                        ?>
                                                    </td>
                                                    <td><?php echo format_date_arabic($review['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا يوجد تقييمات بعد</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <footer class="main-footer">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start text-center">
                        <p class="mb-0">© 2025 Yemen Tourism Guide System. جميع الحقوق محفوظة</p>
                    </div>
                    <div class="col-md-6 text-md-end text-center">
                        <p class="mb-0">آخر تحديث: <?php echo date('Y/m/d h:i A'); ?></p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<?php include 'includes/footer.php'; ?>