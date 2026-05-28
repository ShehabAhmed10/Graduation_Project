<?php
/**
 * صفحة تعديل مدينة
 */

// تضمين ملفات المصادقة والاتصال بقاعدة البيانات
require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

// التحقق من وجود معرف المدينة
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'معرف المدينة غير محدد');
    header('Location: index.php');
    exit();
}

$city_id = intval($_GET['id']);

// جلب بيانات المدينة الحالية
$city = db_fetch("SELECT * FROM cities WHERE id = ?", [$city_id]);

// تحقق ما إذا كان الحقل updated_at موجوداً في الصف
$has_updated_at = is_array($city) && array_key_exists('updated_at', $city);

if (!$city) {
    set_flash_message('error', 'لم يتم العثور على المدينة');
    header('Location: index.php');
    exit();
}

// تعيين عنوان الصفحة
$page_title = 'تعديل مدينة - Yemen Tourism';

// تهيئة المتغيرات
$errors = [];
$form_data = [
    'name' => $city['name'] ?? '',
    'description' => $city['description'] ?? '',
    'is_active' => $city['is_active'] ?? 1
];

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // جمع البيانات من النموذج
    $form_data['name'] = trim($_POST['name'] ?? '');
    $form_data['description'] = trim($_POST['description'] ?? '');
    $form_data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // التحقق من صحة البيانات
    if (empty($form_data['name'])) {
        $errors['name'] = 'اسم المدينة مطلوب';
    } elseif (strlen($form_data['name']) < 2) {
        $errors['name'] = 'اسم المدينة يجب أن يكون على الأقل حرفين';
    } elseif (strlen($form_data['name']) > 100) {
        $errors['name'] = 'اسم المدينة يجب أن يكون أقل من 100 حرف';
    }
    
    if (strlen($form_data['description']) > 500) {
        $errors['description'] = 'الوصف يجب أن يكون أقل من 500 حرف';
    }
    
    // التحقق من عدم تكرار اسم المدينة (استثناء المدينة الحالية)
    if (empty($errors['name'])) {
        $check_sql = "SELECT id FROM cities WHERE name = ? AND id != ?";
        $existing = db_fetch($check_sql, [$form_data['name'], $city_id]);
        
        if ($existing) {
            $errors['name'] = 'اسم المدينة موجود مسبقاً';
        }
    }
    
    // إذا لم توجد أخطاء، تحديث البيانات
    if (empty($errors)) {
        try {
            if ($has_updated_at) {
                $update_sql = "UPDATE cities SET name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
                $params = [
                    $form_data['name'],
                    $form_data['description'],
                    $form_data['is_active'],
                    $city_id
                ];
            } else {
                // جدول المدن لا يحتوي على عمود updated_at في بعض النسخ
                $update_sql = "UPDATE cities SET name = ?, description = ?, is_active = ? WHERE id = ?";
                $params = [
                    $form_data['name'],
                    $form_data['description'],
                    $form_data['is_active'],
                    $city_id
                ];
            }
            $result = db_execute($update_sql, $params);
            
            if ($result) {
                set_flash_message('success', 'تم تحديث المدينة "' . $form_data['name'] . '" بنجاح');
                header('Location: index.php');
                exit();
            } else {
                $errors['general'] = 'لم يتم تحديث أي بيانات. قد تكون البيانات نفسها';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'حدث خطأ في الخادم: ' . $e->getMessage();
        }
    }
}

// تضمين الهيدر
include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>
        
        <main class="main-content">
            <div class="container-fluid">
                <!-- رأس الصفحة -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-2">تعديل مدينة</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo admin_url('index.php'); ?>">لوحة التحكم</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo admin_url('cities/index.php'); ?>">المدن</a></li>
                                <li class="breadcrumb-item active">تعديل مدينة</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('cities/index.php'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>عودة للقائمة
                        </a>
                    </div>
                </div>
                
                <!-- نموذج التعديل -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-edit me-2 text-warning"></i>
                                    تعديل مدينة: <?php echo htmlspecialchars($city['name']); ?>
                                </h5>
                            </div>
                            
                            <div class="card-body">
                                <!-- معلومات المدينة -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <h6 class="text-muted mb-2">معلومات المدينة</h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-1">
                                                    <i class="fas fa-hashtag text-primary me-2"></i>
                                                    <strong>المعرف:</strong> #<?php echo $city['id']; ?>
                                                </li>
                                                <li class="mb-1">
                                                    <i class="fas fa-calendar text-primary me-2"></i>
                                                    <strong>تاريخ الإضافة:</strong> <?php echo format_date_arabic($city['created_at']); ?>
                                                </li>
                                                <?php if ($has_updated_at && $city['updated_at'] != $city['created_at']): ?>
                                                <li>
                                                    <i class="fas fa-history text-primary me-2"></i>
                                                    <strong>آخر تحديث:</strong> <?php echo format_date_arabic($city['updated_at']); ?>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <h6 class="text-muted mb-2">الحالة الحالية</h6>
                                            <div class="d-flex align-items-center">
                                                <?php echo display_status_badge($city['is_active']); ?>
                                                <span class="ms-3">
                                                    <?php echo $city['is_active'] ? 'تظهر في التطبيق' : 'مخفي في التطبيق'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- رسائل الأخطاء -->
                                <?php if (isset($errors['general'])): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php echo htmlspecialchars($errors['general']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- نموذج التعديل -->
                                <form method="POST" action="" id="editCityForm">
                                    <div class="row">
                                        <!-- حقل اسم المدينة -->
                                        <div class="col-md-12 mb-3">
                                            <label for="name" class="form-label">
                                                اسم المدينة <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                                   id="name" 
                                                   name="name" 
                                                   value="<?php echo htmlspecialchars($form_data['name']); ?>" 
                                                   placeholder="أدخل اسم المدينة" 
                                                   required>
                                            <?php if (isset($errors['name'])): ?>
                                                <div class="invalid-feedback">
                                                    <?php echo htmlspecialchars($errors['name']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- حقل الوصف -->
                                        <div class="col-md-12 mb-3">
                                            <label for="description" class="form-label">وصف المدينة</label>
                                            <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                                                      id="description" 
                                                      name="description" 
                                                      rows="4" 
                                                      placeholder="أدخل وصفاً مختصراً عن المدينة..."
                                                      maxlength="500"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                                            <?php if (isset($errors['description'])): ?>
                                                <div class="invalid-feedback">
                                                    <?php echo htmlspecialchars($errors['description']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="form-text">
                                                <span id="charCount" class="text-muted"><?php echo strlen($form_data['description']); ?>/500</span>
                                            </div>
                                        </div>
                                        
                                        <!-- حالة المدينة -->
                                        <div class="col-md-12 mb-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="is_active" 
                                                       name="is_active" 
                                                       value="1" 
                                                       <?php echo $form_data['is_active'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_active">
                                                    <span class="badge bg-success">نشط</span>
                                                    <span class="text-muted ms-2">
                                                        عند تفعيل هذا الخيار، ستظهر المدينة في التطبيق
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <!-- أزرار الحفظ -->
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <a href="<?php echo admin_url('cities/index.php'); ?>" class="btn btn-outline-secondary me-2">
                                                        <i class="fas fa-times me-2"></i>إلغاء
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-btn" 
                                                            data-url="<?php echo admin_url('cities/index.php?delete=' . $city['id']); ?>" 
                                                            data-name="<?php echo htmlspecialchars($city['name']); ?>">
                                                        <i class="fas fa-trash me-2"></i>حذف المدينة
                                                    </button>
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save me-2"></i>حفظ التعديلات
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- نصائح وإرشادات -->
                            <div class="card-footer bg-light">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6 class="mb-3">
                                            <i class="fas fa-info-circle text-info me-2"></i>ملاحظات هامة:
                                        </h6>
                                        <ul class="list-unstyled text-muted small">
                                            <li class="mb-2">
                                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                <strong>تحذير:</strong> حذف المدينة سيحذف جميع المواقع المرتبطة بها
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                يمكنك إلغاء تفعيل المدينة بدلاً من حذفها
                                            </li>
                                            <li>
                                                <i class="fas fa-history text-primary me-2"></i>
                                                يتم حفظ تاريخ التعديل تلقائياً
                                            </li>
                                        </ul>
                                    </div>
                                </div>
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
                        <p class="mb-0">تعديل مدينة</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script>
$(document).ready(function() {
    // عد الأحرف في حقل الوصف
    $('#description').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        $('#charCount').text(currentLength + '/' + maxLength);
        
        if (remaining < 50) {
            $('#charCount').removeClass('text-muted').addClass('text-warning');
        } else {
            $('#charCount').removeClass('text-warning').addClass('text-muted');
        }
    });
    
    // التحقق من صحة النموذج قبل الإرسال
    $('#editCityForm').on('submit', function(e) {
        const name = $('#name').val().trim();
        const description = $('#description').val().trim();
        
        // إزالة رسائل الخطأ السابقة
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        let isValid = true;
        
        // التحقق من اسم المدينة
        if (!name) {
            showFieldError('name', 'اسم المدينة مطلوب');
            isValid = false;
        } else if (name.length < 2) {
            showFieldError('name', 'اسم المدينة يجب أن يكون على الأقل حرفين');
            isValid = false;
        }
        
        // التحقق من الوصف
        if (description.length > 500) {
            showFieldError('description', 'الوصف يجب أن يكون أقل من 500 حرف');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // تأثير اهتزاز للحقول غير الصالحة
            $('.is-invalid').addClass('animate__animated animate__headShake');
            setTimeout(() => {
                $('.is-invalid').removeClass('animate__animated animate__headShake');
            }, 1000);
        } else {
            // عرض رسالة تحميل
            showLoading('جاري تحديث البيانات...');
        }
    });
    
    // دالة لعرض رسالة خطأ في حقل معين
    function showFieldError(fieldId, message) {
        const $field = $('#' + fieldId);
        const $errorDiv = $('<div>', {
            class: 'invalid-feedback d-block',
            text: message
        });
        
        $field.addClass('is-invalid');
        $field.after($errorDiv);
    }
});
</script>

<?php include '../includes/footer.php'; ?>