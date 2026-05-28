<?php
session_start();

// تأكد من تحميل إعدادات المشروع لتعريف ADMIN_BASE_URL
require_once __DIR__ . '/../config/config.php';

// إذا كان المستخدم مسجل الدخول بالفعل، توجيهه إلى الداشبورد
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . ADMIN_BASE_URL . 'index.php');
    exit();
}

// معالجة تسجيل الدخول
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // التحقق الأساسي
    if (empty($email) || empty($password)) {
        $error_message = 'البريد الإلكتروني وكلمة المرور مطلوبان';
    } else {
        // الاتصال بقاعدة البيانات
        require_once '../config/db.php';
        
        try {
            // البحث عن المشرف بالبريد الإلكتروني
            $sql = "SELECT id, full_name, email, password_hash, role, is_active 
                    FROM users 
                    WHERE email = ? AND role = 'admin'";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                // التحقق من حالة الحساب
                if ($admin['is_active'] != 1) {
                    $error_message = 'حسابك غير مفعل. الرجاء التواصل مع المسؤول';
                } 
                // التحقق من كلمة المرور
                elseif (password_verify($password, $admin['password_hash'])) {
                    // تسجيل بيانات الجلسة
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['last_activity'] = time();
                    
                    // توجيه إلى الداشبورد
                    header('Location: ' . ADMIN_BASE_URL . 'index.php');
                    exit();
                } else {
                    $error_message = 'كلمة المرور غير صحيحة';
                }
            } else {
                $error_message = 'بيانات الدخول غير صحيحة';
            }
        } catch (PDOException $e) {
            $error_message = 'حدث خطأ في الخادم. الرجاء المحاولة لاحقاً';
        }
    }
}

// إذا جاء من تسجيل خروج
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $success_message = 'تم تسجيل الخروج بنجاح';
}

// إذا انتهت الجلسة
if (isset($_GET['expired']) && $_GET['expired'] == '1') {
    $error_message = 'انتهت الجلسة. الرجاء تسجيل الدخول مرة أخرى';
}

$page_title = 'تسجيل دخول المشرف - Yemen Tourism';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <?php include 'includes/header.php'; ?>
    <!-- ملف CSS الخاص بالـ login يُحمّل من الهيدر تلقائياً -->
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h2>Yemen Tourism</h2>
                <p>لوحة تحكم المشرفين</p>
            </div>
            
            <div class="login-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label class="form-label" for="email">
                            <i class="fas fa-envelope me-2"></i>البريد الإلكتروني
                        </label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="أدخل بريدك الإلكتروني" 
                                   required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock me-2"></i>كلمة المرور
                        </label>
                        <div class="input-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="أدخل كلمة المرور" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <p>© 2025 Yemen Tourism Guide System. جميع الحقوق محفوظة</p>
                <p>تواصل مع <a href="mailto:support@yementourism.com">الدعم الفني</a> في حالة وجود مشاكل</p>
            </div>
        </div>
    </div>

    <script src="<?php echo ADMIN_BASE_URL; ?>assets/js/login.js"></script>
</body>
</html>