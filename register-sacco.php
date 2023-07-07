<?php
        require 'private/initialize.php';
        $pagename = 'Register';
?>
<?php require 'components/head.php'; ?>
  <body class="no-display-for-devices">
    <div class="page-holder d-flex align-items-center">
      <div class="container">
        <div class="row align-items-center py-5">
          <div class="col-5 col-lg-7 mx-auto mb-0 mb-lg-0 mt-0">
            <div class="pr-lg-5"><img src="assets/img/login.png" alt="" class="img-fluid"></div>
          </div>
          <div class="col-lg-5 px-lg-4">
            <img src="assets/img/logo.png" class="img-center img-fuild" height="80px" alt="">
            <div class="card mt-0 mb-2">
              <div class="card-header">
                <p class="text-muted h-900 text-center">Please Sign Up To Register Your SACCO</p>
              </div>
              <div class="card-body mt-0">
                <form id="registerForm" method="post" class="form-horizontal" novalidate>
                  <div class="form-group mb-3">
                    <input type="text" name="name" id="name" placeholder="Enter Valid SACCO Full Name" class="form-control border-0 shadow form-control-lg">
                  </div>
                  <div class="form-group mb-3">
                    <input type="email" name="email" id="email" placeholder="Enter Valid SACCO Email" class="form-control border-0 shadow form-control-lg text-dark">
                  </div>

                  <div class="form-group mb-3">
                    <input type="password" name="password" id="password" placeholder="Enter Password" class="form-control border-0 shadow form-control-lg text-dark">
                  </div>
                  <div class="form-group mb-3">
                    <input type="password" name="copassword" id="copassword" placeholder="Confirm Password" class="form-control border-0 shadow form-control-lg text-dark">
                  </div>
                  <div class="form-group mb-2">
                    <div class="custom-control custom-checkbox">
                      <input id="terms" type="checkbox" name="terms"  class="custom-control-input">
                      <label for="terms" class="custom-control-label">Agree to terms and conditions</label>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-outline-warning btn-block login shadow px-5">Register</button>
                  <div class="loading p-2 col-xs-1" align="center">
                    <div class="loader"></div>
                  </div>
                </form>
                <p class="text-center mt-3 text-capitalize"><a href="auth">Click Here</a> to Login into SACCO</p>
              </div>
            </div>
          </div>
        </div>
        <div class="float-center ml-5">
          <p class="mt-0 mb-0 text-center"><span class="text-dark">Powered by</span> <a href="#" class="external text-warning">MOB<i class="fa fa-info-circle text-success" aria-hidden="true"></i>TUNGO</a> <br/> <span class="text-dark">&copy; <?php echo $year; ?> Ahu</span>riire (U) LTD</p>
      </div>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require 'components/javascript.php'; ?>
    <script src="middlewares/register.js"></script>
  </body>
</html>
