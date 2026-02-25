<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    .stat-card h3 {
      font-size: 2.5rem;
      color: #667eea;
      margin: 10px 0;
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
      transition: all 0.3s;
    }
    .sidebar a:hover, .sidebar a.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
</style>

  <div class="dashboard-header">
    <div class="container">
      <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
      <p>Admin Dashboard - Manage the entire system</p>
    </div>
  </div>

  <div class="container mt-5">
    <div class="row">
      
      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="sidebar">
          <h5 class="mb-3">Navigation</h5>
          <a href="#" class="active"><i class="bi bi-speedometer2"></i> Overview</a>
          <a href="#"><i class="bi bi-people"></i> Manage Teachers</a>
          <a href="#"><i class="bi bi-person-hearts"></i> Manage Counselors</a>
          <a href="#"><i class="bi bi-person-check"></i> Manage Parents</a>
          <a href="#"><i class="bi bi-person-badge"></i> Manage Children</a>
          <a href="#"><i class="bi bi-gear"></i> Settings</a>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">
        <h3 class="mb-4">System Overview</h3>
        
        <div class="row">
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-people" style="font-size: 2rem; color: #667eea;"></i>
              <h3>0</h3>
              <p>Total Teachers</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-person-hearts" style="font-size: 2rem; color: #764ba2;"></i>
              <h3>0</h3>
              <p>Total Counselors</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-person-check" style="font-size: 2rem; color: #14B8A6;"></i>
              <h3>0</h3>
              <p>Total Parents</p>
            </div>
          </div>

          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-person-badge" style="font-size: 2rem; color: #F59E0B;"></i>
              <h3>0</h3>
              <p>Total Children</p>
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
              <i class="bi bi-file-earmark-text" style="font-size: 2rem; color: #8B5CF6;"></i>
              <h3>0</h3>
              <p>Total Reports</p>
            </div>
          </div>
        </div>

        <div class="row mt-5 align-items-center">
          <div class="col-md-7">
            <h4>Quick Actions</h4>
            <div class="btn-group mt-3" role="group">
              <button type="button" class="btn btn-primary">Add Teacher</button>
              <button type="button" class="btn btn-primary">Add Counselor</button>
              <button type="button" class="btn btn-primary">Add Parent</button>
            </div>
          </div>
          <div class="col-md-5 text-center">
            <img src="assets/img/custom/33.png" class="img-fluid" style="max-height: 200px;">
          </div>
        </div>
      </div>

    </div>
  </div>

<?php include 'includes/footer.php'; ?>
