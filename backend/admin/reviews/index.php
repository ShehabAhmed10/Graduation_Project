<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

$page_title = 'إدارة التقييمات - Yemen Tourism';

$reviews = db_select("SELECT r.*, u.name as user_name, a.name as attraction_name, h.name as hotel_name
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN attractions a ON r.attraction_id = a.id
    LEFT JOIN hotels h ON r.hotel_id = h.id
    ORDER BY r.created_at DESC");

if ($reviews === false) {
    error_log('Failed to fetch reviews for admin list');
    $reviews = [];
}

include '../includes/header.php';
?>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/topbar.php'; ?>
        <main class="main-content">
            <div class="container-fluid">
                <?php echo display_flash_messages(); ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">قائمة التقييمات</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="reviews-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>المستخدم</th>
                                        <th>الهدف</th>
                                        <th>التقييم</th>
                                        <th>النص</th>
                                        <th>الحالة</th>
                                        <th>تاريخ</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($reviews as $r): ?>
                                    <tr>
                                        <td><?php echo $r['id']; ?></td>
                                        <td><?php echo htmlspecialchars($r['user_name'] ?? 'مستخدم مجهول'); ?></td>
                                        <td><?php echo htmlspecialchars($r['attraction_name'] ?? $r['hotel_name'] ?? '-'); ?></td>
                                        <td><?php echo (int)$r['rating']; ?></td>
                                        <td style="max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($r['comment']); ?></td>
                                        <td><?php
                                            // بعض النسخ تخزن الحالة في حقل `status` (pending/approved/rejected)
                                            $status = $r['status'] ?? null;
                                            if ($status === null) {
                                                // قد يكون هناك حقل is_approved (0/1)
                                                $status = isset($r['is_approved']) ? ($r['is_approved'] ? 'approved' : 'pending') : null;
                                            }

                                            if ($status === 'approved' || $status === '1' || $status === 1) {
                                                echo '<span class="badge bg-success">تمت الموافقة</span>';
                                            } elseif ($status === 'rejected' || $status === '0' || $status === 0) {
                                                echo '<span class="badge bg-danger">مرفوض</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">قيد الانتظار</span>';
                                            }
                                        ?></td>
                                        <td><?php echo $r['created_at']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('reviews/moderate.php?id=' . $r['id']); ?>" class="btn btn-sm btn-primary">مراجعة</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
