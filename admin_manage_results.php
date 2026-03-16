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

// Handle Delete Result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $result_id = $_POST['result_id'];
    $stmt = $pdo->prepare("DELETE FROM result WHERE result_id = ?");
    if ($stmt->execute([$result_id])) {
        $message = "AI Result deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting AI result.";
        $messageType = "error";
    }
}

// Fetch all results with relevant context (child name, teacher name)
$query = "
    SELECT r.*, c.full_name AS child_name, t.full_name AS teacher_name
    FROM result r
    JOIN children c ON r.child_id = c.child_id
    LEFT JOIN teacher t ON c.teacher_id = t.teacher_id
    ORDER BY r.analysis_date DESC
";
$stmt = $pdo->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #4f46e5, #3730a3); color: white; }
    .pred-badge { padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.85rem; }
    .pred-H { background: #dcfce7; color: #166534; }
    .pred-M { background: #fef3c7; color: #92400e; }
    .pred-L { background: #fee2e2; color: #991b1b; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Admin Dashboard - Manage AI Results & System Data</p>
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
            <a href="admin_manage_children.php"><i class="fas fa-child me-2"></i> Manage Students</a>
            <hr class="text-secondary">
            <small class="text-muted d-block mb-2 fw-bold text-uppercase px-2">Analytics</small>
            <a href="admin_manage_results.php" class="active"><i class="fas fa-chart-bar me-2"></i> Manage AI Results</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-robot text-primary me-2"></i> AI Analysis Results Tracker</h3>
      </div>

      <div class="bg-white p-4 rounded shadow-sm table-responsive">
        <table class="table table-hover align-middle" id="resultsTable">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Student</th>
              <th>Teacher</th>
              <th>Prediction</th>
              <th>Confidence</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($results) > 0): ?>
              <?php foreach ($results as $r): 
                  $cls = $r['prediction'];
                  $label = $cls === 'H' ? 'High' : ($cls === 'M' ? 'Average' : 'Low');
              ?>
                <tr>
                  <td><?php echo $r['result_id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($r['child_name']); ?></strong></td>
                  <td><small><?php echo htmlspecialchars($r['teacher_name'] ?: 'None'); ?></small></td>
                  <td><span class="pred-badge pred-<?php echo $cls; ?>"><?php echo $label; ?></span></td>
                  <td><?php echo $r['confidence_score']; ?>%</td>
                  <td><?php echo date('Y-m-d', strtotime($r['analysis_date'])); ?></td>
                  <td>
                    <div class="d-flex gap-2">
                        <!-- View Details Modal Trigger -->
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $r['result_id']; ?>" title="View Input Details">
                          <i class="fas fa-eye"></i>
                        </button>

                        <!-- Delete Form -->
                        <form method="POST" class="d-inline delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="result_id" value="<?php echo $r['result_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Result"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>

                    <!-- Details Modal -->
                    <div class="modal fade" id="modal-<?php echo $r['result_id']; ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-server me-2"></i> Model Input Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <p><strong>Raw JSON Input:</strong></p>
                            <pre class="bg-light p-3 rounded" style="font-size: 0.85rem; border: 1px solid #ccc;"><?php 
                                $json = json_decode($r['input_data'], true);
                                echo $json ? json_encode($json, JSON_PRETTY_PRINT) : htmlspecialchars($r['input_data']); 
                            ?></pre>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No AI results found in the database.</td></tr>
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
        Swal.fire({ icon:'<?php echo $messageType;?>', title:'<?php echo $messageType==="success"?"Success!":"Error!";?>', text:'<?php echo addslashes($message);?>', confirmButtonColor:'#4f46e5' });
    <?php endif; ?>

    $('#resultsTable').DataTable({
        "order": [[5, "desc"]] // Sort by date
    });

    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Delete Confirmation', text: 'Are you sure you want to permanently delete this AI result?', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
        }).then((result) => { if (result.isConfirmed) this.submit(); });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
