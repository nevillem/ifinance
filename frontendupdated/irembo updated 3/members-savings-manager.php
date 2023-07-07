<?php
      require 'private/initialize.php';
      $pagename = 'Savings';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Savings (<span id="numofsavings">0</span>) </h2>
                </div>
                <div class="card-body mt-3 mb-3">
                  <!-- <div class="row form-horizontal">

                    <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">
                      <div class="form-group has-success d-inline-flex">
                        <label class="form-label col-sm-4 " for="id2">Minimum Date:</label>
                        <div class="col-sm-8">
                          <input  name="min" id="min"  class="form-control border-0 shadow form-control-md input-text">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">
                      <div class="form-group has-success d-inline-flex">
                        <label class="form-label col-sm-4 " for="id2">Maximum Date:</label>
                        <div class="col-sm-8">
                          <input  id="max" name="max"  class="form-control border-0 shadow form-control-md input-text">
                        </div>
                      </div>
                    </div>
                  </div> -->
                <table class="table table-striped table-hover table-bordered display nowrap" id="dataTables-savings">
                    <thead>
                      <tr>
                        <th></th>
                        <th>#</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Amount</th>
                        <th>P/M</th>
                        <th>Deposited By</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Receipt-No</th>
                      </tr>
                    </thead>
                    <tbody id="savings_table">
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
    <script src="middlewares/user/saving-manager.js"></script>

    </script>
  </body>
</html>
