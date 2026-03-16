<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: login_register.php');
    exit;
}

require_once 'db_connect.php';
$parent_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch this parent's children
$children = $pdo->prepare("
    SELECT c.child_id, c.full_name, c.date_of_birth,
           t.full_name AS teacher_name
    FROM children c
    LEFT JOIN teacher t ON c.teacher_id = t.teacher_id
    WHERE c.parent_id = ?
    ORDER BY c.child_id
");
$children->execute([$parent_id]);
$children = $children->fetchAll(PDO::FETCH_ASSOC);

// For each child, get latest AI result and grades
$child_data = [];
foreach ($children as $child) {
    $cid = $child['child_id'];

    // Latest AI result
    $res = $pdo->prepare("SELECT prediction, confidence_score, analysis_date FROM result WHERE child_id = ? ORDER BY analysis_date DESC LIMIT 1");
    $res->execute([$cid]);
    $latest_result = $res->fetch(PDO::FETCH_ASSOC);

    // Academic grades
    $grades = $pdo->prepare("SELECT subject, score, grade, term FROM academic_performance WHERE child_id = ? ORDER BY recorded_date DESC LIMIT 5");
    $grades->execute([$cid]);
    $grade_list = $grades->fetchAll(PDO::FETCH_ASSOC);

    // Active alerts
    $alerts = $pdo->prepare("SELECT message, alert_type, created_at FROM alert WHERE child_id = ? AND status = 'New' ORDER BY created_at DESC");
    $alerts->execute([$cid]);
    $alert_list = $alerts->fetchAll(PDO::FETCH_ASSOC);

    $child_data[] = [
        'info'    => $child,
        'result'  => $latest_result,
        'grades'  => $grade_list,
        'alerts'  => $alert_list
    ];
}

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #f7971e, #ffd200); color: #333; font-weight: bold; }
    .child-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.07); margin-bottom: 25px; }
    .child-card .child-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
    .child-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg,#f7971e,#ffd200); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #333; font-weight: bold; }
    .result-pill { padding: 6px 18px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; }
    .result-H { background: #dcfce7; color: #166534; }
    .result-M { background: #fef3c7; color: #92400e; }
    .result-L { background: #fee2e2; color: #991b1b; }
    .grade-row td { padding: 8px 12px; }
    .score-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-left: 4px; }
    .alert-chip { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 8px 14px; border-radius: 8px; font-size: 0.88rem; margin-top: 8px; }
</style>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Parent Portal - Monitor Your Children's Performance</p>
    </div>
</div>

<div class="container mt-5">
  <div class="row">
    <div class="col-lg-3">
      <div class="sidebar">
        <h5 class="mb-3"><i class="fas fa-bars me-2"></i> Menu</h5>
        <a href="dashboard_parent.php" class="active"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a href="#children-section"><i class="fas fa-child me-2"></i> My Children's Reports</a>
      </div>
    </div>

    <div class="col-lg-9" id="children-section">
      <h3 class="mb-4">Registered Children <span class="badge bg-secondary"><?php echo count($children); ?></span></h3>

      <?php if (count($child_data) === 0): ?>
        <div class="text-center py-5">
          <i class="far fa-frown" style="font-size:3rem; color:#ccc;"></i>
          <p class="mt-3 text-muted">No children are linked to your account yet. Please contact the teacher.</p>
        </div>
      <?php endif; ?>

      <?php foreach ($child_data as $cd):
          $child  = $cd['info'];
          $result = $cd['result'];
          $grades = $cd['grades'];
          $alerts = $cd['alerts'];
          $initial = mb_substr($child['full_name'], 0, 1);
          $cls = $result['prediction'] ?? 'M';
      ?>
        <div class="child-card">
          <!-- Child Header -->
          <div class="child-header">
            <div class="child-avatar"><?php echo $initial; ?></div>
            <div class="flex-grow-1">
              <h5 class="mb-0"><?php echo htmlspecialchars($child['full_name']); ?></h5>
              <small class="text-muted">
                Teacher: <strong><?php echo $child['teacher_name'] ? htmlspecialchars($child['teacher_name']) : 'Not assigned'; ?></strong>
                <?php if ($child['date_of_birth']): ?>
                  &nbsp;|&nbsp; DOB: <?php echo $child['date_of_birth']; ?>
                <?php endif; ?>
              </small>
            </div>
            <?php if ($result): ?>
              <span class="result-pill result-<?php echo $cls; ?>">
                <?php echo $cls === 'H' ? '<i class="fas fa-star text-warning"></i> High Achiever' : ($cls === 'M' ? '<i class="fas fa-book text-info"></i> Average' : '<i class="fas fa-exclamation-triangle"></i> Needs Attention'); ?>
              </span>
            <?php else: ?>
              <span class="badge bg-secondary">Not analyzed yet</span>
            <?php endif; ?>
          </div>

          <div class="row g-4">
            <!-- AI Result -->
            <div class="col-md-4">
              <h6 class="fw-bold"><i class="fas fa-robot text-primary me-2"></i> Latest AI Analysis</h6>
              <?php if ($result): ?>
                <p class="mb-1">Classification:
                  <strong class="<?php echo $cls==='H'?'text-success':($cls==='M'?'text-warning':'text-danger'); ?>">
                    <?php echo $cls === 'H' ? 'High Performance' : ($cls === 'M' ? 'Average Performance' : 'Low Performance'); ?>
                  </strong>
                </p>
                <div class="progress mt-1 mb-1" style="height:8px;">
                  <div class="progress-bar bg-<?php echo $cls==='H'?'success':($cls==='M'?'warning':'danger'); ?>"
                       style="width:<?php echo $result['confidence_score']; ?>%"></div>
                </div>
                <small class="text-muted">Confidence: <?php echo $result['confidence_score']; ?>% | <?php echo date('Y-m-d', strtotime($result['analysis_date'])); ?></small>
              <?php else: ?>
                <p class="text-muted small">No AI analysis has been performed yet.</p>
              <?php endif; ?>
            </div>

            <!-- Grades -->
            <div class="col-md-4">
              <h6 class="fw-bold"><i class="fas fa-clipboard-check text-success me-2"></i> Recent Grades</h6>
              <?php if (count($grades) > 0): ?>
                <table class="table table-sm mb-0">
                  <?php foreach ($grades as $g):
                      $sc  = floatval($g['score']);
                      $dot = $sc >= 90 ? '#22c55e' : ($sc >= 70 ? '#f59e0b' : '#ef4444');
                  ?>
                    <tr class="grade-row">
                      <td><?php echo htmlspecialchars($g['subject']); ?></td>
                      <td><span class="score-dot" style="background:<?php echo $dot; ?>"></span><?php echo $g['score']; ?>%</td>
                      <td><small><?php echo $g['grade']; ?></small></td>
                    </tr>
                  <?php endforeach; ?>
                </table>
              <?php else: ?>
                <p class="text-muted small">No grades have been entered yet.</p>
              <?php endif; ?>
            </div>

            <!-- Alerts -->
            <div class="col-md-4">
              <h6 class="fw-bold"><i class="fas fa-bell text-warning me-2"></i> Active Alerts
                <?php if (count($alerts) > 0): ?>
                  <span class="badge bg-danger"><?php echo count($alerts); ?></span>
                <?php endif; ?>
              </h6>
              <?php if (count($alerts) > 0): ?>
                <?php foreach ($alerts as $al): ?>
                  <div class="alert-chip"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars(mb_substr($al['message'], 0, 80)) . '...'; ?></div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted small"><i class="fas fa-check-circle text-success mt-1"></i> No active alerts.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
