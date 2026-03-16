<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login_register.php');
    exit;
}

require_once 'db_connect.php';
$teacher_id = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'];

// Live stats for this teacher
$total_students = $pdo->prepare("SELECT COUNT(*) FROM children WHERE teacher_id = ?");
$total_students->execute([$teacher_id]);
$total_students = $total_students->fetchColumn();

$total_grades = $pdo->prepare("SELECT COUNT(*) FROM academic_performance ap JOIN children c ON ap.child_id = c.child_id WHERE c.teacher_id = ?");
$total_grades->execute([$teacher_id]);
$total_grades = $total_grades->fetchColumn();

$active_alerts = $pdo->prepare("SELECT COUNT(*) FROM alert a JOIN children c ON a.child_id = c.child_id WHERE c.teacher_id = ? AND a.status = 'New'");
$active_alerts->execute([$teacher_id]);
$active_alerts = $active_alerts->fetchColumn();

// Recent students (last 3)
$recent = $pdo->prepare("SELECT c.full_name, c.child_id, p.full_name AS parent_name FROM children c LEFT JOIN parent p ON c.parent_id = p.parent_id WHERE c.teacher_id = ? ORDER BY c.child_id DESC LIMIT 3");
$recent->execute([$teacher_id]);
$recent_students = $recent->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .stat-card h3 { font-size: 2.5rem; color: #667eea; margin: 10px 0; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Teacher Dashboard - Track & Analyze Student Performance</p>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="sidebar">
                <h5 class="mb-3"><i class="fas fa-bars me-2"></i> Menu</h5>
                <a href="dashboard_teacher.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="teacher_students.php"><i class="fas fa-users me-2"></i> My Students</a>
                <a href="teacher_grades.php"><i class="fas fa-clipboard-list me-2"></i> Academic Records</a>
                <a href="teacher_behavior.php"><i class="fas fa-user-edit me-2"></i> Behavioral Input</a>
                <a href="ai_analysis.php"><i class="fas fa-robot me-2"></i> AI Analysis</a>
                <a href="teacher_alerts.php"><i class="fas fa-bell me-2"></i> Alerts <span class="badge bg-danger float-end"><?php echo $active_alerts; ?></span></a>
                <a href="madrasti_import.php"><i class="fas fa-cloud-download-alt me-2"></i> Madrasti Import</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h3 class="mb-4">Overview</h3>

            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <i class="fas fa-users" style="font-size: 2rem; color: #667eea;"></i>
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                        <a href="teacher_students.php" class="btn btn-sm btn-outline-primary">Manage Students</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <i class="fas fa-clipboard-check" style="font-size: 2rem; color: #14B8A6;"></i>
                        <h3><?php echo $total_grades; ?></h3>
                        <p>Grades Entered</p>
                        <a href="teacher_grades.php" class="btn btn-sm btn-outline-success">Input Grades</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #EF4444;"></i>
                        <h3><?php echo $active_alerts; ?></h3>
                        <p>Active Alerts</p>
                        <a href="teacher_alerts.php" class="btn btn-sm btn-outline-danger">View Alerts</a>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h4>Quick Actions</h4>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="teacher_students.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Add Student</a>
                    <a href="teacher_grades.php" class="btn btn-success"><i class="fas fa-notes-medical me-1"></i> Input Grades</a>
                    <a href="teacher_behavior.php" class="btn btn-warning"><i class="fas fa-user-edit me-1"></i> Log Behavior</a>
                    <a href="ai_analysis.php" class="btn btn-info text-white"><i class="fas fa-robot me-1"></i> AI Analysis</a>
                </div>
            </div>

            <div class="mt-5">
                <h4>Recently Added Students</h4>
                <?php if (count($recent_students) > 0): ?>
                    <div class="bg-white p-4 rounded shadow-sm mt-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Name</th><th>Parent</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($recent_students as $s): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
                                        <td><?php echo $s['parent_name'] ? htmlspecialchars($s['parent_name']) : '<span class="text-muted">Not Linked</span>'; ?></td>
                                        <td><a href="teacher_grades.php?child_id=<?php echo $s['child_id']; ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-notes-medical me-1"></i> Grades</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="mt-2 text-end">
                            <a href="teacher_students.php" class="text-primary small">View All Students &rarr;</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="assets/img/custom/3.png" class="img-fluid mb-3" style="max-height: 180px;">
                        <div class="alert alert-info border-0 shadow-sm">
                            You haven't added any students yet. <a href="teacher_students.php">Click here to add your first student.</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
