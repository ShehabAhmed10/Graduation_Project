<?php
/**
 * ملف رفع وإدارة الملفات
 */

// منع الوصول المباشر
if (!defined('PROJECT_ROOT')) {
    die('Direct access not permitted');
}

/**
 * رفع صورة إلى المجلد المحدد
 */
function upload_image($file, $destination_dir, $filename = null) {
    // تضمين ملف التحقق
    require_once __DIR__ . '/validation.php';
    
    // التحقق من وجود أخطاء في الرفع
    $validation_errors = validate_image_upload($file);
    if (!empty($validation_errors)) {
        return [
            'success' => false,
            'errors' => $validation_errors
        ];
    }
    
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!file_exists($destination_dir)) {
        if (!mkdir($destination_dir, 0755, true)) {
            return [
                'success' => false,
                'errors' => ['تعذر إنشاء المجلد المطلوب']
            ];
        }
    }
    
    // توليد اسم ملف فريد إذا لم يتم تحديده
    if (!$filename) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
    }
    
    $destination_path = rtrim($destination_dir, '/') . '/' . $filename;
    
    // نقل الملف إلى المجلد المحدد
    if (!move_uploaded_file($file['tmp_name'], $destination_path)) {
        return [
            'success' => false,
            'errors' => ['تعذر حفظ الملف']
        ];
    }
    
    // التحقق من أن الملف صورة (لأمان إضافي)
    if (!getimagesize($destination_path)) {
        unlink($destination_path);
        return [
            'success' => false,
            'errors' => ['الملف ليس صورة صالحة']
        ];
    }
    
    return [
        'success' => true,
        'filename' => $filename,
        'path' => $destination_path,
        // relative path under uploads (e.g. attractions/xxxx.jpg)
        'relative_path' => ltrim(str_replace(rtrim(UPLOADS_PATH, '/'), '', $destination_path), '/'),
        'url' => rtrim(UPLOADS_URL, '/') . '/' . ltrim(str_replace(rtrim(UPLOADS_PATH, '/'), '', $destination_path), '/')
    ];
}

/**
 * رفع صورة المعلم السياحي
 */
function upload_attraction_image($file) {
    $destination_dir = UPLOADS_PATH . 'attractions/';
    return upload_image($file, $destination_dir);
}

/**
 * رفع صورة الفندق
 */
function upload_hotel_image($file) {
    $destination_dir = UPLOADS_PATH . 'hotels/';
    return upload_image($file, $destination_dir);
}

/**
 * رفع صورة المستخدم (الصورة الشخصية)
 */
function upload_user_avatar($file) {
    $destination_dir = UPLOADS_PATH . 'users/';
    return upload_image($file, $destination_dir);
}

/**
 * حذف ملف
 */
function delete_file($file_path) {
    if (file_exists($file_path) && is_file($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * إنشاء نسخة مصغرة من الصورة
 */
function create_thumbnail($source_path, $destination_path, $width = 300, $height = 300) {
    // التحقق من أن الملف موجود
    if (!file_exists($source_path)) {
        return false;
    }
    
    // الحصول على معلومات الصورة
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        return false;
    }
    
    $original_width = $image_info[0];
    $original_height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    // حساب النسب للحفاظ على الشكل
    $ratio = min($width / $original_width, $height / $original_height);
    $new_width = (int)($original_width * $ratio);
    $new_height = (int)($original_height * $ratio);
    
    // إنشاء الصورة الأصلية
    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source_image) {
        return false;
    }
    
    // إنشاء الصورة الجديدة
    $thumbnail = imagecreatetruecolor($new_width, $new_height);
    
    // الحفاظ على الشفافية للصور PNG و GIF
    if ($mime_type == 'image/png' || $mime_type == 'image/gif') {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }
    
    // نسخ وتغيير حجم الصورة
    imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, 
                      $new_width, $new_height, $original_width, $original_height);
    
    // حفظ الصورة المصغرة
    switch ($mime_type) {
        case 'image/jpeg':
            $result = imagejpeg($thumbnail, $destination_path, 90);
            break;
        case 'image/png':
            $result = imagepng($thumbnail, $destination_path, 9);
            break;
        case 'image/gif':
            $result = imagegif($thumbnail, $destination_path);
            break;
        default:
            $result = false;
    }
    
    // تحرير الذاكرة
    imagedestroy($source_image);
    imagedestroy($thumbnail);
    
    return $result;
}

/**
 * الحصول على حجم الملف بشكل مقروء
 */
if (!function_exists('format_file_size')) {
    function format_file_size($bytes) {
        $bytes = max(0, (float)$bytes);

        if ($bytes >= 1073741824) { // >= 1 GB
            return number_format($bytes / 1073741824, 2) . ' جيجابايت';
        } elseif ($bytes >= 1048576) { // >= 1 MB
            return number_format($bytes / 1048576, 2) . ' ميجابايت';
        } elseif ($bytes >= 1024) { // >= 1 KB
            return number_format($bytes / 1024, 2) . ' كيلوبايت';
        } else {
            return (int)$bytes . ' بايت';
        }
    }
}

/**
 * التحقق من أن الملف صورة آمنة
 */
function is_safe_image($file_path) {
    // التحقق من امتداد الملف
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed_extensions)) {
        return false;
    }
    
    // التحقق من نوع MIME الفعلي
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    return in_array($mime_type, $allowed_mime_types);
}
?>