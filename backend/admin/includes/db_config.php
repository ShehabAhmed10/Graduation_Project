<?php
/**
 * ملف اتصال قاعدة البيانات للداشبورد
 */

// تضمين ملف اتصال قاعدة البيانات الرئيسي
require_once __DIR__ . '/../../config/db.php';

/**
 * دالة لتنفيذ استعلام SELECT
 */
function db_select($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database select error: " . $e->getMessage() . " - SQL: " . $sql);
        return false;
    }
}

/**
 * دالة لتنفيذ استعلام SELECT للحصول على سجل واحد
 */
function db_fetch($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database fetch error: " . $e->getMessage() . " - SQL: " . $sql);
        return false;
    }
}

/**
 * دالة لتنفيذ استعلام INSERT/UPDATE/DELETE
 */
function db_execute($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Database execute error: " . $e->getMessage() . " - SQL: " . $sql);
        return false;
    }
}

/**
 * دالة للحصول على آخر معرف مضاف
 */
function db_last_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * دالة للتحقق من وجود سجل
 */
function db_exists($table, $conditions = [], $params = []) {
    $sql = "SELECT COUNT(*) as count FROM {$table}";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $result = db_fetch($sql, $params);
    return $result && $result['count'] > 0;
}

/**
 * دالة للحصول على عدد السجلات
 */
function db_count($table, $conditions = [], $params = []) {
    $sql = "SELECT COUNT(*) as count FROM {$table}";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $result = db_fetch($sql, $params);
    return $result ? (int)$result['count'] : 0;
}

/**
 * دالة للتحقق من صحة البيانات المدخلة
 */
function validate_input($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule_set) {
        $value = $data[$field] ?? '';
        $field_errors = [];
        
        foreach ($rule_set as $rule) {
            $rule_parts = explode(':', $rule);
            $rule_name = $rule_parts[0];
            $rule_value = $rule_parts[1] ?? null;
            
            switch ($rule_name) {
                case 'required':
                    if (empty(trim($value))) {
                        $field_errors[] = "حقل {$field} مطلوب";
                    }
                    break;
                    
                case 'min':
                    if (strlen(trim($value)) < $rule_value) {
                        $field_errors[] = "حقل {$field} يجب أن يكون {$rule_value} أحرف على الأقل";
                    }
                    break;
                    
                case 'max':
                    if (strlen(trim($value)) > $rule_value) {
                        $field_errors[] = "حقل {$field} يجب أن يكون {$rule_value} أحرف على الأكثر";
                    }
                    break;
                    
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $field_errors[] = "بريد إلكتروني غير صالح";
                    }
                    break;
                    
                case 'numeric':
                    if (!is_numeric($value)) {
                        $field_errors[] = "حقل {$field} يجب أن يكون رقماً";
                    }
                    break;
                    
                case 'unique':
                    $table = $rule_parts[1];
                    $column = $rule_parts[2];
                    $except_id = $rule_parts[3] ?? null;
                    
                    $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
                    $params = [$value];
                    
                    if ($except_id) {
                        $sql .= " AND id != ?";
                        $params[] = $except_id;
                    }
                    
                    $result = db_fetch($sql, $params);
                    if ($result && $result['count'] > 0) {
                        $field_errors[] = "قيمة {$field} مستخدمة مسبقاً";
                    }
                    break;
            }
        }
        
        if (!empty($field_errors)) {
            $errors[$field] = implode('، ', $field_errors);
        }
    }
    
    return $errors;
}

/**
 * دالة لتنقية النصوص العربية
 */
function sanitize_arabic($text) {
    if (empty($text)) return $text;
    
    // تحويل الترميز إلى UTF-8
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    
    // إزالة المسافات الزائدة
    $text = trim($text);
    
    // إزالة الأحرف الخاصة الخطيرة
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    return $text;
}
?>