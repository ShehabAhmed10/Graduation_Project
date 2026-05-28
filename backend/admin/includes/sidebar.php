<?php
/**
 * ملف القائمة الجانبية للداشبورد
 */
// استخدم المسار الكامل من REQUEST_URI لتحسين اكتشاف القسم الحالي
$current_path = $_SERVER['REQUEST_URI'] ?? '';
?>
<!-- الشريط الجانبي -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <h2 class="sidebar-title">Yemen Tourism</h2>
        <p class="sidebar-subtitle">لوحة التحكم</p>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="<?php echo admin_url('index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/index.php') !== false || strpos($current_path, '/admin/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-home nav-icon"></i>
                    <span>لوحة التحكم</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <span class="nav-section-title">إدارة المحتوى</span>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('cities/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/cities/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-city nav-icon"></i>
                    <span>المدن</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('locations/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/locations/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt nav-icon"></i>
                    <span>المواقع</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('attraction_types/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/attraction_types/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-tags nav-icon"></i>
                    <span>أنواع المعالم</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('attractions/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/attractions/') !== false && strpos($current_path, '/featured.php') === false && strpos($current_path, '/attraction_types/') === false) ? 'active' : ''; ?>">
                    <i class="fas fa-landmark nav-icon"></i>
                    <span>المعالم السياحية</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('attractions/featured.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/featured.php') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-star nav-icon text-warning"></i>
                    <span>المعالم المميزة</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('hotels/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/hotels/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-hotel nav-icon"></i>
                    <span>الفنادق</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <span class="nav-section-title">إدارة المستخدمين</span>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('users/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/users/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-users nav-icon"></i>
                    <span>المستخدمين</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('reviews/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/reviews/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-star nav-icon"></i>
                    <span>التقييمات</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <span class="nav-section-title">الإعدادات</span>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('settings/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/settings/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-cog nav-icon"></i>
                    <span>الإعدادات العامة</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('notifications/index.php'); ?>" class="nav-link <?php echo (strpos($current_path, '/notifications/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-bell nav-icon"></i>
                    <span>الإشعارات</span>
                    <span class="badge bg-danger notification-badge">3</span>
                </a>
            </li>
        </ul>
    </nav>
    
     
</aside>