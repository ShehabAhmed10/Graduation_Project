<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'yemen_tourism_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// عناوين URLs
define('BASE_URL', 'http://localhost/YemenTourismProject/backend/');
define('API_BASE_URL', BASE_URL . 'api/');
define('ADMIN_BASE_URL', BASE_URL . 'admin/');

// مسارات الملفات
define('PROJECT_ROOT', dirname(dirname(__FILE__)));
define('UPLOADS_PATH', PROJECT_ROOT . '/uploads/');
define('UPLOADS_URL', BASE_URL . 'uploads/');

// إعدادات أخرى
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
define('SESSION_TIMEOUT', 3600);
define('ENVIRONMENT', 'development');

// تفعيل عرض الأخطاء
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('Asia/Aden');
?>