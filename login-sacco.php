<?php
      require 'private/initialize.php';
      $pagename = 'Login';
?>
<?php require 'components/head.php'; ?>
  <body class="no-display-for-devices">
    <div class="page-holder d-flex align-items-center">
      <div class="container">
        <div class="row align-items-center py-5">
          <div class="col-5 col-lg-7 mx-auto mb-5 mb-lg-0 mt-5">
            <div class="pr-lg-5"><img src="assets/img/login.png" alt="" class="img-fluid" loading="lazy"></div>
          </div>
          <div class="col-lg-5 px-lg-4">
            <img src="assets/img/logo.png" class="img-center img-fuild" height="80px" alt="" loading="lazy">
            <div class="card mt-4 mb-5">
              <div class="card-header">
                <p class="text-muted text-center">Please Login Into Your Account</p>
              </div>
              <div class="card-body mt-0">
                <form id="loginForm" method="post" class="form-horizontal" novalidate>
                  <div class="form-group mb-4">
                    <input type="email" name="username" id="username" placeholder="Enter Username" class="form-control border-0 shadow form-control-lg" autocomplete="off">
                  </div>
                  <div class="form-group mb-4">
                    <input type="password" name="password" id="password" placeholder="Enter Password" class="form-control border-0 shadow form-control-lg text-violet" autocomplete="off"> <br>
                  <div class="custom-control custom-checkbox ml-1">
                  <input id="showpass" type="checkbox" class="custom-control-input">
                  <label for="showpass" class="custom-control-label">Show password</label>
                </div>
                  </div>
                  <button type="submit" class="btn btn-outline-warning login btn-block shadow px-5">Login</button>
                  <div class="loading p-2 col-xs-1" align="center">
                    <div class="loader"></div>
                  </div>
                </form>
                <p class="text-center mt-3 text-capitalize"><a href="forgotPassword">Click Here</a> to recover password</p>
              </div>
            </div>
          </div>
        </div>
        <div class="float-center ml-5">
        <p class="mt-5 mb-0 text-center"><span class="text-dark">Powered by</span> <a href="#" class="external text-warning">Mob<i class="fa fa-info-circle text-success" aria-hidden="true"></i>tungo</a> <br/> <span class="text-dark">&copy; <?php echo $year; ?> Ahu</span>riire (U) LTD</p>
      </div>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require 'components/javascript.php'; ?>
    <script src="middlewares/login.js"></script>
    <script>
      $("#showpass").click(function() {
          var x = document.getElementById("password");
          if (x.type === "password") {
            x.type = "text";
          } else {
            x.type = "password";
          }
        })
        
    </script>
  </body>
</html>
