<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
  header('Location: login_register.php');
  exit;
}

require_once 'db_connect.php';
$teacher_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Path to the API JSON file
$json_file = 'assets/data/madrasti_api_mock.json';
$api_data = [];
if (file_exists($json_file)) {
    $api_data = json_decode(file_get_contents($json_file), true);
}

$message = '';
$messageType = '';
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $messageType = 'error';
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $messageType = 'success';
    unset($_SESSION['success_message']);
}

// Function to handle the import and AI routing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_student') {
    $stu_id = $_POST['student_id'];

    // Find student in JSON
    $student_info = null;
    foreach ($api_data['students'] as $s) {
        if ($s['student_id'] === $stu_id) {
            $student_info = $s;
            break;
        }
    }

    if ($student_info) {
        // 1. Check for duplicate import
        $check = $pdo->prepare("SELECT child_id FROM children WHERE madrasti_id = ?");
        $check->execute([$stu_id]);
        if ($check->fetch()) {
            $_SESSION['error_message'] = "Student '{$student_info['full_name']}' is already imported in the system!";
            header("Location: madrasti_import.php");
            exit;
        }

        // 2. Automate Parent Account Creation
        $parent_id = null;
        $parent_phone = $student_info['parent_phone'] ?? '';
        $parent_name = $student_info['parent_name'] ?? 'Parent of ' . $student_info['full_name'];

        // Check if parent already exists by phone
        $stmt_p = $pdo->prepare("SELECT parent_id FROM parent WHERE phone = ?");
        $stmt_p->execute([$parent_phone]);
        $existing_parent = $stmt_p->fetch(PDO::FETCH_ASSOC);

        if ($existing_parent) {
            $parent_id = $existing_parent['parent_id'];
        } else {
            // Create new parent account
            $email_local = strtolower(str_replace(' ', '', $parent_name));
            $parent_email = $email_local . "@madrasti.edu";
            
            // Password is the phone number
            $default_pass = password_hash($parent_phone, PASSWORD_DEFAULT);
            
            $stmt_ins_p = $pdo->prepare("INSERT INTO parent (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt_ins_p->execute([$parent_name, $parent_email, $default_pass, $parent_phone, 'Madrasti Integrated Address']);
            $parent_id = $pdo->lastInsertId();
        }

        // 3. Create Student record
        $stmt_s = $pdo->prepare("INSERT INTO children (full_name, teacher_id, parent_id, madrasti_id) VALUES (?, ?, ?, ?)");
        $stmt_s->execute([$student_info['full_name'], $teacher_id, $parent_id, $stu_id]);
        $new_child_id = $pdo->lastInsertId();

        // 4. Save Behavioral Record to Database
        $b_raw = $student_info['behavioral_data'];
        $stmt_b = $pdo->prepare("INSERT INTO behavior (child_id, behavior_type, observation_date, description, severity_level) VALUES (?, ?, ?, ?, ?)");
        $behavior_desc = "Imported Metrics: Raised Hands ({$b_raw['raised_hands']}), Absences ({$b_raw['student_absence_days']})";
        $stmt_b->execute([$new_child_id, 'System Import', date('Y-m-d'), $behavior_desc, 'Low']);

        // 5. Prepare data for AI Redirection
        $b_data = $student_info['behavioral_data'];
        $s_data = $student_info['survey_data'];

        $postData = [
            'child_id'                   => $new_child_id,
            'gender'                     => $student_info['gender'] === 'M' ? 'M' : 'F',
            'GradeID'                    => $student_info['grade_level'],
            'SectionID'                  => $student_info['section'],
            'raisedhands'                => $b_data['raised_hands'],
            'VisITedResources'           => $b_data['visited_resources'],
            'AnnouncementsView'          => $b_data['announcements_viewed'],
            'Discussion'                 => $b_data['discussion_groups'],
            'StudentAbsenceDays'         => $b_data['student_absence_days'],
            'ParentAnsweringSurvey'      => $s_data['parent_answering_survey'],
            'ParentschoolSatisfaction'   => $s_data['parent_school_satisfaction']
        ];

        // Auto-submit form to AI page
        echo '<div style="display:flex; justify-content:center; align-items:center; height:100vh; font-family:sans-serif; flex-direction:column; background:#f8f9fa;">';
        echo '<div style="background:white; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.1); text-align:center;">';
        echo '<div style="color:#4f46e5; font-size:40px; margin-bottom:20px;"><i class="fas fa-robot"></i></div>';
        echo '<h2 style="color:#1e293b; margin-bottom:10px;">Processing Madrasti Data...</h2>';
        echo '<p style="color:#64748b;">Synchronizing student markers and connecting to AI Engine.</p>';
        echo '<form id="autoSubmit" action="ai_analysis.php" method="POST">';
        foreach ($postData as $key => $value) {
            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
        echo '</form>';
        echo '</div></div>';
        echo '<script>setTimeout(function(){ document.getElementById("autoSubmit").submit(); }, 1200);</script>';
        exit;
    }
}

include 'includes/header.php';
?>

<style>
    .dashboard-header { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white; padding: 30px 0; margin-top: 80px; }
    .sidebar { background: #f8f9fa; min-height: calc(100vh - 180px); padding: 20px; }
    .sidebar a { display: block; padding: 12px 20px; margin: 5px 0; border-radius: 8px; color: #333; text-decoration: none; transition: all 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, #0284c7, #0369a1); color: white; }
    
    .api-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #e2e8f0; }
    .stat-pill { background: #f1f5f9; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; color: #475569; margin: 3px; display: inline-block; }
    .stat-pill i { color: #0284c7; margin-left: 5px; }
    .madrasti-logo { background: linear-gradient(135deg, #0f766e, #047857); color: white; width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; }
    
    .risk-indicator { padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
    .risk-high { background: #fee2e2; color: #b91c1c; }
    .risk-med { background: #fef3c7; color: #b45309; }
    .risk-low { background: #dcfce7; color: #15803d; }
</style>

<div class="dashboard-header">
    <div class="container d-flex align-items-center gap-3">
        <div class="madrasti-logo"><i class="fas fa-graduation-cap"></i></div>
        <div>
            <h2 class="mb-0">Madrasti Platform Integration</h2>
            <p class="mb-0 opacity-75">Import students and automatically trigger AI behavioral analysis.</p>
        </div>
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
        <a href="teacher_alerts.php"><i class="fas fa-bell me-2"></i> Alerts</a>
        <a href="madrasti_import.php" class="active"><i class="fas fa-cloud-download-alt me-2"></i> Madrasti Import</a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      
      <!-- API Info -->
      <?php if (!empty($api_data)): ?>
        <div class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-sm" style="background-color: #f0f9ff; color: #0369a1;">
          <i class="fas fa-check-circle fs-3 me-3"></i>
          <div>
            <strong>Successfully connected to <?php echo $api_data['api_info']['source']; ?></strong><br>
            School: <strong><?php echo $api_data['api_info']['school_name']; ?></strong> | Academic Year: <?php echo $api_data['api_info']['academic_year']; ?>
          </div>
        </div>

        <div class="row g-4">
          <?php foreach ($api_data['students'] as $s):
    $b = $s['behavioral_data'];
    // Simple heuristic to color-code risk before AI
    $riskScore = 0;
    if ($b['raised_hands'] < 30)
      $riskScore++;
    if ($b['visited_resources'] < 50)
      $riskScore++;
    if ($b['student_absence_days'] === 'Above-7')
      $riskScore += 2;

    $riskClass = 'risk-low';
    $riskLabel = 'Active';
    if ($riskScore >= 3) {
      $riskClass = 'risk-high';
      $riskLabel = 'At Risk';
    }
    elseif ($riskScore > 0) {
      $riskClass = 'risk-med';
      $riskLabel = 'Needs Attention';
    }
?>
            <div class="col-md-6">
              <div class="api-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 45px; height: 45px; font-size: 1.2rem;">
                      <?php echo mb_substr($s['full_name'], 0, 1); ?>
                    </div>
                    <div>
                      <h5 class="mb-0 text-dark fw-bold"><?php echo htmlspecialchars($s['full_name']); ?></h5>
                      <small class="text-muted"><i class="fas fa-id-card"></i> <?php echo $s['student_id']; ?> | <?php echo $s['grade_level']; ?></small>
                    </div>
                  </div>
                  <span class="risk-indicator <?php echo $riskClass; ?>"><?php echo $riskLabel; ?></span>
                </div>

                <div class="mb-3">
                  <span class="stat-pill" title="Hands Raised"><i class="fas fa-hand-paper"></i> <?php echo $b['raised_hands']; ?></span>
                  <span class="stat-pill" title="Resources Visited"><i class="fas fa-book-open"></i> <?php echo $b['visited_resources']; ?></span>
                  <span class="stat-pill" title="Announcements Viewed"><i class="fas fa-bullhorn"></i> <?php echo $b['announcements_viewed']; ?></span>
                  <span class="stat-pill text-danger opacity-75" title="Absences"><i class="fas fa-calendar-times"></i> <?php echo $b['student_absence_days']; ?></span>
                </div>

                <form method="POST" class="import-form">
                    <input type="hidden" name="action" value="import_student">
                    <input type="hidden" name="student_id" value="<?php echo $s['student_id']; ?>">
                    <button type="button" class="btn btn-primary w-100 btn-import">
                        <i class="fas fa-robot me-1"></i> AI Analyze & Import
                    </button>
                </form>
              </div>
            </div>
          <?php
  endforeach; ?>
        </div>

      <?php
else: ?>
        <div class="alert alert-danger shadow-sm">
          <i class="fas fa-exclamation-triangle me-2"></i> Failed to read the API data.
        </div>
      <?php
endif; ?>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if ($message): ?>
        Swal.fire({
            icon: '<?php echo $messageType; ?>',
            title: '<?php echo $messageType === "success" ? "Success!" : "Notice"; ?>',
            text: '<?php echo addslashes($message); ?>',
            confirmButtonColor: '#0284c7'
        });
    <?php endif; ?>

    document.querySelectorAll('.btn-import').forEach(btn => {
        btn.addEventListener('click', function() {
            Swal.fire({
                title: 'Import & Analyze?',
                text: "This student will be added to your roster and their behavioral metrics will be sent to the AI for instant analysis.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-1"></i> Yes, Proceed',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Connecting to AI...',
                        html: 'Analyzing student behaviors...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    this.closest('form').submit();
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
