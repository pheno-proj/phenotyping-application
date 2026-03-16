<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login_register.php');
    exit;
}

require_once 'db_connect.php';
$user_name = $_SESSION['user_name'];
$message = '';
$messageType = '';

// Handle Delete Operation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $child_id = $_POST['child_id'];
    $stmt = $pdo->prepare("DELETE FROM children WHERE child_id = ?");
    if ($stmt->execute([$child_id])) {
        $message = "Student deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete student.";
        $messageType = "error";
    }
}

// Fetch all children with their assigned teacher and parent names
$query = "
    SELECT c.*, t.full_name AS teacher_name, p.full_name AS parent_name
    FROM children c
    LEFT JOIN teacher t ON c.teacher_id = t.teacher_id
    LEFT JOIN parent p ON c.parent_id = p.parent_id
    ORDER BY c.child_id DESC
";
$stmt = $pdo->query($query);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; border-radius: 8px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #4f46e5, #3730a3); color: white; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Manage System Students Data</p>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="sidebar shadow-sm rounded">
                <h5 class="mb-4"><i class="fas fa-cogs text-primary me-2"></i> System Management</h5>
                <a href="dashboard_admin.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <hr class="text-secondary">
                <small class="text-muted d-block mb-2 fw-bold text-uppercase px-2">Users</small>
                <a href="admin_manage_teachers.php"><i class="fas fa-chalkboard-teacher me-2"></i> Manage Teachers</a>
                <a href="admin_manage_parents.php"><i class="fas fa-user-friends me-2"></i> Manage Parents</a>
                <a href="admin_manage_counselors.php"><i class="fas fa-user-md me-2"></i> Manage Counselors</a>
                <a href="admin_manage_children.php" class="active"><i class="fas fa-child me-2"></i> Manage Students</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Registered Students <span class="badge bg-secondary"><?php echo count($children); ?></span></h3>
            </div>

            <div class="bg-white p-4 rounded shadow-sm table-responsive">
                <table class="table table-hover align-middle" id="childrenTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Madrasti ID</th>
                            <th>Student Name</th>
                            <th>DOB</th>
                            <th>Assigned Teacher</th>
                            <th>Linked Parent</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($children) > 0): ?>
                            <?php foreach ($children as $c): ?>
                                <tr>
                                    <td><?php echo $c['child_id']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $c['madrasti_id'] ?: 'Internal'; ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($c['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['date_of_birth'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($c['teacher_name'] ?: 'Not Assigned'); ?></td>
                                    <td><?php echo htmlspecialchars($c['parent_name'] ?: 'Not Linked'); ?></td>
                                    <td class="text-end text-nowrap">
                                        <form method="POST" class="d-inline delete-form">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="child_id" value="<?php echo $c['child_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No students registered in the system.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<script>
$(document).ready(function() {
    <?php if ($message): ?>
        Swal.fire({
            icon: '<?php echo $messageType; ?>',
            title: '<?php echo $messageType === "success" ? "Success!" : "Error!"; ?>',
            text: '<?php echo addslashes($message); ?>',
            confirmButtonColor: '#4f46e5'
        });
    <?php endif; ?>

    $('#childrenTable').DataTable();

    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This student and all their associated records (grades, behavior, AI results) will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) this.submit();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
