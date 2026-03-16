<?php
session_start();

// Access Control: Only Admin, Teacher, and Counselor
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher', 'counselor'])) {
    header("Location: login_register.php");
    exit();
}

require_once 'db_connect.php';
require_once 'includes/ai_connector.php';

$prediction   = null;
$error        = null;
$saved_to_db  = false;
$user_role    = $_SESSION['role'];
$user_id      = $_SESSION['user_id'];

// For teacher: fetch their students for the dropdown
$my_students = [];
if ($user_role === 'teacher') {
    $stmt = $pdo->prepare("SELECT child_id, full_name FROM children WHERE teacher_id = ? ORDER BY full_name");
    $stmt->execute([$user_id]);
    $my_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_child_id = isset($_POST['child_id']) && $_POST['child_id'] !== '' ? intval($_POST['child_id']) : null;

    $data = [
        'gender'                    => $_POST['gender']                    ?? 'M',
        'NationalITy'               => $_POST['NationalITy']               ?? 'KW',
        'PlaceofBirth'              => $_POST['PlaceofBirth']              ?? 'KuwaIT',
        'StageID'                   => $_POST['StageID']                   ?? 'MiddleSchool',
        'GradeID'                   => $_POST['GradeID']                   ?? 'G-07',
        'SectionID'                 => $_POST['SectionID']                 ?? 'A',
        'Topic'                     => $_POST['Topic']                     ?? 'IT',
        'Semester'                  => $_POST['Semester']                  ?? 'F',
        'Relation'                  => $_POST['Relation']                  ?? 'Father',
        'raisedhands'               => (int)($_POST['raisedhands']         ?? 0),
        'VisITedResources'          => (int)($_POST['VisITedResources']    ?? 0),
        'AnnouncementsView'         => (int)($_POST['AnnouncementsView']   ?? 0),
        'Discussion'                => (int)($_POST['Discussion']          ?? 0),
        'ParentAnsweringSurvey'     => $_POST['ParentAnsweringSurvey']     ?? 'No',
        'ParentschoolSatisfaction'  => $_POST['ParentschoolSatisfaction']  ?? 'Bad',
        'StudentAbsenceDays'        => $_POST['StudentAbsenceDays']        ?? 'Under-7',
    ];

    $result = AIConnector::predict($data);

    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        $prediction = $result;

        // ── Save to DB if a student was selected ──────────────────────────
        if ($selected_child_id && $user_role === 'teacher') {
            $details = json_encode($data);
            $save = $pdo->prepare(
                "INSERT INTO result (child_id, prediction, confidence_score, details) VALUES (?, ?, ?, ?)"
            );
            if ($save->execute([
                $selected_child_id,
                $prediction['prediction'],
                $prediction['confidence'],
                $details
            ])) {
                $saved_to_db = true;

                // Auto-generate alert if Low performance
                if ($prediction['prediction'] === 'L') {
                    $alert = $pdo->prepare(
                        "INSERT INTO alert (child_id, message, alert_type, status) VALUES (?, ?, 'Behavioral', 'New')"
                    );
                    $alert->execute([
                        $selected_child_id,
                        'Warning: AI Analysis indicates Low performance demanding immediate attention.'
                    ]);
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
  :root {
    --primary-gradient: linear-gradient(135deg, var(--color-primary), #8e44ad);
  }

  .ai-page { 
    padding: 140px 0 80px; 
    background-color: #f8faff; 
    min-height: 100vh;
  }

  .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: var(--color-secondary);
    text-align: center;
  }

  .ai-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    overflow: hidden;
    background: #fff;
    transition: transform 0.3s ease;
  }

  .ai-card-header {
    background: var(--primary-gradient);
    padding: 40px;
    color: #fff;
    text-align: center;
  }

  .ai-card-header i {
    font-size: 3rem;
    margin-bottom: 15px;
    display: block;
  }

  .form-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 20px;
    border-left: 5px solid var(--color-primary);
    padding-left: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .form-control, .form-select {
    border-radius: 10px;
    padding: 12px 15px;
    border: 1px solid #e1e5ee;
  }

  .form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--color-primary-rgb), 0.15);
    border-color: var(--color-primary);
  }

  .range-label {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .range-value {
    background: var(--color-primary);
    color: #fff;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
  }

  .btn-analyze {
    background: var(--primary-gradient);
    color: #fff;
    border: none;
    padding: 18px 50px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.2rem;
    box-shadow: 0 5px 20px rgba(106, 17, 203, 0.3);
    transition: all 0.3s ease;
  }

  .btn-analyze:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(106, 17, 203, 0.4);
    color: #fff;
  }

  /* Result Styles */
  .result-box {
    animation: fadeInUp 0.5s ease;
    background: #fff;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    border: 2px solid #eee;
    text-align: center;
  }

  .prediction-badge {
    font-size: 2.5rem;
    font-weight: 800;
    padding: 15px 40px;
    border-radius: 100px;
    display: inline-block;
    margin: 20px 0;
  }

  .conf-progress {
    height: 10px;
    border-radius: 10px;
    background-color: #eee;
    margin: 15px 0;
  }

  /* Loading Spinner */
  #loading-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255,255,255,0.9);
    z-index: 9999;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .spinner-border {
    width: 4rem;
    height: 4rem;
    color: var(--color-primary);
  }

  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<div id="loading-overlay">
  <div class="spinner-border" role="status"></div>
  <h4 class="mt-3 fw-bold text-primary">Analyzing Student Behavior...</h4>
  <p class="text-muted">Our AI engine is processing phenotyping data</p>
</div>

<main class="ai-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xl-9 col-lg-10">

        <h1 class="section-title">Digital Phenotyping AI Engine</h1>

        <?php if ($error): ?>
        <div class="alert alert-danger ai-card p-4 mb-4">
          <i class="bi bi-exclamation-octagon-fill me-2 fs-4"></i>
          <strong>Analysis Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($prediction): ?>
        <div class="result-box">
          <h3 class="fw-bold mb-1">Analysis Result</h3>
          <p class="text-muted mb-0">Predicted Academic/Behavioral Category</p>
          
          <?php
            $p_code = $prediction['prediction'];
            $label = $p_code == 'H' ? 'High Excellence' : ($p_code == 'M' ? 'Middle Stable' : 'Low (Action Required)');
            $color = $p_code == 'H' ? 'success' : ($p_code == 'M' ? 'warning' : 'danger');
            $icon = $p_code == 'H' ? 'trophy-fill' : ($p_code == 'M' ? 'journal-check' : 'exclamation-circle-fill');
          ?>

          <div class="prediction-badge bg-<?= $color ?>-light text-<?= $color ?> shadow-sm">
             <i class="bi bi-<?= $icon ?> me-2"></i><?= $label ?>
          </div>

          <div class="row justify-content-center">
            <div class="col-md-6">
              <div class="d-flex justify-content-between mb-1">
                <span class="fw-bold">Confidence Score</span>
                <span class="text-primary fw-bold"><?= $prediction['confidence'] ?>%</span>
              </div>
              <div class="progress conf-progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?= $prediction['confidence'] ?>%"></div>
              </div>
            </div>
          </div>

          <?php if ($saved_to_db): ?>
            <div class="alert alert-success mt-3 d-inline-block px-4">
              <i class="bi bi-database-check"></i> Analysis result saved to the database successfully!
              <?php if ($prediction['prediction'] === 'L'): ?>
                <br><i class="bi bi-bell-fill text-danger"></i> <strong>Automatic alert generated for the counselor!</strong>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <a href="ai_analysis.php" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-3">Reset Analysis</a>
        </div>
        <?php endif; ?>

        <div class="card ai-card">
          <div class="ai-card-header">
            <i class="bi bi-robot"></i>
            <h2 class="fw-bold mb-1">Smart Interaction Prediction</h2>
            <p class="opacity-75 mb-0">Powered by Decision Tree Machine Learning Model</p>
          </div>

          <div class="card-body p-5">
            <form method="POST" id="aiForm">

              <?php if ($user_role === 'teacher' && count($my_students) > 0): ?>
              <!-- Student Selector for DB Saving -->
              <div class="mb-4 p-4 rounded-3" style="background:#f0fdf4; border: 2px dashed #22c55e;">
                <div class="form-section-title" style="color:#16a34a;"><i class="bi bi-database-add me-2"></i>Link Analysis to Student (Save Result)</div>
                <select name="child_id" class="form-select">
                  <option value="">— Analyze without saving (optional) —</option>
                  <?php foreach ($my_students as $s): ?>
                    <option value="<?= $s['child_id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="text-muted mt-1 d-block">If you select a student, the analysis result will be automatically saved and linked to them.</small>
              </div>
              <?php endif; ?>

              <!-- Section 1: Demographic & Academic Profile -->
              <div class="form-section-title">
                <i class="bi bi-person-badge me-2"></i>Student Profiling
              </div>
              <div class="row g-4 mb-5">
                <div class="col-md-4">
                  <label class="form-label">Gender</label>
                  <select name="gender" class="form-select">
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Educational Stage</label>
                  <select name="StageID" class="form-select">
                    <option value="lowerlevel">Primary / Lower Level</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Grade Level</label>
                  <select name="GradeID" class="form-select">
                    <?php foreach(['G-02','G-04','G-05','G-06','G-07','G-08','G-09','G-10','G-11','G-12'] as $g): ?>
                    <option value="<?= $g ?>" <?= $g==='G-07'?'selected':'' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Subject (Topic)</label>
                  <select name="Topic" class="form-select">
                    <?php foreach(['IT','Math','Arabic','Science','English','Quran','Spanish','French','History','Biology'] as $t): ?>
                    <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Semester</label>
                  <select name="Semester" class="form-select">
                    <option value="F">First Semester</option>
                    <option value="S">Second Semester</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Section</label>
                  <select name="SectionID" class="form-select">
                    <option value="A">Section A</option>
                    <option value="B">Section B</option>
                    <option value="C">Section C</option>
                  </select>
                </div>
              </div>

              <!-- Section 2: Engagement Indicators -->
              <div class="form-section-title">
                <i class="bi bi-graph-up-arrow me-2"></i>Engagement & Interaction Metrics
              </div>
              <div class="row g-4 mb-5">
                <div class="col-md-6">
                  <div class="range-label">
                    <span>Hands Raised</span>
                    <span class="range-value" id="rh-val">50</span>
                  </div>
                  <input type="range" class="form-range" name="raisedhands" min="0" max="100" value="50"
                    oninput="document.getElementById('rh-val').textContent=this.value">
                </div>
                <div class="col-md-6">
                  <div class="range-label">
                    <span>Resources Visited</span>
                    <span class="range-value" id="vr-val">50</span>
                  </div>
                  <input type="range" class="form-range" name="VisITedResources" min="0" max="100" value="50"
                    oninput="document.getElementById('vr-val').textContent=this.value">
                </div>
                <div class="col-md-6">
                  <div class="range-label">
                    <span>Announcements Viewed</span>
                    <span class="range-value" id="av-val">20</span>
                  </div>
                  <input type="range" class="form-range" name="AnnouncementsView" min="0" max="100" value="20"
                    oninput="document.getElementById('av-val').textContent=this.value">
                </div>
                <div class="col-md-6">
                  <div class="range-label">
                    <span>Discussion Group Participation</span>
                    <span class="range-value" id="di-val">30</span>
                  </div>
                  <input type="range" class="form-range" name="Discussion" min="0" max="100" value="30"
                    oninput="document.getElementById('di-val').textContent=this.value">
                </div>
              </div>

              <!-- Section 3: Parental & Attendance Context -->
              <div class="form-section-title">
                <i class="bi bi-house-heart me-2"></i>External Factors
              </div>
              <div class="row g-4 mb-5">
                <div class="col-md-4">
                  <label class="form-label">Parental Survey Participation</label>
                  <select name="ParentAnsweringSurvey" class="form-select">
                    <option value="Yes">Yes, Parent Answered</option>
                    <option value="No">No, Not Participated</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Parental School Satisfaction</label>
                  <select name="ParentschoolSatisfaction" class="form-select">
                    <option value="Good">Satisfied (Good)</option>
                    <option value="Bad">Not Satisfied (Bad)</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Student Absence</label>
                  <select name="StudentAbsenceDays" class="form-select">
                    <option value="Under-7">Occasional (Under 7 Days)</option>
                    <option value="Above-7">Chronic (Above 7 Days)</option>
                  </select>
                </div>
              </div>

              <!-- Hidden Defaults -->
              <input type="hidden" name="NationalITy" value="KW">
              <input type="hidden" name="PlaceofBirth" value="KuwaIT">
              <input type="hidden" name="Relation" value="Father">

              <div class="text-center pt-4">
                <button type="submit" class="btn btn-analyze">
                  Run Behavior Analysis <i class="bi bi-arrow-right ms-2"></i>
                </button>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<script>
  document.getElementById('aiForm').addEventListener('submit', function() {
    document.getElementById('loading-overlay').style.display = 'flex';
  });
</script>

<?php include 'includes/footer.php'; ?>

