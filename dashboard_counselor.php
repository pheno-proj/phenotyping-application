<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counselor') {
    header('Location: login_register.php');
    exit;
}

require_once 'db_connect.php';
$user_name = $_SESSION['user_name'];

// Fetch all at-risk (Low) students with their latest result
$at_risk = $pdo->query("
    SELECT c.child_id, c.full_name,
           r.prediction, r.confidence_score, r.analysis_date,
           t.full_name AS teacher_name,
           p.full_name AS parent_name,
           p.phone     AS parent_phone,
           (SELECT COUNT(*) FROM alert a WHERE a.child_id = c.child_id AND a.status='New') AS alert_count
    FROM children c
    JOIN result r ON r.child_id = c.child_id
    LEFT JOIN teacher t ON c.teacher_id = t.teacher_id
    LEFT JOIN parent  p ON c.parent_id  = p.parent_id
    WHERE r.prediction = 'L'
      AND r.analysis_date = (SELECT MAX(analysis_date) FROM result WHERE child_id = c.child_id)
    ORDER BY r.analysis_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all students with any result for full monitoring
$all_analyzed = $pdo->query("
    SELECT c.child_id, c.full_name,
           r.prediction, r.confidence_score, r.analysis_date,
           t.full_name AS teacher_name
    FROM children c
    JOIN result r ON r.child_id = c.child_id
    LEFT JOIN teacher t ON c.teacher_id = t.teacher_id
    WHERE r.analysis_date = (SELECT MAX(analysis_date) FROM result WHERE child_id = c.child_id)
    ORDER BY FIELD(r.prediction, 'L', 'M', 'H'), r.analysis_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$total_children  = $pdo->query("SELECT COUNT(*) FROM children")->fetchColumn();
$total_low       = count($at_risk);
$total_analyzed  = count($all_analyzed);
$total_alerts    = $pdo->query("SELECT COUNT(*) FROM alert WHERE status='New'")->fetchColumn();

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #1a1a2e, #0f3460); color: white; }
    .stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.07); margin-bottom: 20px; }
    .risk-card { background: white; border-radius: 12px; padding: 20px; border-left: 5px solid #ef4444; margin-bottom: 15px; box-shadow: 0 2px 12px rgba(239,68,68,0.1); }
    .risk-card:hover { box-shadow: 0 6px 20px rgba(239,68,68,0.2); transform: translateY(-2px); transition: all 0.3s; }
    .pred-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: bold; }
    .pred-H { background: #dcfce7; color: #166534; }
    .pred-M { background: #fef3c7; color: #92400e; }
    .pred-L { background: #fee2e2; color: #991b1b; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Counselor Dashboard - Monitor At-Risk Students</p>
    </div>
</div>

<div class="container mt-5">
  <div class="row">
    <div class="col-lg-3">
      <div class="sidebar">
        <h5 class="mb-3"><i class="fas fa-bars me-2"></i> Menu</h5>
        <a href="dashboard_counselor.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="#at-risk"><i class="fas fa-exclamation-triangle me-2"></i> At-Risk Students</a>
        <a href="#all-students"><i class="fas fa-users me-2"></i> All Monitored Students</a>
        <a href="ai_analysis.php"><i class="fas fa-robot me-2"></i> AI Analysis</a>
      </div>
    </div>

    <div class="col-lg-9">
      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-users" style="font-size:1.8rem; color:#667eea;"></i>
            <h3 class="mt-1"><?php echo $total_children; ?></h3>
            <p class="text-muted mb-0 small">Total Students</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-robot" style="font-size:1.8rem; color:#22c55e;"></i>
            <h3 class="mt-1"><?php echo $total_analyzed; ?></h3>
            <p class="text-muted mb-0 small">Analyzed</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-exclamation-circle" style="font-size:1.8rem; color:#ef4444;"></i>
            <h3 class="mt-1 text-danger"><?php echo $total_low; ?></h3>
            <p class="text-muted mb-0 small">At Risk (Low)</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-bell" style="font-size:1.8rem; color:#f59e0b;"></i>
            <h3 class="mt-1 text-warning"><?php echo $total_alerts; ?></h3>
            <p class="text-muted mb-0 small">Active Alerts</p>
          </div>
        </div>
      </div>

      <!-- AT-RISK Students -->
      <h4 id="at-risk" class="mb-3">
        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
        At-Risk Students (Low Performance)
        <span class="badge bg-danger ms-2"><?php echo $total_low; ?></span>
      </h4>

      <?php if (count($at_risk) === 0): ?>
        <div class="alert alert-success shadow-sm"><i class="fas fa-check-circle me-2"></i> There are currently no low-performance students monitored.</div>
      <?php endif; ?>

      <?php foreach ($at_risk as $s): ?>
        <div class="risk-card">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <h6 class="mb-1 fw-bold"><i class="fas fa-user text-danger me-2"></i> <?php echo htmlspecialchars($s['full_name']); ?></h6>
              <small class="text-muted">
                Teacher: <strong><?php echo htmlspecialchars($s['teacher_name'] ?: 'Not Assigned'); ?></strong>
                &nbsp;|&nbsp;
                Parent: <strong><?php echo htmlspecialchars($s['parent_name'] ?: 'Not Linked'); ?></strong>
                <?php if ($s['parent_phone']): ?>
                  &nbsp;|&nbsp; <i class="fas fa-phone"></i> <?php echo $s['parent_phone']; ?>
                <?php endif; ?>
              </small>
            </div>
            <div class="text-end">
              <span class="pred-badge pred-L"><i class="fas fa-exclamation-triangle me-1"></i> Low Performance</span>
              <br><small class="text-muted"><?php echo date('Y-m-d', strtotime($s['analysis_date'])); ?></small>
            </div>
          </div>
          <div class="mt-3 d-flex align-items-center gap-3">
            <div class="flex-grow-1">
              <div class="progress" style="height:8px;">
                <div class="progress-bar bg-danger" style="width:<?php echo $s['confidence_score']; ?>%"></div>
              </div>
              <small class="text-muted">AI Confidence: <?php echo $s['confidence_score']; ?>%</small>
            </div>
            <?php if ($s['alert_count'] > 0): ?>
              <span class="badge bg-warning text-dark"><i class="fas fa-bell me-1"></i> <?php echo $s['alert_count']; ?> Alert(s)</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- ALL Analyzed Students -->
      <h4 id="all-students" class="mt-5 mb-3"><i class="fas fa-table text-primary me-2"></i> All Analyzed Students</h4>
      <div class="bg-white p-4 rounded shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr><th>Name</th><th>Teacher</th><th>Classification</th><th>Confidence</th><th>Date</th></tr>
            </thead>
            <tbody>
              <?php if (count($all_analyzed) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No AI analysis has been performed yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($all_analyzed as $s):
                $cls = $s['prediction'];
                $label = $cls === 'H' ? 'High' : ($cls === 'M' ? 'Average' : 'Low (Needs Attention)');
              ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
                  <td><small><?php echo htmlspecialchars($s['teacher_name'] ?: '—'); ?></small></td>
                  <td><span class="pred-badge pred-<?php echo $cls; ?>"><?php echo $label; ?></span></td>
                  <td>
                    <div class="progress" style="height:6px; width:80px;">
                      <div class="progress-bar bg-<?php echo $cls==='H'?'success':($cls==='M'?'warning':'danger'); ?>"
                           style="width:<?php echo $s['confidence_score']; ?>%"></div>
                    </div>
                    <small><?php echo $s['confidence_score']; ?>%</small>
                  </td>
                  <td><small><?php echo date('Y-m-d', strtotime($s['analysis_date'])); ?></small></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
