<?php
      require 'private/initialize.php';
      $pagename = 'Activity';
?>
<?php require 'components/head.php'; ?>
   <body id="content">
    <!-- navbar-->
    <?php require_once('components/user/header.php'); ?>
    <div class="d-flex align-items-stretch">
      <?php require_once('components/user/sidebar.php'); ?>
      <div class="page-holder w-100 d-flex flex-wrap">
        <div class="container-fluid px-xl-5">
        <section class="py-5 mt-3">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-5 mb-lg-0">
                <div class="card-header">
                    <h2 class="h6 mb-0 text-uppercase text-center">Number Of Logins (<span id="activitylogin">0</span>) </h2>
                </div>
                <div class="card-body mt-3 mb-3">
                <table class="table table-striped table-hover table-bordered" id="dataTables-login">
                    <thead>
                      <tr>
                        <th></th>
                        <th>#</th>
                        <th>Ip Address</th>
                        <th>Operating System Or Broswer</th>
                        <th>Date and Time</th>
                      </tr>
                    </thead>
                    <tbody id="login_table">
                    </tbody>
                  </table>
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
    <script src="middlewares/user/header.js"></script>
    <script src="middlewares/user/login-times.js"></script>

    </script>
  </body>
</html>
