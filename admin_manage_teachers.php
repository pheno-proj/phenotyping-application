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

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $specialization = trim($_POST['specialty']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];

        if (strlen($name) < 3) {
            $message = "Name must be at least 3 characters!";
            $messageType = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format!";
            $messageType = "error";
        } elseif (!preg_match('/^05[0-9]{8}$/', $phone)) {
            $message = "Invalid Saudi phone number! Must start with 05 and be 10 digits.";
            $messageType = "error";
        } elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters!";
            $messageType = "error";
        } else {
            // Check if email exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM teacher WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetchColumn() > 0) {
                $message = "Email is already registered!";
                $messageType = "error";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO teacher (full_name, email, password, specialization, phone) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hashed_password, $specialization, $phone])) {
                    $message = "Teacher added successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to add teacher.";
                    $messageType = "error";
                }
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['teacher_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $specialization = trim($_POST['specialty']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];

        if (strlen($name) < 3) {
            $message = "Name must be at least 3 characters!";
            $messageType = "error";
        } elseif (!preg_match('/^05[0-9]{8}$/', $phone)) {
            $message = "Invalid Saudi phone number!";
            $messageType = "error";
        } else {
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    $message = "Password must be at least 8 characters!";
                    $messageType = "error";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE teacher SET full_name=?, email=?, specialization=?, phone=?, password=? WHERE teacher_id=?");
                    if ($stmt->execute([$name, $email, $specialization, $phone, $hashed_password, $id])) {
                        $message = "Teacher updated successfully!";
                        $messageType = "success";
                    }
                }
            } else {
                $stmt = $pdo->prepare("UPDATE teacher SET full_name=?, email=?, specialization=?, phone=? WHERE teacher_id=?");
                if ($stmt->execute([$name, $email, $specialization, $phone, $id])) {
                    $message = "Teacher updated successfully!";
                    $messageType = "success";
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['teacher_id'];
        $stmt = $pdo->prepare("DELETE FROM teacher WHERE teacher_id=?");
        if ($stmt->execute([$id])) {
            $message = "Teacher deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to delete teacher. They might be linked to children records.";
            $messageType = "error";
        }
    }
}

// Fetch all teachers
$teachers_stmt = $pdo->query("SELECT * FROM teacher ORDER BY teacher_id DESC");
$teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <p>Manage Teacher Accounts</p>
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
                <a href="admin_manage_teachers.php" class="active"><i class="fas fa-chalkboard-teacher me-2"></i> Manage Teachers</a>
                <a href="admin_manage_parents.php"><i class="fas fa-user-friends me-2"></i> Manage Parents</a>
                <a href="admin_manage_counselors.php"><i class="fas fa-user-md me-2"></i> Manage Counselors</a>
                <a href="admin_manage_children.php"><i class="fas fa-child me-2"></i> Manage Students</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Registered Teachers <span class="badge bg-secondary"><?php echo count($teachers); ?></span></h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                    <i class="fas fa-plus-circle me-1"></i> Add New Teacher
                </button>
            </div>

            <div class="bg-white p-4 rounded shadow-sm table-responsive">
                <table class="table table-hover align-middle" id="teachersTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Specialty</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($teachers) > 0): ?>
                            <?php foreach ($teachers as $t): ?>
                                <tr>
                                    <td><?php echo $t['teacher_id']; ?></td>
                                    <td><?php echo htmlspecialchars($t['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($t['email']); ?></td>
                                    <td><?php echo htmlspecialchars($t['phone'] ?: 'None'); ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($t['specialization'] ?: 'General'); ?></span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $t['teacher_id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline delete-form">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="teacher_id" value="<?php echo $t['teacher_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $t['teacher_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-edit text-primary me-2"></i> Edit Teacher</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="teacher_id" value="<?php echo $t['teacher_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Full Name</label>
                                                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($t['full_name']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($t['email']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Phone</label>
                                                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($t['phone']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Specialty</label>
                                                                <input type="text" name="specialty" class="form-control" value="<?php echo htmlspecialchars($t['specialization']); ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-danger">New Password (leave blank to keep current)</label>
                                                                <input type="password" name="password" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No teachers registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus text-primary me-2"></i> Add New Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" required minlength="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="05XXXXXXXX" pattern="05[0-9]{8}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Specialty</label>
                        <input type="text" name="specialty" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password (min 8 chars)</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Teacher</button>
                </div>
            </form>
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

    $('#teachersTable').DataTable();

    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This teacher will be permanently deleted.",
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
