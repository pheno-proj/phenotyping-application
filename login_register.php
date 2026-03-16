<?php 
session_start(); 
include 'includes/header.php'; 
?>

<style>
  .auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 120px 0 60px 0;
    background-color: #f6f9ff;
  }
  .auth-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    overflow: hidden;
    border: none;
  }
  .auth-illustration {
    background: linear-gradient(135deg, var(--color-primary) 0%, #764ba2 100%);
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
  }
  .auth-illustration img {
    max-width: 100%;
    height: auto;
    filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
  }
  .auth-form {
    padding: 50px 40px;
  }
  .nav-pills .nav-link {
    border-radius: 10px;
    padding: 12px 25px;
    margin: 0 10px;
    font-weight: 600;
  }
  .nav-pills .nav-link.active {
    background: linear-gradient(135deg, var(--color-primary) 0%, #764ba2 100%);
  }
  .form-control, .form-select {
    border-radius: 10px;
    padding: 12px 18px;
    border: 1px solid #e0e7ff;
    background-color: #f8faff;
  }
  .form-control:focus, .form-select:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 0.25rem rgba(109, 40, 217, 0.1);
    background-color: #fff;
  }
  .btn-auth {
    background: linear-gradient(135deg, var(--color-primary) 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 14px 30px;
    color: #fff;
    font-weight: 700;
    width: 100%;
    margin-top: 20px;
    transition: all 0.3s ease;
  }
  .btn-auth:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(109, 40, 217, 0.3);
    color: #fff;
  }
  .role-btn {
    flex: 1;
    padding: 20px 10px;
    border: 2px solid #e0e7ff;
    border-radius: 15px;
    background: #fff;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
  .role-btn:hover {
    border-color: var(--color-primary);
    background: #fdfdff;
  }
  .role-btn.active {
    border-color: var(--color-primary);
    background: linear-gradient(135deg, var(--color-primary) 0%, #764ba2 100%);
    color: #fff;
    transform: scale(1.05);
  }
  .role-btn i {
    font-size: 28px;
    display: block;
    margin-bottom: 10px;
  }
</style>

<section id="auth-section" class="auth-container">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-11 col-xl-10">
        <div class="auth-card" data-aos="zoom-in">
          <div class="row g-0">
            
            <!-- Illustration Side -->
            <div class="col-lg-5 auth-illustration d-none d-lg-flex">
              <img src="assets/img/custom/10.png" alt="Auth Illustration" class="img-fluid mb-4">
              <h3 class="text-center">Welcome Back!</h3>
              <p class="text-center opacity-75">Join our platform to monitor and analyze behavioral patterns effectively.</p>
            </div>

            <!-- Form Side -->
            <div class="col-lg-7 auth-form">
              
              <!-- Notifications are now handled by SweetAlert2 in footer.php -->
              
              <ul class="nav nav-pills mb-5 justify-content-center" id="authTabs">
                <li class="nav-item">
                  <a class="nav-link active" data-bs-toggle="pill" href="#login">Login</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="pill" href="#register">Register</a>
                </li>
              </ul>

              <div class="tab-content">
                
                <!-- Login Form -->
                <div class="tab-pane fade show active" id="login">
                  <h4 class="mb-4 fw-bold">Login to Your Account</h4>
                  <form action="auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="mb-3">
                      <label class="form-label small fw-bold">Select Your Role</label>
                      <select class="form-select" name="user_type" required>
                        <option value="">Choose...</option>
                        <option value="admin">Administrator</option>
                        <option value="teacher">Teacher / Faculty</option>
                        <option value="parent">Parent / Guardian</option>
                        <option value="counselor">Counselor / Expert</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label small fw-bold">Email or Username</label>
                      <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                        <input type="text" class="form-control border-start-0" name="email" placeholder="name@example.com" required>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label small fw-bold">Password</label>
                      <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-start-0" name="password" placeholder="••••••••" required>
                      </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label small" for="rememberMe">Remember me</label>
                      </div>
                      <a href="#" class="small text-primary">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-auth">Sign In Now</button>
                  </form>
                </div>

                <!-- Register Form -->
                <div class="tab-pane fade" id="register">
                  <h4 class="mb-4 fw-bold">Create New Account</h4>
                  
                  <div class="d-flex gap-3 mb-4">
                    <div class="role-btn" onclick="selectRole('teacher')">
                      <i class="bi bi-person-workspace text-primary"></i>
                      <span class="small fw-bold">Teacher</span>
                    </div>
                    <div class="role-btn" onclick="selectRole('parent')">
                      <i class="bi bi-people text-info"></i>
                      <span class="small fw-bold">Parent</span>
                    </div>
                    <div class="role-btn" onclick="selectRole('counselor')">
                      <i class="bi bi-shield-lock text-success"></i>
                      <span class="small fw-bold">Counselor</span>
                    </div>
                  </div>

                  <form action="auth.php" method="POST" id="registerForm">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="user_type" id="selectedRole" value="">

                    <div class="row g-3">
                      <div class="col-md-12">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" class="form-control" name="full_name" placeholder="At least 3 characters" minlength="3" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" placeholder="05XXXXXXXX" pattern="05[0-9]{8}" title="Saudi phone number must start with 05 and be 10 digits" required>
                      </div>
                      <div class="col-md-12">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Min. 8 characters" minlength="8" required>
                      </div>

                      <div class="col-md-12 teacher-field d-none">
                        <label class="form-label small fw-bold">Specialization</label>
                        <input type="text" class="form-control" name="specialization" placeholder="e.g. Science, Behavioral Analysis">
                      </div>

                      <div class="col-md-12 parent-field d-none">
                        <label class="form-label small fw-bold">Home Address</label>
                        <textarea class="form-control" name="address" rows="2" placeholder="Full address details"></textarea>
                      </div>

                      <div class="col-md-12 mt-4">
                        <div class="form-check">
                          <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                          <label class="form-check-label small" for="agreeTerms">I agree to the <a href="#">Terms of Service</a></label>
                        </div>
                        <button type="submit" class="btn btn-auth">Complete Registration</button>
                      </div>
                    </div>
                  </form>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  function selectRole(role) {
    document.getElementById('selectedRole').value = role;
    document.querySelectorAll('.role-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    document.querySelectorAll('.teacher-field, .parent-field').forEach(field => field.classList.add('d-none'));
    if (role === 'teacher') {
      document.querySelectorAll('.teacher-field').forEach(field => field.classList.remove('d-none'));
    } else if (role === 'parent') {
      document.querySelectorAll('.parent-field').forEach(field => field.classList.remove('d-none'));
    }
  }

  document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (!document.getElementById('selectedRole').value) {
      e.preventDefault();
      showAlert('Role Required', 'Please select a role (Teacher, Parent, or Counselor) before continuing.', 'warning');
    }
  });
</script>

<?php include 'includes/footer.php'; ?>
