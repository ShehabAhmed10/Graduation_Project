<?php
/**
 * ملف الشريط العلوي للداشبورد
 */
?>
<!-- الشريط العلوي -->
<header class="topbar">
    <div class="topbar-content">
        <div class="topbar-left">
            <button class="sidebar-toggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title ms-3"><?php echo $page_title ?? 'لوحة التحكم'; ?></h1>
        </div>
        
        <div class="topbar-right">
            <div class="dropdown">
                <button class="btn btn-light btn-icon" type="button" id="notificationsDropdown" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span class="badge bg-danger notification-count">3</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                    <h6 class="dropdown-header">الإشعارات الجديدة</h6>
                    <a class="dropdown-item" href="#">
                        <div class="d-flex align-items-center">
                            <div class="notification-icon bg-primary">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="ms-3">
                                <div class="notification-title">مستخدم جديد</div>
                                <div class="notification-time text-muted">منذ 5 دقائق</div>
                            </div>
                        </div>
                    </a>
                    <a class="dropdown-item" href="#">
                        <div class="d-flex align-items-center">
                            <div class="notification-icon bg-success">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="ms-3">
                                <div class="notification-title">تقييم جديد</div>
                                <div class="notification-time text-muted">منذ ساعة</div>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center" href="<?php echo admin_url('notifications/index.php'); ?>">
                        عرض جميع الإشعارات
                    </a>
                </div>
            </div>
            
            <div class="user-info">
                <div class="dropdown">
                    <button class="btn btn-link text-dark text-decoration-none dropdown-toggle" 
                            type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo $_SESSION['admin_name'] ?? 'مشرف'; ?></div>
                            <div class="user-role">
                                <?php echo ($_SESSION['admin_role'] ?? 'admin') == 'super_admin' ? 'مدير عام' : 'مشرف'; ?>
                            </div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="<?php echo admin_url('profile.php'); ?>">
                                <i class="fas fa-user me-2"></i>الملف الشخصي
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo admin_url('settings/index.php'); ?>">
                                <i class="fas fa-cog me-2"></i>الإعدادات
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo admin_url('logout.php'); ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>