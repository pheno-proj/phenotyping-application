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
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
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
            $check = $pdo->prepare("SELECT COUNT(*) FROM parent WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetchColumn() > 0) {
                $message = "Email is already registered!";
                $messageType = "error";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO parent (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hashed_password, $phone, $address])) {
                    $message = "Parent added successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to add parent.";
                    $messageType = "error";
                }
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['parent_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
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
                    $message = "New password must be at least 8 characters!";
                    $messageType = "error";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE parent SET full_name=?, email=?, phone=?, address=?, password=? WHERE parent_id=?");
                    if ($stmt->execute([$name, $email, $phone, $address, $hashed_password, $id])) {
                        $message = "Parent details updated successfully!";
                        $messageType = "success";
                    }
                }
            } else {
                $stmt = $pdo->prepare("UPDATE parent SET full_name=?, email=?, phone=?, address=? WHERE parent_id=?");
                if ($stmt->execute([$name, $email, $phone, $address, $id])) {
                    $message = "Parent details updated successfully!";
                    $messageType = "success";
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['parent_id'];
        $stmt = $pdo->prepare("DELETE FROM parent WHERE parent_id=?");
        if ($stmt->execute([$id])) {
            $message = "Parent deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to delete. Parent might be linked to children in the system.";
            $messageType = "error";
        }
    }
}

// Fetch all parents
$parents_stmt = $pdo->query("SELECT * FROM parent ORDER BY parent_id DESC");
$parents = $parents_stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <p>Manage Parent Accounts</p>
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
                <a href="admin_manage_parents.php" class="active"><i class="fas fa-user-friends me-2"></i> Manage Parents</a>
                <a href="admin_manage_counselors.php"><i class="fas fa-user-md me-2"></i> Manage Counselors</a>
                <a href="admin_manage_children.php"><i class="fas fa-child me-2"></i> Manage Students</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Registered Parents <span class="badge bg-secondary"><?php echo count($parents); ?></span></h3>
                <button class="btn btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#addParentModal">
                    <i class="fas fa-plus-circle me-1"></i> Add New Parent
                </button>
            </div>

            <div class="bg-white p-4 rounded shadow-sm table-responsive">
                <table class="table table-hover align-middle" id="parentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($parents) > 0): ?>
                            <?php foreach ($parents as $p): ?>
                                <tr>
                                    <td><?php echo $p['parent_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                                    <td><?php echo htmlspecialchars($p['phone'] ?: 'None'); ?></td>
                                    <td><?php echo htmlspecialchars($p['address'] ? mb_substr($p['address'], 0, 25) . '...' : 'None'); ?></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $p['parent_id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline delete-form">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="parent_id" value="<?php echo $p['parent_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $p['parent_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-edit text-primary me-2"></i> Edit Parent</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="parent_id" value="<?php echo $p['parent_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Full Name</label>
                                                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($p['full_name']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($p['email']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Phone</label>
                                                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($p['phone']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Address</label>
                                                                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($p['address']); ?></textarea>
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
                            <tr><td colspan="6" class="text-center text-muted py-4">No parents registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Parent Modal -->
<div class="modal fade" id="addParentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus text-primary me-2"></i> Add New Parent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="At least 3 characters" minlength="3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Saudi Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="05XXXXXXXX" pattern="05[0-9]{8}" title="Must start with 05 and be 10 digits" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Detailed Address"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password (min 8 chars)</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Save Parent</button>
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
            confirmButtonColor: '#f59e0b'
        });
    <?php endif; ?>

    $('#parentsTable').DataTable();

    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This parent will be permanently deleted.",
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
