<?php
      require 'private/initialize.php';
      $pagename = 'Password';
?>
<?php require 'components/head.php'; ?>
  <body id="content">
    <!-- navbar-->
    <?php require_once('components/header.php'); ?>
    <div class="d-flex align-items-stretch">
      <?php require_once('components/sidebar.php'); ?>
      <div class="page-holder w-100 d-flex flex-wrap">
        <div class="container-fluid px-xl-5">
        <section class="py-5 mt-3">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-5 mb-lg-0">
                <div class="card-header">
                    <h2 class="h6 mb-0 text-uppercase text-center">Change Password</h2>
                </div>
                <div class="card-body mt-3 mb-3">
                <form class="col-6 offset-lg-3" id="newpassword" novalidate oninput='conpassword.setCustomValidity(conpassword.value != newpass.value ? "Passwords do not match." : "")'>
                <div class="form-group">
                    <input type="password" class="form-control" name="oldpass" id="oldpass" placeholder="enter old password" required autocomplete="off">
                  </div>
                  <div class="form-group">
                    <input type="password" class="form-control" name="newpass" id="newpass" placeholder="enter new password"required autocomplete="off">
                  </div>
                  <div class="form-group">
                    <input type="password" class="form-control" name="conpassword" id="conpassword" placeholder="confirm password" required autocomplete="off">
                  </div>
                
                  <button type="submit" class="btn btn-danger offset-lg-4 login">Change Password</button>
                  <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                </form>
                </div>
              </div>
            </div>
          </div>
        </section>
        </div>
      <?php require_once('components/footer.php') ?>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require_once 'components/javascript.php'; ?>
    <script src="middlewares/header.js"></script>
    <script src="middlewares/password.js"></script>
    </script>
  </body>
</html>
