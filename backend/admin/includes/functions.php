<?php
/**
 * ملف الدوال المساعدة للداشبورد
 */

// دوال مساعدة لبناء روابط Admin مطلقة
if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        $path = ltrim($path, '/');
        if (defined('ADMIN_BASE_URL')) {
            return ADMIN_BASE_URL . $path;
        }
        return $path;
    }
}

if (!function_exists('admin_asset')) {
    function admin_asset($path = '') {
        $path = ltrim($path, '/');
        if (defined('ADMIN_ASSETS_URL')) {
            return ADMIN_ASSETS_URL . $path;
        }
        if (defined('ADMIN_BASE_URL')) {
            return ADMIN_BASE_URL . 'assets/' . $path;
        }
        return 'assets/' . $path;
    }
}

/**
 * دالة لعرض خبز الزبيبة (Breadcrumb)
 */
function display_breadcrumb($items = []) {
    if (empty($items)) {
        // خبز زبيبة افتراضي
        $current_page = basename($_SERVER['PHP_SELF']);
        $items = [
            ['title' => 'لوحة التحكم', 'url' => 'index.php']
        ];
        
        if ($current_page != 'index.php') {
            $items[] = ['title' => get_page_title($current_page), 'url' => ''];
        }
    }
    
    $html = '<nav aria-label="breadcrumb">';
    $html .= '<ol class="breadcrumb">';
    
    foreach ($items as $index => $item) {
        $is_last = ($index == count($items) - 1);
        $active_class = $is_last ? 'active' : '';
        
        $html .= '<li class="breadcrumb-item ' . $active_class . '">';
        if (!$is_last && !empty($item['url'])) {
            $html .= '<a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a>';
        } else {
            $html .= htmlspecialchars($item['title']);
        }
        $html .= '</li>';
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * دالة للحصول على عنوان الصفحة من اسمها
 */
function get_page_title($page_name) {
    $titles = [
        'index.php' => 'لوحة التحكم',
        'cities/index.php' => 'المدن',
        'cities/create.php' => 'إضافة مدينة',
        'cities/edit.php' => 'تعديل مدينة',
        'locations/index.php' => 'المواقع',
        'locations/create.php' => 'إضافة موقع',
        'locations/edit.php' => 'تعديل موقع',
        'attraction_types/index.php' => 'أنواع المعالم',
        'attraction_types/create.php' => 'إضافة نوع',
        'attraction_types/edit.php' => 'تعديل نوع',
        'attractions/index.php' => 'المعالم السياحية',
        'attractions/create.php' => 'إضافة معلم',
        'attractions/edit.php' => 'تعديل معلم',
        'hotels/index.php' => 'الفنادق',
        'hotels/create.php' => 'إضافة فندق',
        'hotels/edit.php' => 'تعديل فندق',
        'users/index.php' => 'المستخدمين',
        'users/edit.php' => 'تعديل مستخدم',
        'reviews/index.php' => 'التقييمات',
        'reviews/moderate.php' => 'مراجعة التقييمات',
        'settings/index.php' => 'الإعدادات',
        'notifications/index.php' => 'الإشعارات',
        'notifications/create.php' => 'إرسال إشعار',
        'profile.php' => 'الملف الشخصي'
    ];
    
    return $titles[$page_name] ?? 'لوحة التحكم';
}

/**
 * دالة لإنشاء زر الإضافة
 */
function create_add_button($url, $text = 'إضافة جديد') {
    // تحويل الروابط النسبية إلى روابط إدارية مطلقة
    if (!preg_match('#^https?://#i', $url) && strpos($url, '/') !== 0) {
        $url = admin_url($url);
    }

    return '<a href="' . htmlspecialchars($url) . '" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>' . htmlspecialchars($text) . '
            </a>';
}

/**
 * دالة لإنشاء أزرار الإجراءات في الجداول
 */
function create_action_buttons($edit_url = null, $delete_url = null, $view_url = null, $item_name = '') {
    $buttons = '<div class="btn-group" role="group">';
    
    // تحويل الروابط النسبية إلى روابط إدارية مطلقة
    foreach (['view_url','edit_url','delete_url'] as $var) {
        if (!empty($$var) && !preg_match('#^https?://#i', $$var) && strpos($$var, '/') !== 0) {
            $$var = admin_url($$var);
        }
    }

    if ($view_url) {
        $buttons .= '<a href="' . htmlspecialchars($view_url) . '" class="btn btn-sm btn-info" 
                     data-bs-toggle="tooltip" title="عرض">
                        <i class="fas fa-eye"></i>
                    </a>';
    }
    
    if ($edit_url) {
        $buttons .= '<a href="' . htmlspecialchars($edit_url) . '" class="btn btn-sm btn-warning" 
                     data-bs-toggle="tooltip" title="تعديل">
                        <i class="fas fa-edit"></i>
                    </a>';
    }
    
    if ($delete_url) {
        $buttons .= '<a href="' . htmlspecialchars($delete_url) . '" 
                     class="btn btn-sm btn-danger delete-btn" 
                     data-url="' . htmlspecialchars($delete_url) . '" 
                     data-name="' . htmlspecialchars($item_name) . '"
                     data-bs-toggle="tooltip" title="حذف">
                        <i class="fas fa-trash"></i>
                    </a>';
    }
    
    $buttons .= '</div>';
    return $buttons;
}

/**
 * دالة لعرض حالة العنصر (نشط/غير نشط)
 */
function display_status_badge($status) {
    if ($status == 1 || $status === true || $status == 'active') {
        return '<span class="badge bg-success">نشط</span>';
    } else {
        return '<span class="badge bg-danger">غير نشط</span>';
    }
}

/**
 * دالة لعرض نجمة التقييم
 */
function display_rating_stars($rating, $max = 5) {
    $html = '<div class="rating-stars">';
    for ($i = 1; $i <= $max; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * دالة لعرض صورة مع صورة افتراضية
 */
function display_image($image_url, $alt = 'صورة', $default_icon = 'fas fa-image', $size = '50px') {
    if (empty($image_url)) {
        return '<div class="default-image" style="width: ' . $size . '; height: ' . $size . '; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 4px; color: #6c757d;">\n                    <i class="' . $default_icon . ' fa-2x"></i>\n                </div>';
    }

    // إذا كانت URL مباشرة
    if (filter_var($image_url, FILTER_VALIDATE_URL)) {
        $src = $image_url;
        return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" style="width: ' . $size . '; height: ' . $size . '; object-fit: cover; border-radius: 4px;">';
    }

    // إذا كانت مسار ملف محلي
    if (file_exists($image_url)) {
        // حاول تحويل المسار إلى URL إذا كان داخل PROJECT_ROOT/uploads
        if (defined('PROJECT_ROOT') && defined('UPLOADS_URL') && strpos($image_url, PROJECT_ROOT) === 0) {
            $relative = ltrim(str_replace(PROJECT_ROOT, '', $image_url), '\/');
            $src = rtrim(UPLOADS_URL, '/') . '/' . $relative;
        } else {
            $src = $image_url;
        }

        return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" style="width: ' . $size . '; height: ' . $size . '; object-fit: cover; border-radius: 4px;">';
    }

    // افتراضي
    return '<div class="default-image" style="width: ' . $size . '; height: ' . $size . '; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 4px; color: #6c757d;">\n                    <i class="' . $default_icon . ' fa-2x"></i>\n                </div>';
}

/**
 * دالة لتنسيق التاريخ بالعربية
 */
function format_date_arabic($date_string) {
    if (empty($date_string)) return '';
    
    $months = [
        'January' => 'يناير',
        'February' => 'فبراير',
        'March' => 'مارس',
        'April' => 'أبريل',
        'May' => 'مايو',
        'June' => 'يونيو',
        'July' => 'يوليو',
        'August' => 'أغسطس',
        'September' => 'سبتمبر',
        'October' => 'أكتوبر',
        'November' => 'نوفمبر',
        'December' => 'ديسمبر'
    ];
    
    $date = new DateTime($date_string);
    $english_month = $date->format('F');
    $arabic_month = $months[$english_month] ?? $english_month;
    
    return $date->format('d') . ' ' . $arabic_month . ' ' . $date->format('Y - h:i A');
}

/**
 * دالة لتنفيذ Pagination
 */
function paginate($total_items, $current_page, $items_per_page, $url_pattern) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // زر السابق
    if ($current_page > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '">السابق</a>';
        $html .= '</li>';
    }
    
    // الأرقام
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // زر التالي
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '">التالي</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * دالة للتحقق من وجود ملف وتحويله إلى رابط تحميل
 */
function file_download_link($file_path, $file_name = null) {
    // إذا كان رابط URL مباشر
    if (empty($file_path)) {
        return '<span class="text-muted">لا يوجد ملف</span>';
    }

    if (filter_var($file_path, FILTER_VALIDATE_URL)) {
        $display_name = $file_name ?: basename(parse_url($file_path, PHP_URL_PATH));
        return '<a href="' . htmlspecialchars($file_path) . '" class="file-download-link" download="' . htmlspecialchars($display_name) . '"><i class="fas fa-download me-1"></i>' . htmlspecialchars($display_name) . '</a>';
    }

    if (!file_exists($file_path)) {
        return '<span class="text-muted">لا يوجد ملف</span>';
    }

    $display_name = $file_name ?: basename($file_path);
    $file_size = filesize($file_path);
    $formatted_size = format_file_size($file_size);

    // إذا كان داخل مجلد UPLOADS، حوّل إلى رابط
    if (defined('PROJECT_ROOT') && defined('UPLOADS_URL') && strpos($file_path, PROJECT_ROOT) === 0) {
        $relative = ltrim(str_replace(PROJECT_ROOT, '', $file_path), '\/');
        $href = rtrim(UPLOADS_URL, '/') . '/' . $relative;
    } else {
        $href = $file_path;
    }

    return '<a href="' . htmlspecialchars($href) . '" 
                class="file-download-link" 
                download="' . htmlspecialchars($display_name) . '"
                data-bs-toggle="tooltip" title="' . $formatted_size . '">
                <i class="fas fa-download me-1"></i>' . htmlspecialchars($display_name) . '
            </a>';
}

/**
 * دالة لتنسيق حجم الملف
 */  
function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' جيجابايت';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' ميجابايت';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' كيلوبايت';
    } else {
        return $bytes . ' بايت';
    }
}

/**
 * دالة لإنشاء إشعار لمستخدم
 */
if (!function_exists('create_notification')) {
    function create_notification($user_id, $title, $body) {
        // تحقق من صحة المعطيات
        $user_id = intval($user_id);
        if ($user_id <= 0) return false;

        // استخدم db_execute الموجود في المشروع
        try {
            db_execute("INSERT INTO notifications (user_id, title, body, is_read, created_at) VALUES (?, ?, ?, 0, NOW())", [$user_id, $title, $body]);
            return true;
        } catch (Exception $e) {
            error_log('create_notification error: ' . $e->getMessage());
            return false;
        }
    }
}

