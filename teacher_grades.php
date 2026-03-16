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

// Pre-select child if passed in URL
$selected_child_id = isset($_GET['child_id']) ? $_GET['child_id'] : '';

// Function to convert score to letter grade
function getLetterGrade($score) {
    if ($score >= 90) return 'A';
    if ($score >= 80) return 'B';
    if ($score >= 70) return 'C';
    if ($score >= 60) return 'D';
    return 'F';
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $child_id = $_POST['child_id'];
        $subject  = trim($_POST['subject']);
        $score    = $_POST['score'];
        $term     = trim($_POST['term']);
        $date     = $_POST['recorded_date'];
        $grade    = getLetterGrade((float)$score);

        if (!empty($child_id) && !empty($subject) && $score !== '') {
            $stmt = $pdo->prepare("INSERT INTO academic_performance (child_id, subject, score, grade, term, recorded_date) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$child_id, $subject, $score, $grade, $term, $date])) {
                $message = "Grade recorded successfully!";
                $messageType = "success";
            } else {
                $message = "Error recording grade.";
                $messageType = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $messageType = "error";
        }
    } elseif ($action === 'delete') {
        $performance_id = $_POST['performance_id'];
        $stmt = $pdo->prepare("DELETE FROM academic_performance WHERE performance_id = ?");
        if ($stmt->execute([$performance_id])) {
            $message = "Record deleted successfully!";
            $messageType = "success";
        }
    }
}

// Fetch Teacher's Students
$students = $pdo->prepare("SELECT child_id, full_name FROM children WHERE teacher_id = ? ORDER BY full_name");
$students->execute([$teacher_id]);
$studentsList = $students->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing grades for teacher's students
$grades = $pdo->prepare("
    SELECT a.*, c.full_name AS child_name 
    FROM academic_performance a 
    JOIN children c ON a.child_id = c.child_id 
    WHERE c.teacher_id = ?
    ORDER BY a.recorded_date DESC
");
$grades->execute([$teacher_id]);
$gradeList = $grades->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #14b8a6, #0d9488); color: white; }
    .score-badge { padding: 5px 10px; border-radius: 6px; font-weight: bold; width: 60px; display: inline-block; text-align: center; color: white; }
    .score-high { background-color: #22c55e; }
    .score-med  { background-color: #f59e0b; }
    .score-low  { background-color: #ef4444; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Manage Academic Records</p>
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
        <a href="teacher_grades.php" class="active"><i class="fas fa-clipboard-list me-2"></i> Academic Records</a>
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
          <h4 class="mb-0"><i class="fas fa-plus-circle text-success me-2"></i> Input New Grade</h4>
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
                    <option value="<?php echo $stu['child_id']; ?>" <?php echo ($selected_child_id == $stu['child_id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($stu['full_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Subject *</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Math" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Score (%) *</label>
                <input type="number" name="score" class="form-control" min="0" max="100" step="0.1" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Term / Semester</label>
                <input type="text" name="term" class="form-control" placeholder="e.g. Fall 2026">
              </div>
              <div class="col-md-4">
                <label class="form-label">Registration Date *</label>
                <input type="date" name="recorded_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-8 d-flex align-items-end justify-content-end">
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save Grade</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- History Table -->
      <h4 class="mb-3"><i class="fas fa-history text-primary me-2"></i> Academic History</h4>
      <div class="bg-white p-4 rounded shadow-sm table-responsive">
        <table class="table table-hover align-middle" id="gradesTable">
          <thead class="table-light">
            <tr>
              <th>Student</th>
              <th>Subject</th>
              <th>Term</th>
              <th>Date</th>
              <th>Score</th>
              <th>Grade</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($gradeList) > 0): ?>
              <?php foreach ($gradeList as $g): 
                  $scoreClass = $g['score'] >= 85 ? 'score-high' : ($g['score'] >= 65 ? 'score-med' : 'score-low');
              ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($g['child_name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($g['subject']); ?></td>
                  <td><?php echo htmlspecialchars($g['term']); ?></td>
                  <td><?php echo date('Y-m-d', strtotime($g['recorded_date'])); ?></td>
                  <td><span class="score-badge <?php echo $scoreClass; ?>"><?php echo $g['score']; ?>%</span></td>
                  <td><strong><?php echo $g['grade']; ?></strong></td>
                  <td>
                    <form method="POST" class="d-inline delete-form">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="performance_id" value="<?php echo $g['performance_id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No grades recorded yet.</td></tr>
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
        Swal.fire({ icon:'<?php echo $messageType;?>', title:'<?php echo $messageType==="success"?"Success!":"Error!";?>', text:'<?php echo addslashes($message);?>', confirmButtonColor:'#14b8a6' });
    <?php endif; ?>

    $('#gradesTable').DataTable();

    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Delete Confirmation', text: 'Are you sure you want to delete this grade?', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
        }).then((result) => { if (result.isConfirmed) this.submit(); });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
