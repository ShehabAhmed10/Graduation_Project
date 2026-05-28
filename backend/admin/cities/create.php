<?php
/**
 * صفحة إضافة مدينة جديدة
 */

// تضمين ملفات المصادقة والاتصال بقاعدة البيانات
require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

// تعيين عنوان الصفحة
$page_title = 'إضافة مدينة جديدة - Yemen Tourism';

// تهيئة المتغيرات
$errors = [];
$form_data = [
    'name' => '',
    'description' => '',
    'is_active' => 1
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
    
    // التحقق من عدم تكرار اسم المدينة
    if (empty($errors['name'])) {
        $check_sql = "SELECT id FROM cities WHERE name = ?";
        $existing = db_fetch($check_sql, [$form_data['name']]);
        
        if ($existing) {
            $errors['name'] = 'اسم المدينة موجود مسبقاً';
        }
    }
    
    // إذا لم توجد أخطاء، حفظ البيانات
    if (empty($errors)) {
        try {
            $insert_sql = "INSERT INTO cities (name, description, is_active) VALUES (?, ?, ?)";
            $result = db_execute($insert_sql, [
                $form_data['name'],
                $form_data['description'],
                $form_data['is_active']
            ]);
            
                if ($result) {
                $city_id = db_last_insert_id();
                set_flash_message('success', 'تم إضافة المدينة "' . $form_data['name'] . '" بنجاح');
                header('Location: ' . admin_url('cities/index.php'));
                exit();
            } else {
                $errors['general'] = 'حدث خطأ أثناء حفظ البيانات. الرجاء المحاولة مرة أخرى.';
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
                        <h1 class="h3 mb-2">إضافة مدينة جديدة</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo admin_url('index.php'); ?>">لوحة التحكم</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo admin_url('cities/index.php'); ?>">المدن</a></li>
                                <li class="breadcrumb-item active">إضافة جديدة</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('cities/index.php'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>عودة للقائمة
                        </a>
                    </div>
                </div>
                
                <!-- نموذج الإضافة -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-plus-circle me-2 text-primary"></i>
                                    إضافة مدينة جديدة
                                </h5>
                            </div>
                            
                            <div class="card-body">
                                <?php if (isset($errors['general'])): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php echo htmlspecialchars($errors['general']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="" id="cityForm">
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
                                            <div class="form-text">
                                                مثال: صنعاء، عدن، تعز، حضرموت
                                            </div>
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
                                                يمكنك كتابة وصف مختصر عن المدينة (حد أقصى 500 حرف).
                                                <span id="charCount" class="text-muted">0/500</span>
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
                                                <a href="<?php echo admin_url('cities/index.php'); ?>" class="btn btn-outline-secondary">
                                                    <i class="fas fa-times me-2"></i>إلغاء
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i>حفظ المدينة
                                                </button>
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
                                            <i class="fas fa-lightbulb text-warning me-2"></i>نصائح لإضافة مدينة:
                                        </h6>
                                        <ul class="list-unstyled text-muted small">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                استخدم أسماء المدن اليمنية المعروفة
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                اكتب وصفاً مختصراً يجذب السياح
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                تأكد من صحة المعلومات قبل الحفظ
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                يمكنك تعديل المدينة لاحقاً إذا لزم الأمر
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
                        <p class="mb-0">إضافة مدينة جديدة</p>
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
    
    // تشغيل الحدث لضبط العدد الأولي
    $('#description').trigger('input');
    
    // التحقق من صحة النموذج قبل الإرسال
    $('#cityForm').on('submit', function(e) {
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