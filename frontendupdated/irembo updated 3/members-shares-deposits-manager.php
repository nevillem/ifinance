<?php
      require 'private/initialize.php';
      $pagename = 'Transactions';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Shares Deposits (<span id="numoffixed">0</span>) </h2>
                  <!-- <a href="#" data-toggle="modal" data-target="#addfixedsavingsmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Share Deposit</a> -->
                </div>
                <div class="card-body mt-3 mb-3">
                <table class="table table-striped table-hover table-bordered" id="dataTables-fixed">
                    <thead>
                      <tr>
                        <th></th>
                        <th>#</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Share Units</th>
                        <!-- <th>Share Type</th> -->
                        <th>Share Amount</th>
                        <th>Reference Number</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody id="shares_user_table">
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
    <script src="middlewares/user/shares.js"></script>
    <?php require_once('partial-components/transactions/shares.php'); ?>

    </script>
  </body>
</html>
