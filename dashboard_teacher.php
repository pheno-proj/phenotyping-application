<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login_register.php');
    exit;
}

$user_name = $_SESSION['user_name'];
include 'includes/header.php';
?>

<style>
    .dashboard-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px 0;
      margin-top: 80px;
    }
    .stat-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .sidebar {
      background: #f8f9fa;
      min-height: calc(100vh - 180px);
      padding: 20px;
    }
    .sidebar a {
      display: block;
      padding: 12px 20px;
      margin: 5px 0;
      border-radius: 8px;
      color: #333;
      text-decoration: none;
    }
    .sidebar a:hover, .sidebar a.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
</style>

  <div class="dashboard-header">
    <div class="container">
      <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
      <p>Teacher Dashboard - Track Student Performance</p>
    </div>
  </div>

  <div class="container mt-5">
    <div class="row">
      
      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="sidebar">
          <h5 class="mb-3">Navigation</h5>
          <a href="#" class="active"><i class="bi bi-speedometer2"></i> Overview</a>
          <a href="#"><i class="bi bi-people"></i> My Students</a>
          <a href="#"><i class="bi bi-clipboard-data"></i> Academic Input</a>
          <a href="#"><i class="bi bi-emoji-smile"></i> Behavior Input</a>
          <a href="#"><i class="bi bi-exclamation-circle"></i> Alerts</a>
          <a href="#"><i class="bi bi-file-text"></i> Reports</a>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">
        <h3 class="mb-4">My Dashboard</h3>
        
        <div class="row">
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-people" style="font-size: 2rem; color: #667eea;"></i>
              <h3>0</h3>
              <p>Total Students</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #EF4444;"></i>
              <h3>0</h3>
              <p>Active Alerts</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-clipboard-check" style="font-size: 2rem; color: #14B8A6;"></i>
              <h3>0</h3>
              <p>Assessments Done</p>
            </div>
          </div>
        </div>

        <div class="mt-5">
          <h4>Quick Actions</h4>
          <div class="btn-group mt-3" role="group">
            <button type="button" class="btn btn-primary">Add Student</button>
            <button type="button" class="btn btn-success">Input Grades</button>
            <button type="button" class="btn btn-info">Input Behavior</button>
          </div>
        </div>

        <div class="mt-5">
          <h4>Recent Students</h4>
          <div class="text-center py-5">
            <img src="assets/img/custom/3.png" class="img-fluid mb-3" style="max-height: 250px;">
            <div class="alert alert-info border-0 shadow-sm">
              No students added yet. Click "Add Student" to get started and begin tracking behavior.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

<?php include 'includes/footer.php'; ?>
