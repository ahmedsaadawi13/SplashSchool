<!-- FILE: /app/views/home/index.php -->
<?php require_once APP . '/views/layouts/header.php'; ?>

<div class="hero">
    <h1>Welcome to SplashSchool</h1>
    <p class="lead">Complete Multi-Tenant School Management System</p>
    <div class="cta-buttons">
        <a href="<?php echo BASE_URL; ?>/auth/login" class="btn btn-primary btn-lg">Login</a>
        <a href="<?php echo BASE_URL; ?>/auth/register" class="btn btn-secondary btn-lg">Register Your School</a>
    </div>
</div>

<div class="features">
    <h2>Features</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <h3>Student Management</h3>
            <p>Comprehensive student records, documents, and tracking</p>
        </div>
        <div class="feature-card">
            <h3>Attendance</h3>
            <p>Daily attendance tracking and reports</p>
        </div>
        <div class="feature-card">
            <h3>Grades & Exams</h3>
            <p>Grade management and report card generation</p>
        </div>
        <div class="feature-card">
            <h3>Fee Management</h3>
            <p>Invoicing, payment tracking, and financial reports</p>
        </div>
        <div class="feature-card">
            <h3>Parent Portal</h3>
            <p>Parents can view student progress and fees</p>
        </div>
        <div class="feature-card">
            <h3>Multi-Tenant SaaS</h3>
            <p>Secure, scalable architecture for multiple schools</p>
        </div>
    </div>
</div>

<?php require_once APP . '/views/layouts/footer.php'; ?>
