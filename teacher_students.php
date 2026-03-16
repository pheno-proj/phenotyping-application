<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login_register.php');
    exit;
}

require_once 'db_connect.php';
$teacher_id  = $_SESSION['user_id'];
$user_name   = $_SESSION['user_name'];
$message     = '';
$messageType = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $full_name = trim($_POST['full_name']);
        $dob       = $_POST['dob'];
        $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

        if (!empty($full_name)) {
            $stmt = $pdo->prepare("INSERT INTO children (parent_id, teacher_id, full_name, date_of_birth) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$parent_id, $teacher_id, $full_name, $dob])) {
                $message = "Student added successfully!";
                $messageType = "success";
            } else {
                $message = "Error adding student.";
                $messageType = "error";
            }
        } else {
            $message = "Student name is required.";
            $messageType = "error";
        }
    } elseif ($action === 'delete') {
        $child_id = $_POST['child_id'];
        $stmt = $pdo->prepare("DELETE FROM children WHERE child_id = ? AND teacher_id = ?");
        if ($stmt->execute([$child_id, $teacher_id])) {
            $message = "Student deleted successfully!";
            $messageType = "success";
        }
    }
}

// Fetch Parent list for dropdown
$parents = $pdo->query("SELECT parent_id, full_name FROM parent ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Students associated with this teacher
$students = $pdo->prepare("
    SELECT c.*, p.full_name AS parent_name 
    FROM children c 
    LEFT JOIN parent p ON c.parent_id = p.parent_id 
    WHERE c.teacher_id = ?
    ORDER BY c.child_id DESC
");
$students->execute([$teacher_id]);
$studentList = $students->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Manage Your Students & Parent Linking</p>
    </div>
</div>

<div class="container mt-5">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3">
      <div class="sidebar">
        <h5 class="mb-3"><i class="fas fa-bars me-2"></i> Menu</h5>
        <a href="dashboard_teacher.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="teacher_students.php" class="active"><i class="fas fa-users me-2"></i> My Students</a>
        <a href="teacher_grades.php"><i class="fas fa-clipboard-list me-2"></i> Academic Records</a>
        <a href="teacher_behavior.php"><i class="fas fa-user-edit me-2"></i> Behavioral Input</a>
        <a href="ai_analysis.php"><i class="fas fa-robot me-2"></i> AI Analysis</a>
        <a href="teacher_alerts.php"><i class="fas fa-bell me-2"></i> Alerts</a>
        <a href="madrasti_import.php"><i class="fas fa-cloud-download-alt me-2"></i> Madrasti Import</a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      
      <!-- Add Form -->
      <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white pb-0 border-0">
          <h4 class="mb-0"><i class="fas fa-user-plus text-primary me-2"></i> Add New Student</h4>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="row g-3">
              <div class="col-md-5">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required placeholder="e.g. John Doe">
              </div>
              <div class="col-md-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Link Parent (Optional)</label>
                <select name="parent_id" class="form-select">
                  <option value="">-- Select Parent --</option>
                  <?php foreach ($parents as $p): ?>
                    <option value="<?php echo $p['parent_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Student</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Students Table -->
      <h4 class="mb-3"><i class="fas fa-list-alt text-secondary me-2"></i> Students List</h4>
      <div class="bg-white p-4 rounded shadow-sm table-responsive">
        <table class="table table-hover align-middle" id="studentsTable">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Student Name</th>
              <th>Date of Birth</th>
              <th>Parent</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($studentList) > 0): ?>
              <?php foreach ($studentList as $s): ?>
                <tr>
                  <td><?php echo $s['child_id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
                  <td><?php echo $s['date_of_birth'] ?: '-'; ?></td>
                  <td><?php echo $s['parent_name'] ? htmlspecialchars($s['parent_name']) : '<span class="text-muted">Not Linked</span>'; ?></td>
                  <td>
                    <div class="d-flex gap-2">
                      <a href="teacher_grades.php?child_id=<?php echo $s['child_id']; ?>" class="btn btn-sm btn-outline-success" title="Add Grades"><i class="fas fa-plus"></i> Grades</a>
                      <a href="ai_analysis.php?child_id=<?php echo $s['child_id']; ?>" class="btn btn-sm btn-outline-info" title="AI Analysis"><i class="fas fa-robot"></i></a>
                      <form method="POST" class="delete-form d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="child_id" value="<?php echo $s['child_id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center text-muted py-4">No students found.</td></tr>
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
            confirmButtonColor: '#667eea'
        });
    <?php endif; ?>

    $('#studentsTable').DataTable();

    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?', text: 'This will permanently delete the student and their records.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!', cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) this.submit();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
