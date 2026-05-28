<?php
/**
 * ملف تسجيل خروج المشرف
 */

session_start();

// تدمير جميع بيانات الجلسة
session_unset();
session_destroy();

// توجيه إلى صفحة تسجيل الدخول مع رسالة نجاح
header('Location: login.php?logout=1');
exit();
?>