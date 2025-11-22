<!-- FILE: /app/views/auth/login.php -->
<?php require_once APP . '/views/layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Login to SplashSchool</h1>

        <form action="<?php echo BASE_URL; ?>/auth/login" method="POST">
            <?php echo CSRF::field(); ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div class="auth-footer">
            <p>Don't have a school account? <a href="<?php echo BASE_URL; ?>/auth/register">Register Your School</a></p>
        </div>

        <div class="demo-credentials">
            <h3>Demo Credentials</h3>
            <p><strong>Platform Admin:</strong> admin / admin123</p>
            <p><strong>School Admin:</strong> school_admin / admin123</p>
        </div>
    </div>
</div>

<?php require_once APP . '/views/layouts/footer.php'; ?>
