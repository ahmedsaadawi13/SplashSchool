<!-- FILE: /app/views/dashboard/school_admin.php -->
<?php require_once APP . '/views/layouts/header.php'; ?>

<h1>School Admin Dashboard</h1>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Total Students</h3>
        <div class="stat-number"><?php echo $total_students ?? 0; ?></div>
    </div>

    <div class="stat-card">
        <h3>Total Teachers</h3>
        <div class="stat-number"><?php echo $total_teachers ?? 0; ?></div>
    </div>

    <div class="stat-card">
        <h3>Attendance Today</h3>
        <div class="stat-number">
            <?php
            if (isset($attendance_today)) {
                echo $attendance_today['present'] ?? 0;
                echo ' / ';
                echo $attendance_today['total'] ?? 0;
            } else {
                echo '0 / 0';
            }
            ?>
        </div>
    </div>

    <div class="stat-card">
        <h3>Unpaid Invoices</h3>
        <div class="stat-number"><?php echo $unpaid_invoices ?? 0; ?></div>
        <small>Amount: $<?php echo number_format($total_unpaid_amount ?? 0, 2); ?></small>
    </div>
</div>

<div class="quick-actions">
    <h2>Quick Actions</h2>
    <div class="action-buttons">
        <a href="<?php echo BASE_URL; ?>/students/add" class="btn btn-primary">Add Student</a>
        <a href="<?php echo BASE_URL; ?>/teachers/add" class="btn btn-primary">Add Teacher</a>
        <a href="<?php echo BASE_URL; ?>/attendance/index" class="btn btn-primary">Mark Attendance</a>
        <a href="<?php echo BASE_URL; ?>/fees/createInvoice" class="btn btn-primary">Create Invoice</a>
    </div>
</div>

<?php require_once APP . '/views/layouts/footer.php'; ?>
