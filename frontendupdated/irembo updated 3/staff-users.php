<?php
      require 'private/initialize.php';
      $pagename = 'Staff';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Staff Members (<span id="numofstaff">0</span>) </h2>
                  <a href="#" data-toggle="modal" data-target="#addstaff" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Staff</a>
                </div>
                <div class="card-body mt-3 mb-3" id="users-add">
                <table class="table table-striped table-hover table-bordered" id="dataTables-staff">
                    <thead>
                      <tr>
                        <th></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Branch</th>
                        <th>Role</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="staff_table">
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
    <script src="middlewares/header.js"></script>
    <script src="middlewares/staff.js"></script>
    <?php require_once 'partial-components/staff/staff.php'; ?>

    </script>
  </body>
</html>