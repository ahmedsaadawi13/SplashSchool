<!-- FILE: /app/views/students/index.php -->
<?php require_once APP . '/views/layouts/header.php'; ?>

<div class="page-header">
    <h1>Students</h1>
    <a href="<?php echo BASE_URL; ?>/students/add" class="btn btn-primary">Add Student</a>
</div>

<div class="filter-box">
    <form method="GET" action="<?php echo BASE_URL; ?>/students/index" class="search-form">
        <input type="text" name="search" placeholder="Search by code, name, or email"
               value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (isset($search) && $search): ?>
            <a href="<?php echo BASE_URL; ?>/students/index" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<p>Total Students: <?php echo $total ?? 0; ?></p>

<table class="table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Gender</th>
            <th>Class</th>
            <th>Section</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                <td><?php echo htmlspecialchars($student['gender']); ?></td>
                <td><?php echo isset($student['class_name']) ? htmlspecialchars($student['class_name']) : '-'; ?></td>
                <td><?php echo isset($student['section_name']) ? htmlspecialchars($student['section_name']) : '-'; ?></td>
                <td><span class="badge badge-<?php echo $student['status']; ?>"><?php echo $student['status']; ?></span></td>
                <td>
                    <a href="<?php echo BASE_URL; ?>/students/view/<?php echo $student['id']; ?>" class="btn btn-sm">View</a>
                    <a href="<?php echo BASE_URL; ?>/students/edit/<?php echo $student['id']; ?>" class="btn btn-sm">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">No students found</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once APP . '/views/layouts/footer.php'; ?>
