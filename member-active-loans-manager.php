<?php
      require 'private/initialize.php';
      $pagename = 'Loans-Active';
?>
<?php require 'components/head.php'; ?>
   <body id="content">
    <!-- navbar-->
    <?php require_once('components/user/header.php'); ?>
    <div class="d-flex align-items-stretch">
      <?php require_once('components/user/sidebar-manager.php'); ?>
      <div class="page-holder w-100 d-flex flex-wrap">
        <div class="container-fluid px-xl-5">
        <section class="py-5 mt-3">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-5 mb-lg-0">
                <div class="card-header">
                    <h2 class="h6 mb-0 text-uppercase text-center">Active Loans (<span id="numofloansapps">0</span>) </h2>
                    <!-- <a href="#" data-toggle="modal" data-target="#addschedulemodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-danger float-left">Loan Calculator</a>
                  <a href="#" data-toggle="modal" data-target="#addloanmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Loan Application</a> -->
                </div>
                <div class="card-body mt-3 mb-3">
                  <!-- <table class="table row">
                        <tbody class="col-6 row">
                          <tr class="col-6">
                            <td>Minimum date:</td>
                            <td><input type="text" class="form-control-sm" id="min" name="min"></td>
                        </tr>
                        <tr class="col-6">
                            <td>Maximum date:</td>
                            <td><input type="text" class="form-control-sm" id="max" name="max"></td>
                        </tr>
                    </tbody>
                  </table> -->
                
                <table class="table table-striped table-hover table-bordered display nowrap" id="dataTables-loans-active">
                    <thead>
                      <tr>
                        <th></th>
                        <th>#</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Loans No.</th>
                        <th>Payment Amount</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <!-- <th>Next Payment Date</th> -->
                        <th>Approve Date</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="loans_active_table">
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
    <?php require_once 'components/user/javascript.php'; ?>
    <script src="middlewares/user/header.js"></script>
    <script src="middlewares/user/loans-manager.js"></script>
    <?php require_once('partial-components/members/loans.php'); ?>

    </script>
  </body>
</html>
