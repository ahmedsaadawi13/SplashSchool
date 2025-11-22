<!-- FILE: /app/views/dashboard/platform_admin.php -->
<?php require_once APP . '/views/layouts/header.php'; ?>

<h1>Platform Admin Dashboard</h1>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Total Tenants</h3>
        <div class="stat-number"><?php echo $total_tenants ?? 0; ?></div>
    </div>

    <div class="stat-card">
        <h3>Active Tenants</h3>
        <div class="stat-number"><?php echo $active_tenants ?? 0; ?></div>
    </div>

    <div class="stat-card">
        <h3>Trialing Tenants</h3>
        <div class="stat-number"><?php echo $trialing_tenants ?? 0; ?></div>
    </div>
</div>

<h2>Recent Tenants</h2>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Subdomain</th>
            <th>Status</th>
            <th>Subscription</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($recent_tenants)): ?>
            <?php foreach ($recent_tenants as $tenant): ?>
            <tr>
                <td><?php echo $tenant['id']; ?></td>
                <td><?php echo htmlspecialchars($tenant['name']); ?></td>
                <td><?php echo htmlspecialchars($tenant['subdomain']); ?></td>
                <td><span class="badge badge-<?php echo $tenant['status']; ?>"><?php echo $tenant['status']; ?></span></td>
                <td><span class="badge badge-<?php echo $tenant['subscription_status']; ?>"><?php echo $tenant['subscription_status']; ?></span></td>
                <td><?php echo date('Y-m-d', strtotime($tenant['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">No tenants found</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once APP . '/views/layouts/footer.php'; ?>
