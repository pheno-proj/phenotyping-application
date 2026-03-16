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

// Handle marking as Read or Resolved
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alert_id']) && isset($_POST['new_status'])) {
        $a_id = $_POST['alert_id'];
        $n_st = $_POST['new_status'];
        if (in_array($n_st, ['Read', 'Resolved'])) {
            $stmt = $pdo->prepare("UPDATE alert SET status = ? WHERE alert_id = ?");
            if ($stmt->execute([$n_st, $a_id])) {
                $message = "Alert status updated successfully!";
            }
        }
    }
}

// Fetch all alerts for students of this teacher
$alertsQuery = $pdo->prepare("
    SELECT a.*, c.full_name AS child_name
    FROM alert a
    JOIN children c ON a.child_id = c.child_id
    WHERE c.teacher_id = ?
    ORDER BY FIELD(a.status, 'New', 'Read', 'Resolved'), a.created_at DESC
");
$alertsQuery->execute([$teacher_id]);
$alerts = $alertsQuery->fetchAll(PDO::FETCH_ASSOC);

// Count active alerts
$activeCount = 0;
foreach ($alerts as $a) {
    if ($a['status'] === 'New') $activeCount++;
}

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #10b981, #047857); color: white; }
    .alert-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid #ccc; transition: all 0.3s; }
    .alert-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
    .status-New { border-left-color: #ef4444; background: #fff5f5; }
    .status-Read { border-left-color: #f59e0b; background: #fffbeb; }
    .status-Resolved { border-left-color: #10b981; background: #f0fdf4; opacity: 0.8; }
    .badge-New { background: #ef4444; color: white; }
    .badge-Read { background: #f59e0b; color: white; }
    .badge-Resolved { background: #10b981; color: white; }
    .rtl-support { direction: ltr; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Manage System Alerts (Low Performance & AI Notifications)</p>
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
        <a href="teacher_behavior.php"><i class="fas fa-user-edit me-2"></i> Behavioral Input</a>
        <a href="ai_analysis.php"><i class="fas fa-robot me-2"></i> AI Analysis</a>
        <a href="teacher_alerts.php" class="active"><i class="fas fa-bell me-2"></i> Alerts <span class="badge bg-danger float-end"><?php echo $activeCount; ?></span></a>
        <a href="madrasti_import.php"><i class="fas fa-cloud-download-alt me-2"></i> Madrasti Import</a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      <h3 class="mb-4 d-flex justify-content-between align-items-center">
        <span><i class="fas fa-bell text-warning me-2"></i> Alerts & Notifications</span>
      </h3>

      <?php if (count($alerts) === 0): ?>
        <div class="alert alert-success d-flex align-items-center shadow-sm" role="alert">
          <i class="fas fa-check-circle fs-4 me-3"></i>
          <div>No alerts currently found for your students. Everything looks good!</div>
        </div>
      <?php endif; ?>

      <!-- Alerts List -->
      <?php foreach ($alerts as $a): 
          $sLabel = $a['status'] === 'New' ? 'New' : ($a['status'] === 'Read' ? 'Read' : 'Resolved');
          $tLabel = $a['alert_type'] === 'Behavioral' ? 'Behavioral' : ($a['alert_type'] === 'Academic' ? 'Academic' : 'General');
      ?>
        <div class="alert-card status-<?php echo $a['status']; ?>">
          <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div class="mb-3 mb-md-0">
              <h5 class="mb-1 text-dark fw-bold">
                <i class="fas fa-user-circle text-secondary me-1"></i> <?php echo htmlspecialchars($a['child_name']); ?>
              </h5>
              <div class="text-muted small mb-2">
                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('Y-m-d h:i A', strtotime($a['created_at'])); ?> 
                &nbsp;|&nbsp; 
                <span class="badge bg-secondary"><?php echo $tLabel; ?></span>
              </div>
              <p class="mb-0 mt-2" style="font-size: 1.05rem;"><i class="fas fa-exclamation-triangle text-danger px-1"></i> <?php echo nl2br(htmlspecialchars($a['message'])); ?></p>
            </div>
            
            <div class="text-md-end text-start mt-2 mt-md-0" style="min-width: 130px;">
              <span class="badge badge-<?php echo $a['status']; ?> mb-2 d-block py-2"><i class="fas fa-info-circle"></i> <?php echo $sLabel; ?></span>
              
              <?php if ($a['status'] !== 'Resolved'): ?>
                <form method="POST" class="mt-2">
                  <input type="hidden" name="alert_id" value="<?php echo $a['alert_id']; ?>">
                  <?php if ($a['status'] === 'New'): ?>
                    <button type="submit" name="new_status" value="Read" class="btn btn-sm btn-outline-warning w-100 mb-1">
                      <i class="fas fa-eye"></i> Mark as Read
                    </button>
                  <?php endif; ?>
                  <button type="submit" name="new_status" value="Resolved" class="btn btn-sm btn-outline-success w-100">
                    <i class="fas fa-check-double"></i> Mark Resolved
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if ($message): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo addslashes($message); ?>',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
