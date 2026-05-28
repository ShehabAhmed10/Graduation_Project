/**
 * الملف الرئيسي للداشبورد - الوظائف الأساسية
 */

// تهيئة الداشبورد عند تحميل الصفحة
$(document).ready(function() {
    initDashboard();
});

/**
 * تهيئة جميع مكونات الداشبورد
 */
function initDashboard() {
    initSidebar();
    initActiveMenu();
    initTooltips();
    initPopovers();
    initBackToTop();
    initResponsive();
    initNotifications();
}

/**
 * إدارة الشريط الجانبي (Sidebar)
 */
function initSidebar() {
    // تبديل عرض/إخفاء السايدبار
    $('.sidebar-toggle').click(function() {
        $('.sidebar').toggleClass('show');
        $('body').toggleClass('sidebar-open');
    });
    
    // إغلاق السايدبار عند النقر خارجيه (للشاشات الصغيرة)
    $(document).click(function(event) {
        if (!$(event.target).closest('.sidebar').length && 
            !$(event.target).closest('.sidebar-toggle').length && 
            $('.sidebar').hasClass('show')) {
            $('.sidebar').removeClass('show');
        }
    });
    
    // منع إغلاق السايدبار عند النقر داخله
    $('.sidebar').click(function(event) {
        event.stopPropagation();
    });
}

/**
 * تعيين العنصر النشط في القائمة بناءً على الصفحة الحالية
 */
function initActiveMenu() {
    const currentPage = window.location.pathname.split('/').pop();
    
    $('.nav-link').each(function() {
        const linkHref = $(this).attr('href');
        if (linkHref && linkHref === currentPage) {
            $(this).addClass('active');
        }
    });
}

/**
 * تهيئة التلميحات (Tooltips)
 */
function initTooltips() {
    $('[data-bs-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top'
    });
}

/**
 * تهيئة النوافذ المنبثقة (Popovers)
 */
function initPopovers() {
    $('[data-bs-toggle="popover"]').popover();
}

/**
 * زر العودة إلى الأعلى
 */
function initBackToTop() {
    const $backToTop = $('<button>', {
        class: 'btn btn-primary btn-back-to-top',
        html: '<i class="fas fa-chevron-up"></i>',
        title: 'العودة إلى الأعلى'
    }).appendTo('body');
    
    $backToTop.css({
        position: 'fixed',
        bottom: '30px',
        left: '30px',
        zIndex: 1000,
        width: '50px',
        height: '50px',
        borderRadius: '50%',
        display: 'none',
        boxShadow: '0 2px 10px rgba(0,0,0,0.2)'
    });
    
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $backToTop.fadeIn();
        } else {
            $backToTop.fadeOut();
        }
    });
    
    $backToTop.click(function() {
        $('html, body').animate({scrollTop: 0}, 500);
    });
}

/**
 * إدارة الإشعارات
 */
function initNotifications() {
    $('.notification-badge').click(function() {
        // هنا يمكنك إضافة منطق عرض الإشعارات
        console.log('عرض الإشعارات');
    });
}

/**
 * تهيئة الاستجابة للشاشات المختلفة
 */
function initResponsive() {
    // إعادة ترتيب العناصر بناءً على حجم الشاشة
    function handleResize() {
        if ($(window).width() < 992) {
            $('.sidebar').removeClass('show');
        }
    }
    
    // تنفيذ عند تحميل الصفحة وتغيير الحجم
    handleResize();
    $(window).resize(handleResize);
}

/**
 * دالة لعرض رسالة تحميل
 * @param {string} message - نص الرسالة
 */
function showLoading(message = 'جاري المعالجة...') {
    $('#loadingMessage').text(message);
    $('#loadingModal').modal('show');
}

/**
 * دالة لإخفاء رسالة التحميل
 */
function hideLoading() {
    $('#loadingModal').modal('hide');
}

/**
 * دالة للتحقق من صحة البريد الإلكتروني
 * @param {string} email - البريد الإلكتروني للتحقق
 * @returns {boolean}
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * دالة للتحقق من صحة رقم الهاتف (صيغة أساسية)
 * @param {string} phone - رقم الهاتف للتحقق
 * @returns {boolean}
 */
function isValidPhone(phone) {
    const phoneRegex = /^[0-9\s\-\+\(\)]{8,20}$/;
    return phoneRegex.test(phone);
}

/**
 * دالة لعرض رسالة خطأ في حقل معين
 * @param {string} fieldId - معرف الحقل
 * @param {string} message - نص الرسالة
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
 * دالة لإزالة جميع رسائل الخطأ من النموذج
 * @param {string} formId - معرف النموذج (اختياري)
 */
function clearFieldErrors(formId = null) {
    const selector = formId ? '#' + formId + ' .invalid-feedback' : '.invalid-feedback';
    $(selector).remove();
    $('.is-invalid').removeClass('is-invalid');
}

/**
 * دالة لعرض تأكيد قبل الحذف
 * @param {string} itemName - اسم العنصر المراد حذفه
 * @param {function} callback - الدالة التي ستُنفذ عند التأكيد
 */
function confirmDelete(itemName, callback) {
    $('#deleteMessage').text(`هل أنت متأكد أنك تريد حذف "${itemName}"؟`);
    
    $('#confirmDeleteBtn').off('click').on('click', function() {
        if (typeof callback === 'function') {
            callback();
        }
        $('#confirmDeleteModal').modal('hide');
    });
    
    $('#confirmDeleteModal').modal('show');
}

/**
 * دالة لنسخ النص إلى الحافظة
 * @param {string} text - النص المراد نسخه
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        toastr.success('تم نسخ النص إلى الحافظة');
    }).catch(function(err) {
        console.error('فشل نسخ النص: ', err);
        toastr.error('فشل نسخ النص');
    });
}

/**
 * دالة لتنسيق التاريخ
 * @param {string} dateString - تاريخ بصيغة نصية
 * @returns {string} تاريخ منسق
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('ar-SA', options);
}

/**
 * دالة لحساب الوقت المنقضي منذ تاريخ معين
 * @param {string} dateString - تاريخ بصيغة نصية
 * @returns {string} وقت منقضي (مثل: منذ ساعتين)
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval > 1) return `منذ ${interval} سنوات`;
    
    interval = Math.floor(seconds / 2592000);
    if (interval > 1) return `منذ ${interval} أشهر`;
    
    interval = Math.floor(seconds / 86400);
    if (interval > 1) return `منذ ${interval} أيام`;
    
    interval = Math.floor(seconds / 3600);
    if (interval > 1) return `منذ ${interval} ساعات`;
    
    interval = Math.floor(seconds / 60);
    if (interval > 1) return `منذ ${interval} دقائق`;
    
    return 'الآن';
}