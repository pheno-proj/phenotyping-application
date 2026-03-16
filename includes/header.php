<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Migration: Ensure 'role' exists if 'user_type' is set (for old sessions)
if (isset($_SESSION['user_id']) && !isset($_SESSION['role']) && isset($_SESSION['user_type'])) {
    $_SESSION['role'] = $_SESSION['user_type'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Digital Phenotyping</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Source+Sans+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Variables CSS Files. Uncomment your preferred color scheme -->
  <!-- <link href="assets/css/variables.css" rel="stylesheet"> -->
  <!-- <link href="assets/css/variables-blue.css" rel="stylesheet"> -->
  <!-- <link href="assets/css/variables-green.css" rel="stylesheet"> -->
  <!-- <link href="assets/css/variables-orange.css" rel="stylesheet"> -->
  <link href="assets/css/variables-purple.css" rel="stylesheet">
  <!-- <link href="assets/css/variables-red.css" rel="stylesheet"> -->
  <!-- <link href="assets/css/variables-pink.css" rel="stylesheet"> -->

  <!-- Template Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top" data-scrollto-offset="0">
    <div class="container-fluid d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center scrollto me-auto me-lg-0">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.png" alt=""> -->
        <h1>Phenotyping<span>.</span></h1>
      </a>

      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto" href="index.php#hero-animated">Home</a></li>
          <li><a class="nav-link scrollto" href="index.php#about">About</a></li>
          
          <?php if (isset($_SESSION['user_id'])): ?>
            <?php 
              $user_role = $_SESSION['role'] ?? $_SESSION['user_type'] ?? 'guest';
              $dashboard_url = 'dashboard_' . $user_role . '.php'; 
              $can_access_ai = in_array($user_role, ['admin', 'teacher', 'counselor']);
            ?>
            <li><a class="nav-link scrollto" href="<?= $dashboard_url ?>">Dashboard</a></li>
            <?php if ($can_access_ai): ?>
              <li><a class="nav-link scrollto text-primary fw-bold" href="ai_analysis.php">AI Analysis</a></li>
            <?php endif; ?>
            <li><a class="nav-link scrollto text-danger" href="logout.php">Logout</a></li>
          <?php else: ?>
            <li><a class="nav-link scrollto" href="login_register.php">Login / Register</a></li>
          <?php endif; ?>
        </ul>
        <i class="bi bi-list mobile-nav-toggle d-none"></i>
      </nav><!-- .navbar -->

      <?php if (!isset($_SESSION['user_id'])): ?>
        <a class="btn-getstarted scrollto" href="login_register.php">Get Started</a>
      <?php else: ?>
        <?php $user_role = $_SESSION['role'] ?? $_SESSION['user_type'] ?? 'guest'; ?>
        <a class="btn-getstarted scrollto" href="dashboard_<?= $user_role ?>.php">My Account</a>
      <?php endif; ?>

    </div>
  </header><!-- End Header -->
