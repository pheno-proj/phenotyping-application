<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
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
      <p>Parent Dashboard - Monitor Your Children's Progress</p>
    </div>
  </div>

  <div class="container mt-5">
    <div class="row">
      
      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="sidebar">
          <h5 class="mb-3">Navigation</h5>
          <a href="#" class="active"><i class="bi bi-speedometer2"></i> Overview</a>
          <a href="#"><i class="bi bi-people"></i> My Children</a>
          <a href="#"><i class="bi bi-graph-up"></i> Academic Reports</a>
          <a href="#"><i class="bi bi-emoji-smile"></i> Behavior Reports</a>
          <a href="#"><i class="bi bi-bell"></i> Notifications</a>
          <a href="#"><i class="bi bi-person"></i> Profile</a>
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
              <p>My Children</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-file-text" style="font-size: 2rem; color: #14B8A6;"></i>
              <h3>0</h3>
              <p>Reports Available</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="bi bi-bell" style="font-size: 2rem; color: #F59E0B;"></i>
              <h3>0</h3>
              <p>New Notifications</p>
            </div>
          </div>
        </div>

        <div class="mt-5">
          <h4>My Children</h4>
          <div class="text-center py-4">
             <img src="assets/img/custom/5.png" class="img-fluid mb-3" style="max-height: 200px;">
             <div class="alert alert-info border-0 shadow-sm">
                No children linked to your account yet. Please contact your child's teacher or the admin to link your profile.
             </div>
          </div>
        </div>

        <div class="mt-5">
          <h4>Recent Updates</h4>
          <div class="alert alert-secondary">
            No recent updates available.
          </div>
        </div>
      </div>

    </div>
  </div>

<?php include 'includes/footer.php'; ?>
