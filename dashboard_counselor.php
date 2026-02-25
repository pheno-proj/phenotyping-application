<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counselor') {
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
    .alert-item {
      background: white;
      border-left: 4px solid #EF4444;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 5px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

  <div class="dashboard-header">
    <div class="container">
      <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
      <p>Counselor Dashboard - Monitor At-Risk Students</p>
    </div>
  </div>

  <div class="container mt-5">
    <div class="row">
      
      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="sidebar">
          <h5 class="mb-3">Navigation</h5>
          <a href="#" class="active"><i class="bi bi-speedometer2"></i> Overview</a>
          <a href="#"><i class="bi bi-exclamation-triangle"></i> Active Alerts</a>
          <a href="#"><i class="bi bi-people"></i> At-Risk Students</a>
          <a href="#"><i class="bi bi-graph-up-arrow"></i> Analytics</a>
          <a href="#"><i class="bi bi-file-earmark-text"></i> Reports</a>
          <a href="#"><i class="bi bi-chat-dots"></i> Interventions</a>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">
        <h3 class="mb-4">System Overview</h3>
        
        <div class="row">
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #EF4444;"></i>
              <h3>0</h3>
              <p>Active Alerts</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-people" style="font-size: 2rem; color: #F59E0B;"></i>
              <h3>0</h3>
              <p>At-Risk Students</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-check-circle" style="font-size: 2rem; color: #14B8A6;"></i>
              <h3>0</h3>
              <p>Interventions</p>
            </div>
          </div>
        </div>

        <div class="mt-5">
          <h4>Recent Alerts</h4>
          <div class="text-center py-4">
            <img src="assets/img/custom/22.png" class="img-fluid mb-3" style="max-height: 250px;">
            <div class="alert alert-info border-0 shadow-sm">
              No alerts at this time. The system will notify you automatically when intervention is needed for at-risk students.
            </div>
          </div>
        </div>

        <div class="mt-5">
          <h4>Quick Actions</h4>
          <div class="btn-group mt-3" role="group">
            <button type="button" class="btn btn-danger">View Alerts</button>
            <button type="button" class="btn btn-warning">At-Risk Students</button>
            <button type="button" class="btn btn-info">Generate Report</button>
          </div>
        </div>
      </div>

    </div>
  </div>

<?php include 'includes/footer.php'; ?>
