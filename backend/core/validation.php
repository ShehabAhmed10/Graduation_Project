<?php
/**
 * ملف التحقق من صحة المدخلات
 */

// منع الوصول المباشر
if (!defined('PROJECT_ROOT')) {
    die('Direct access not permitted');
}

/**
 * التحقق إذا كان الحقل مطلوباً وغير فارغ
 */
function is_required($value) {
    if (is_array($value)) {
        return !empty($value);
    }
    
    return trim($value) !== '';
}

/**
 * التحقق من صحة البريد الإلكتروني
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * التحقق من صحة الرقم الهاتف (صيغة أساسية)
 */
function is_valid_phone($phone) {
    // صيغة بسيطة للتحقق - يمكن تعديلها حسب الحاجة
    return preg_match('/^[0-9\s\-\+\(\)]{8,20}$/', $phone);
}

/**
 * التحقق إذا كانت القيمة رقمية
 */
function is_numeric_value($value) {
    return is_numeric($value);
}

/**
 * التحقق إذا كانت القيمة عدد صحيح موجب
 */
function is_positive_integer($value) {
    return filter_var($value, FILTER_VALIDATE_INT, 
                     ['options' => ['min_range' => 1]]) !== false;
}

/**
 * التحقق من طول النص
 */
function validate_length($value, $min = 0, $max = 255) {
    $length = mb_strlen($value);
    return $length >= $min && $length <= $max;
}

/**
 * التحقق من نطاق الرقم
 */
function validate_range($value, $min = 0, $max = 100) {
    return $value >= $min && $value <= $max;
}

/**
 * التحقق من صحة كلمة المرور
 */
function validate_password($password) {
    // كلمة المرور يجب أن تكون على الأقل 8 أحرف
    if (strlen($password) < 8) {
        return false;
    }
    
    // يجب أن تحتوي على حرف كبير على الأقل
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // يجب أن تحتوي على رقم على الأقل
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * التحقق من صحة الإحداثيات الجغرافية
 */
function validate_coordinates($lat, $lng) {
    return filter_var($lat, FILTER_VALIDATE_FLOAT) !== false &&
           filter_var($lng, FILTER_VALIDATE_FLOAT) !== false &&
           $lat >= -90 && $lat <= 90 &&
           $lng >= -180 && $lng <= 180;
}

/**
 * التحقق من صحة التقييم (1-5)
 */
function validate_rating($rating) {
    return in_array($rating, [1, 2, 3, 4, 5]);
}

/**
 * التحقق من صحة صيغة التاريخ
 */
function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * دالة التحقق العامة
 */
function validate($rules, $data) {
    $errors = [];
    
    foreach ($rules as $field => $rule_set) {
        $value = $data[$field] ?? '';
        
        foreach ($rule_set as $rule) {
            $rule_parts = explode(':', $rule);
            $rule_name = $rule_parts[0];
            $rule_param = $rule_parts[1] ?? null;
            
            switch ($rule_name) {
                case 'required':
                    if (!is_required($value)) {
                        $errors[$field][] = "حقل {$field} مطلوب";
                    }
                    break;
                    
                case 'email':
                    if (is_required($value) && !is_valid_email($value)) {
                        $errors[$field][] = "البريد الإلكتروني غير صحيح";
                    }
                    break;
                    
                case 'min':
                    if (is_required($value) && strlen($value) < $rule_param) {
                        $errors[$field][] = "حقل {$field} يجب أن يكون على الأقل {$rule_param} أحرف";
                    }
                    break;
                    
                case 'max':
                    if (strlen($value) > $rule_param) {
                        $errors[$field][] = "حقل {$field} يجب أن يكون على الأكثر {$rule_param} أحرف";
                    }
                    break;
                    
                case 'numeric':
                    if (is_required($value) && !is_numeric($value)) {
                        $errors[$field][] = "حقل {$field} يجب أن يكون رقماً";
                    }
                    break;
                    
                case 'in':
                    $allowed_values = explode(',', $rule_param);
                    if (is_required($value) && !in_array($value, $allowed_values)) {
                        $errors[$field][] = "قيمة {$field} غير مسموحة";
                    }
                    break;
            }
        }
    }
    
    return $errors;
}

/**
 * التحقق من صحة ملف رفع الصورة
 */
function validate_image_upload($file) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "حدث خطأ أثناء رفع الملف";
        return $errors;
    }
    
    // التحقق من حجم الملف
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "حجم الملف يجب أن يكون أقل من " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
    }
    
    // التحقق من نوع الملف
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        $errors[] = "نوع الملف غير مسموح. المسموح: JPG, PNG, GIF";
    }
    
    return $errors;
}
?>