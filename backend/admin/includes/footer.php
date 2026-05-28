<?php
/**
 * ملف تذييل الصفحة (Footer)
 * يحتوي على سكريبتات JavaScript
 */

// تحديد مسار الملفات الثابتة
// ضمان تعريف ADMIN_ASSETS_URL
if (!defined('ADMIN_ASSETS_URL')) {
    if (defined('ADMIN_BASE_URL')) {
        define('ADMIN_ASSETS_URL', ADMIN_BASE_URL . 'assets/');
    } else {
        define('ADMIN_ASSETS_URL', 'assets/');
    }
}
?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Custom JS Files -->
    <script src="<?php echo ADMIN_ASSETS_URL; ?>js/dashboard.js"></script>
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'login.php'): ?>
        <script src="<?php echo ADMIN_ASSETS_URL; ?>js/login.js"></script>
    <?php endif; ?>
    
    <script>
        // تهيئة Select2
        $(document).ready(function() {
            $('.select2').select2({
                dir: "rtl",
                language: "ar",
                placeholder: "اختر...",
                allowClear: true
            });
            
            // تهيئة DataTables إذا وجدت
            if ($.fn.DataTable) {
                $('.datatable').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
                    },
                    order: [],
                    pageLength: 25,
                    dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>'
                });
            }
            
            // تهيئة Toastr
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": true,
                    "positionClass": "toast-top-left",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut",
                    "rtl": true
                };
                
                // عرض رسائل PHP كـ Toastr
                <?php if (isset($_SESSION['toastr'])): ?>
                    <?php foreach ($_SESSION['toastr'] as $toast): ?>
                        toastr.<?php echo $toast['type']; ?>('<?php echo addslashes($toast['message']); ?>');
                    <?php endforeach; ?>
                    <?php unset($_SESSION['toastr']); ?>
                <?php endif; ?>
            }
            
            // تأكيد قبل الحذف
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                var itemName = $(this).data('name') || 'هذا العنصر';
                
                if (confirm('هل أنت متأكد أنك تريد حذف ' + itemName + '؟')) {
                    window.location.href = url;
                }
            });
            
            // تحميل الصور المسبقة
            function previewImage(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        $('#' + previewId).attr('src', e.target.result);
                        $('#' + previewId).show();
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }
            
            // معالجة اختيار الصور
            $('.image-input').change(function() {
                var previewId = $(this).data('preview');
                previewImage(this, previewId);
            });
        });
        
        // دالة لعرض رسالة تحميل
        function showLoading(message) {
            $('#loadingMessage').text(message || 'جاري المعالجة...');
            $('#loadingModal').modal('show');
        }
        
        // دالة لإخفاء رسالة التحميل
        function hideLoading() {
            $('#loadingModal').modal('hide');
        }
        
        // معالجة حذف العناصر
        $(document).on('click', '.delete-btn', function() {
            var url = $(this).data('url');
            var name = $(this).data('name') || 'هذا العنصر';
            
            $('#deleteMessage').text('هل أنت متأكد أنك تريد حذف "' + name + '"؟');
            
            $('#confirmDeleteBtn').off('click').on('click', function() {
                window.location.href = url;
            });
            
            $('#confirmDeleteModal').modal('show');
        });
    </script>
    
    <!-- Modal للتحميل -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <h5 id="loadingMessage">جاري المعالجة...</h5>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal لتأكيد الحذف -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">هل أنت متأكد أنك تريد حذف هذا العنصر؟</p>
                    <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">نعم، احذف</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>