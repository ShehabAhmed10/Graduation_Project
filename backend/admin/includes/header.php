<?php
/**
 * ملف رأس الصفحة (Header)
 * يحتوي على العلامات الأساسية وروابط CSS
 */
 
// تضمين ملف التكوين العام (يضمن تعريف BASE_URL و ADMIN_BASE_URL)
require_once __DIR__ . '/../../config/config.php';

// الحصول على عنوان الصفحة الحالي
$page_title = $page_title ?? 'لوحة التحكم - Yemen Tourism';

// تعريف مسارات ثابتة للـ Admin إذا لم تُعرف من قبل
if (!defined('ADMIN_ASSETS_URL')) {
    define('ADMIN_ASSETS_URL', ADMIN_BASE_URL . 'assets/');
}

// تضمين دوال المساعدة الخاصة بالـ Admin
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts (Cairo) -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Custom CSS Files -->
    <link rel="stylesheet" href="<?php echo ADMIN_ASSETS_URL; ?>css/dashboard.css">
    <link rel="stylesheet" href="<?php echo ADMIN_ASSETS_URL; ?>css/layout.css">
    <link rel="stylesheet" href="<?php echo ADMIN_ASSETS_URL; ?>css/rtl.css">
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'login.php'): ?>
        <link rel="stylesheet" href="<?php echo ADMIN_ASSETS_URL; ?>css/login.css">
    <?php endif; ?>
    
    <style>
        /* تعطيل Bootstrap RTL حتى تعمل ملفاتنا */
        [dir="rtl"] .dropdown-menu {
            text-align: right;
            left: auto;
            right: 0;
        }
        
        [dir="rtl"] .modal-header .btn-close {
            margin: -0.5rem auto -0.5rem -0.5rem;
        }
    </style>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ADMIN_ASSETS_URL; ?>img/favicon.ico">
</head>
<body>
    <!-- المحتوى سيضاف هنا -->