/**
 * ملف JavaScript لصفحة تسجيل الدخول
 */

$(document).ready(function() {
    initLoginPage();
});

/**
 * تهيئة صفحة تسجيل الدخول
 */
function initLoginPage() {
    initPasswordToggle();
    initFormValidation();
    initAnimations();
}

/**
 * إظهار/إخفاء كلمة المرور
 */
function initPasswordToggle() {
    const passwordInput = $('#password');
    const eyeIcon = passwordInput.parent().find('i.fa-key');
    
    passwordInput.on('input', function() {
        if ($(this).val().length > 0) {
            eyeIcon.removeClass('fa-key').addClass('fa-eye');
            eyeIcon.css('cursor', 'pointer');
            
            eyeIcon.off('click').on('click', function() {
                const type = passwordInput.attr('type');
                if (type === 'password') {
                    passwordInput.attr('type', 'text');
                    eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        } else {
            eyeIcon.removeClass('fa-eye fa-eye-slash').addClass('fa-key');
            eyeIcon.css('cursor', 'default');
        }
    });
}

/**
 * التحقق من صحة النموذج
 */
function initFormValidation() {
    $('#loginForm').on('submit', function(e) {
        const email = $('#email').val().trim();
        const password = $('#password').val().trim();
        let isValid = true;
        
        // إزالة رسائل الخطأ السابقة
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // التحقق من البريد الإلكتروني
        if (!email) {
            showFieldError('email', 'البريد الإلكتروني مطلوب');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'بريد إلكتروني غير صالح');
            isValid = false;
        }
        
        // التحقق من كلمة المرور
        if (!password) {
            showFieldError('password', 'كلمة المرور مطلوبة');
            isValid = false;
        } else if (password.length < 6) {
            showFieldError('password', 'كلمة المرور يجب أن تكون 6 أحرف على الأقل');
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
}

/**
 * التحقق من صحة البريد الإلكتروني
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * عرض رسالة خطأ في حقل معين
 */
function showFieldError(fieldId, message) {
    const $field = $('#' + fieldId);
    const $errorDiv = $('<div>', {
        class: 'invalid-feedback d-block',
        text: message
    });
    
    $field.addClass('is-invalid');
    $field.after($errorDiv);
}

/**
 * تأثيرات الحركة
 */
function initAnimations() {
    // تأثير عند تحميل الصفحة
    const card = $('.login-card');
    card.css({
        'opacity': '0',
        'transform': 'translateY(20px)'
    });
    
    setTimeout(() => {
        card.css({
            'transition': 'opacity 0.5s ease, transform 0.5s ease',
            'opacity': '1',
            'transform': 'translateY(0)'
        });
    }, 100);
    
    // تأثير عند التركيز على الحقول
    $('.form-control').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
    
    // تأثير عند تمرير الماوس فوق زر الدخول
    $('.btn-login').hover(
        function() {
            $(this).css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
}

/**
 * محاكاة تحميل عند النقر على زر الدخول
 */
$('.btn-login').click(function() {
    const $button = $(this);
    const originalText = $button.html();
    
    // إذا كان النموذج صالحاً، عرض تحميل
    if ($('#loginForm')[0].checkValidity()) {
        $button.html('<i class="fas fa-spinner fa-spin me-2"></i>جاري تسجيل الدخول...');
        $button.prop('disabled', true);
        
        // استعادة الزر بعد 3 ثوان (للتأكد)
        setTimeout(() => {
            $button.html(originalText);
            $button.prop('disabled', false);
        }, 3000);
    }
});