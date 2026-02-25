  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">

    <div class="footer-content">
      <div class="container">
        <div class="row">

          <div class="col-lg-3 col-md-6">
            <div class="footer-info">
              <h3>Phenotyping Project</h3>
              <p>
                <strong>Phone:</strong> +05 123 456 78<br>
                <strong>Email:</strong> info@example.com<br>
              </p>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 footer-links">
            <h4>Useful Links</h4>
            <ul>
              <li><i class="bi bi-chevron-right"></i> <a href="index.php">Home</a></li>
              <li><i class="bi bi-chevron-right"></i> <a href="#about">About us</a></li>
              <li><i class="bi bi-chevron-right"></i> <a href="login_register.php">Login</a></li>
            </ul>
          </div>

        </div>
      </div>
    </div>

    <div class="footer-legal text-center">
      <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">

        <div class="d-flex flex-column align-items-center align-items-lg-start">
          <div class="copyright">
            &copy; Copyright <strong><span>Phenotyping</span></strong>. All Rights Reserved
          </div>
        </div>

      </div>
    </div>

  </footer><!-- End Footer -->

  <a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <!-- Global SweetAlert2 Logic -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: '<?= htmlspecialchars($_SESSION['success']) ?>',
          confirmButtonColor: '#6d28d9'
        });
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: '<?= htmlspecialchars($_SESSION['error']) ?>',
          confirmButtonColor: '#6d28d9'
        });
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
    });

    // Helper function for custom alerts
    function showAlert(title, text, icon = 'info') {
      Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonColor: '#6d28d9'
      });
    }
  </script>

</body>

</html>
