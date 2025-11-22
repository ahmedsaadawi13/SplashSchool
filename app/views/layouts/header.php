<!-- FILE: /app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>SplashSchool</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>" class="brand">SplashSchool</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="nav-menu">
                <li><a href="<?php echo BASE_URL; ?>/dashboard/index">Dashboard</a></li>

                <?php if (in_array($_SESSION['user_role'], ['school_admin', 'receptionist', 'teacher'])): ?>
                <li><a href="<?php echo BASE_URL; ?>/students/index">Students</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['user_role'] === 'school_admin'): ?>
                <li><a href="<?php echo BASE_URL; ?>/teachers/index">Teachers</a></li>
                <li><a href="<?php echo BASE_URL; ?>/classes/index">Classes</a></li>
                <?php endif; ?>

                <?php if (in_array($_SESSION['user_role'], ['school_admin', 'teacher'])): ?>
                <li><a href="<?php echo BASE_URL; ?>/attendance/index">Attendance</a></li>
                <li><a href="<?php echo BASE_URL; ?>/grades/index">Grades</a></li>
                <?php endif; ?>

                <?php if (in_array($_SESSION['user_role'], ['school_admin', 'accountant'])): ?>
                <li><a href="<?php echo BASE_URL; ?>/fees/index">Fees</a></li>
                <?php endif; ?>

                <?php if (in_array($_SESSION['user_role'], ['school_admin', 'teacher', 'accountant'])): ?>
                <li><a href="<?php echo BASE_URL; ?>/reports/index">Reports</a></li>
                <?php endif; ?>

                <li class="user-menu">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-btn">Logout</a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container main-content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
