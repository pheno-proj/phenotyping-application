<?php 
session_start();
include 'includes/header.php'; 
?>

  <section id="hero-animated" class="hero-animated d-flex align-items-center">
    <div class="container d-flex flex-column justify-content-center align-items-center text-center position-relative" data-aos="zoom-out">
      <img src="assets/img/custom/12.png" class="img-fluid animated" id="hero-img">
      <h2>Welcome to <span>Digital Phenotyping</span></h2>
      <p>Monitoring Children's Behavior & Academic Performance using AI.</p>
      <div class="d-flex">
        <a href="#about" class="btn-get-started scrollto">Get Started</a>
        <a href="login_register.php" class="btn-watch-video d-flex align-items-center"><i class="bi bi-person"></i><span>Login / Register</span></a>
      </div>
    </div>
  </section>

  <main id="main">

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
      <div class="container" data-aos="fade-up">

        <div class="section-header">
          <h2>About The Project</h2>
          <p>This system uses Digital Phenotyping to analyze children's behavior and academic performance using Decision Tree algorithms.</p>
        </div>

        <div class="row g-4 g-lg-5" data-aos="fade-up" data-aos-delay="200">

          <div class="col-lg-5">
            <div class="about-img">
              <img src="assets/img/custom/2.png" class="img-fluid" alt="">
            </div>
          </div>

          <div class="col-lg-7">
            <h3 class="pt-0 pt-lg-5">Empowering Parents, Teachers, and Counselors.</h3>

            <!-- Tabs -->
            <ul class="nav nav-pills mb-3">
              <li><a class="nav-link active" data-bs-toggle="pill" href="#tab1">Objective</a></li>
              <li><a class="nav-link" data-bs-toggle="pill" href="#tab2">How it Works</a></li>
            </ul><!-- End Tabs -->

            <!-- Tab Content -->
            <div class="tab-content">

              <div class="tab-pane fade show active" id="tab1">
                <p class="fst-italic text-center mb-4">
                  <img src="assets/img/custom/1.png" style="max-width: 200px;" class="mb-3"><br>
                  To detect behavioral patterns and identify possible signs of struggle early on.
                </p>
                <div class="d-flex align-items-center mt-4">
                  <i class="bi bi-check2"></i>
                  <h4>Monitor Academic Performance</h4>
                </div>
                <div class="d-flex align-items-center mt-4">
                  <i class="bi bi-check2"></i>
                  <h4>Analyze Behavioral Traits</h4>
                </div>
                <div class="d-flex align-items-center mt-4">
                  <i class="bi bi-check2"></i>
                  <h4>Generate Early Alerts</h4>
                </div>
              </div><!-- End Tab 1 Content -->

              <div class="tab-pane fade show" id="tab2">
                <p class="fst-italic text-center mb-4">
                  <img src="assets/img/custom/11.png" style="max-width: 200px;" class="mb-3"><br>
                  Data is collected from teachers and processed using Machine Learning.
                </p>
                <div class="d-flex align-items-center mt-4">
                  <i class="bi bi-check2"></i>
                  <h4>Data Collection</h4>
                </div>
                <p>Teachers input grades and behavioral observations.</p>

                <div class="d-flex align-items-center mt-4">
                  <i class="bi bi-check2"></i>
                  <h4>Processing</h4>
                </div>
                <p>The system analyzes trends and applies the Decision Tree algorithm.</p>
              </div><!-- End Tab 2 Content -->

            </div>

          </div>

        </div>

      </div>
    </section><!-- End About Section -->

  </main><!-- End #main -->

<?php include 'includes/footer.php'; ?>
