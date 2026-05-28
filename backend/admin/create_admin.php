<?php
// ملف لإنشاء مشرف جديد مع كلمة مرور مشفرة
echo "<h2>إنشاء حساب مشرف جديد</h2>";
require_once 'includes/db_config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($full_name) && !empty($email) && !empty($password)) {
        // تشفير كلمة المرور
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        echo "<h3>بيانات المشرف:</h3>";
        echo "<p><strong>الاسم:</strong> $full_name</p>";
        echo "<p><strong>البريد:</strong> $email</p>";
        echo "<p><strong>كلمة المرور (نص عادي):</strong> $password</p>";
        echo "<p><strong>كلمة المرور (مشفرة):</strong> $password_hash</p>";
        
        echo "<h3>كود SQL للإدخال:</h3>";
        echo "<pre>";
        echo "INSERT INTO users (full_name, email, password_hash, role, is_active) VALUES\n";
        echo "('$full_name', '$email', '$password_hash', 'admin', 1);";
        echo "</pre>";
        
        echo "<hr>";
        
        // إضافة المشرف مباشرة إلى قاعدة البيانات
        try {
           require_once 'includes/db_config.php';
            
            $sql = "INSERT INTO users (full_name, email, password_hash, role, is_active) 
                    VALUES (?, ?, ?, 'admin', 1)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $email, $password_hash]);
            
            if ($stmt->rowCount() > 0) {
                echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>
                        <strong>✓ تم إنشاء المشرف بنجاح!</strong><br>
                        يمكنك الآن تسجيل الدخول باستخدام:<br>
                        البريد: $email<br>
                        كلمة المرور: $password
                      </div>";
            }
        } catch (PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>
                    <strong>✗ خطأ:</strong> " . $e->getMessage() . "
                  </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء مشرف جديد</title>
    <style>
        body { font-family: 'Cairo', sans-serif; padding: 20px; background-color: #f5f7fb; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h2 { color: #4361ee; border-bottom: 2px solid #4361ee; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;
        }
        button { background-color: #4361ee; color: white; border: none; padding: 12px 24px; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #3a0ca3; }
        .note { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">الاسم الكامل:</label>
                <input type="text" id="full_name" name="full_name" required placeholder="أدخل الاسم الكامل">
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" required placeholder="admin@yementourism.com">
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required placeholder="كلمة مرور قوية">
            </div>
            
            <div class="note">
                <strong>ملاحظة:</strong> سيتم تشفير كلمة المرور وإضافتها إلى قاعدة البيانات.
            </div>
            
            <button type="submit">إنشاء مشرف جديد</button>
        </form>
    </div>
</body>
</html>