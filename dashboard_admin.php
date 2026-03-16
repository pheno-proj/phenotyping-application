<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login_register.php');
    exit;
}

require_once 'db_connect.php';
$user_name = $_SESSION['user_name'];

// Safe method to get count
function getCount($pdo, $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0; // If table doesn't exist yet, return 0 instead of crashing
    }
}

// Fetch stats for all roles
$total_teachers   = getCount($pdo, 'teacher');
$total_parents    = getCount($pdo, 'parent');
$total_counselors = getCount($pdo, 'counselor');
$total_students   = getCount($pdo, 'children');
$total_results    = getCount($pdo, 'result');
$total_alerts     = getCount($pdo, 'alert');

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; transition: transform 0.2s; border: 1px solid #e5e7eb; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
    .stat-card h3 { font-size: 2.2rem; margin: 10px 0; font-weight: bold; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #4f46e5, #3730a3); color: white; }
    
    .c-teacher { color: #3b82f6; }
    .c-parent { color: #f59e0b; }
    .c-counselor { color: #10b981; }
    .c-student { color: #8b5cf6; }
    .c-ai { color: #ec4899; }
    .c-alert { color: #ef4444; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>System Administrator Dashboard</p>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="sidebar shadow-sm rounded">
                <h5 class="mb-4"><i class="fas fa-cogs text-primary me-2"></i> System Management</h5>
                <a href="dashboard_admin.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <hr class="text-secondary">
                <small class="text-muted d-block mb-2 fw-bold text-uppercase px-2">Users</small>
                <a href="admin_manage_teachers.php"><i class="fas fa-chalkboard-teacher me-2"></i> Manage Teachers</a>
                <a href="admin_manage_parents.php"><i class="fas fa-user-friends me-2"></i> Manage Parents</a>
                <a href="admin_manage_counselors.php"><i class="fas fa-user-md me-2"></i> Manage Counselors</a>
                <a href="admin_manage_children.php"><i class="fas fa-child me-2"></i> Manage Students</a>
                <hr class="text-secondary">
                <small class="text-muted d-block mb-2 fw-bold text-uppercase px-2">Analytics</small>
                <a href="admin_manage_results.php"><i class="fas fa-chart-bar me-2"></i> Manage AI Results</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h3 class="mb-4 d-flex align-items-center"><i class="fas fa-chart-line text-primary me-2"></i> Global System Overview</h3>

            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card text-center" onclick="location.href='admin_manage_teachers.php'" style="cursor:pointer;">
                        <i class="fas fa-chalkboard-teacher c-teacher" style="font-size: 2.5rem;"></i>
                        <h3 class="c-teacher"><?php echo $total_teachers; ?></h3>
                        <p class="mb-0 text-muted fw-bold">Active Teachers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center" onclick="location.href='admin_manage_parents.php'" style="cursor:pointer;">
                        <i class="fas fa-user-friends c-parent" style="font-size: 2.5rem;"></i>
                        <h3 class="c-parent"><?php echo $total_parents; ?></h3>
                        <p class="mb-0 text-muted fw-bold">Registered Parents</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center" onclick="location.href='admin_manage_counselors.php'" style="cursor:pointer;">
                        <i class="fas fa-user-md c-counselor" style="font-size: 2.5rem;"></i>
                        <h3 class="c-counselor"><?php echo $total_counselors; ?></h3>
                        <p class="mb-0 text-muted fw-bold">Counselors</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center" onclick="location.href='admin_manage_children.php'" style="cursor:pointer;">
                        <i class="fas fa-child c-student" style="font-size: 2.5rem;"></i>
                        <h3 class="c-student"><?php echo $total_students; ?></h3>
                        <p class="mb-0 text-muted fw-bold">System Students</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center" onclick="location.href='admin_manage_results.php'" style="cursor:pointer;">
                        <i class="fas fa-robot c-ai" style="font-size: 2.5rem;"></i>
                        <h3 class="c-ai"><?php echo $total_results; ?></h3>
                        <p class="mb-0 text-muted fw-bold">AI Results Generated</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <i class="fas fa-bell c-alert" style="font-size: 2.5rem;"></i>
                        <h3 class="c-alert"><?php echo $total_alerts; ?></h3>
                        <p class="mb-0 text-muted fw-bold">Total Alerts Triggered</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 bg-white p-4 border rounded shadow-sm">
                <h4 class="mb-3"><i class="fas fa-bolt text-warning me-2"></i> Quick Actions</h4>
                <div class="d-flex flex-wrap gap-2">
                    <a href="admin_manage_teachers.php" class="btn btn-primary"><i class="fas fa-plus-circle me-1"></i> Add Teacher</a>
                    <a href="admin_manage_parents.php" class="btn btn-warning text-dark"><i class="fas fa-plus-circle me-1"></i> Add Parent</a>
                    <a href="admin_manage_counselors.php" class="btn btn-success"><i class="fas fa-plus-circle me-1"></i> Add Counselor</a>
                    <a href="admin_manage_results.php" class="btn btn-secondary"><i class="fas fa-list-alt me-1"></i> View Results Log</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
