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

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $child_id       = $_POST['child_id'];
        $behavior_type  = trim($_POST['behavior_type']);
        $observation    = $_POST['observation_date'];
        $description    = trim($_POST['description']);
        $severity       = $_POST['severity_level'];

        if (!empty($child_id) && !empty($behavior_type) && !empty($observation) && !empty($severity)) {
            $stmt = $pdo->prepare("INSERT INTO behavior (child_id, behavior_type, observation_date, description, severity_level) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$child_id, $behavior_type, $observation, $description, $severity])) {
                $message = "Behavioral observation saved successfully!";
                $messageType = "success";
            } else {
                $message = "Error saving observation.";
                $messageType = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $messageType = "error";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['behavior_id'];
        $stmt = $pdo->prepare("DELETE FROM behavior WHERE behavior_id = ?");
        if ($stmt->execute([$id])) {
            $message = "Observation deleted successfully!";
            $messageType = "success";
        }
    }
}

// Fetch Teacher's Students
$students = $pdo->prepare("SELECT child_id, full_name FROM children WHERE teacher_id = ? ORDER BY full_name");
$students->execute([$teacher_id]);
$studentsList = $students->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing behaviors for teacher's students
$behaviors = $pdo->prepare("
    SELECT b.*, c.full_name AS child_name
    FROM behavior b
    JOIN children c ON b.child_id = c.child_id
    WHERE c.teacher_id = ?
    ORDER BY b.observation_date DESC
");
$behaviors->execute([$teacher_id]);
$behaviorList = $behaviors->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #10b981, #047857); color: white; }
    .severity-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; min-width: 80px; text-align: center; }
    .sev-High { background: #fee2e2; color: #b91c1c; }
    .sev-Medium { background: #fef3c7; color: #b45309; }
    .sev-Low { background: #dcfce7; color: #15803d; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Manage Student Behavioral Observations</p>
    </div>
</div>

<div class="container mt-5">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3">
      <div class="sidebar">
        <h5 class="mb-3"><i class="fas fa-bars me-2"></i> Menu</h5>
        <a href="dashboard_teacher.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="teacher_students.php"><i class="fas fa-users me-2"></i> My Students</a>
        <a href="teacher_grades.php"><i class="fas fa-clipboard-list me-2"></i> Academic Records</a>
        <a href="teacher_behavior.php" class="active"><i class="fas fa-user-edit me-2"></i> Behavioral Input</a>
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
          <h4 class="mb-0"><i class="fas fa-plus-circle text-success me-2"></i> Add New Observation</h4>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Student *</label>
                <select name="child_id" class="form-select" required>
                  <option value="">-- Select Student --</option>
                  <?php foreach ($studentsList as $stu): ?>
                    <option value="<?php echo $stu['child_id']; ?>"><?php echo htmlspecialchars($stu['full_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Behavior Type *</label>
                <input type="text" name="behavior_type" class="form-control" placeholder="e.g. Social withdrawal" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Observation Date *</label>
                <input type="date" name="observation_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Severity Level *</label>
                <select name="severity_level" class="form-select" required>
                  <option value="Low">Low</option>
                  <option value="Medium">Medium</option>
                  <option value="High">High</option>
                </select>
              </div>
              <div class="col-md-8">
                <label class="form-label">Details / Description</label>
                <textarea name="description" class="form-control" rows="1"></textarea>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save Observation</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- History Table -->
      <h4 class="mb-3"><i class="fas fa-history text-primary me-2"></i> Observation History</h4>
      <div class="bg-white p-4 rounded shadow-sm table-responsive">
        <table class="table table-hover align-middle" id="behaviorTable">
          <thead class="table-light">
            <tr>
              <th>Student</th>
              <th>Type</th>
              <th>Date</th>
              <th>Details</th>
              <th>Severity</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($behaviorList) > 0): ?>
              <?php foreach ($behaviorList as $b): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($b['child_name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($b['behavior_type']); ?></td>
                  <td><?php echo $b['observation_date']; ?></td>
                  <td><small><?php echo htmlspecialchars($b['description'] ?: 'None'); ?></small></td>
                  <td><span class="severity-pill sev-<?php echo $b['severity_level']; ?>"><?php echo $b['severity_level']; ?></span></td>
                  <td>
                    <form method="POST" class="d-inline delete-form">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="behavior_id" value="<?php echo $b['behavior_id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No behavioral observations recorded.</td></tr>
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
        Swal.fire({ icon:'<?php echo $messageType;?>', title:'<?php echo $messageType==="success"?"Success!":"Error!";?>', text:'<?php echo addslashes($message);?>', confirmButtonColor:'#10b981' });
    <?php endif; ?>

    $('#behaviorTable').DataTable({
        "order": [[2, "desc"]] // Sort by date
    });

    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Delete Confirmation', text: 'Are you sure you want to delete this observation?', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it', cancelButtonText: 'Cancel'
        }).then((result) => { if (result.isConfirmed) this.submit(); });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
