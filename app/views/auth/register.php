<!-- FILE: /app/views/auth/register.php -->
<?php require_once APP . '/views/layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-box register-box">
        <h1>Register Your School</h1>
        <p>Start your 30-day free trial</p>

        <form action="<?php echo BASE_URL; ?>/auth/register" method="POST">
            <?php echo CSRF::field(); ?>

            <h3>School Information</h3>

            <div class="form-group">
                <label for="school_name">School Name *</label>
                <input type="text" id="school_name" name="school_name" required
                       value="<?php echo isset($form_data['school_name']) ? htmlspecialchars($form_data['school_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="subdomain">Subdomain * (yourschool.splashschool.com)</label>
                <input type="text" id="subdomain" name="subdomain" required pattern="[a-z0-9]+"
                       value="<?php echo isset($form_data['subdomain']) ? htmlspecialchars($form_data['subdomain']) : ''; ?>">
                <small>Lowercase letters and numbers only</small>
            </div>

            <div class="form-group">
                <label for="email">School Email *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="phone">School Phone *</label>
                <input type="tel" id="phone" name="phone" required
                       value="<?php echo isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : ''; ?>">
            </div>

            <h3>Administrator Account</h3>

            <div class="form-group">
                <label for="admin_name">Full Name *</label>
                <input type="text" id="admin_name" name="admin_name" required
                       value="<?php echo isset($form_data['admin_name']) ? htmlspecialchars($form_data['admin_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="admin_email">Email *</label>
                <input type="email" id="admin_email" name="admin_email" required
                       value="<?php echo isset($form_data['admin_email']) ? htmlspecialchars($form_data['admin_email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="admin_username">Username *</label>
                <input type="text" id="admin_username" name="admin_username" required
                       value="<?php echo isset($form_data['admin_username']) ? htmlspecialchars($form_data['admin_username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password * (min 6 characters)</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Start Free Trial</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?php echo BASE_URL; ?>/auth/login">Login</a></p>
        </div>
    </div>
</div>

<?php require_once APP . '/views/layouts/footer.php'; ?>
