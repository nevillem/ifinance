<?php
      require 'private/initialize.php';
      $pagename = 'Settings';
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
                    <h2 class="h6 mb-0 text-uppercase text-center"> Payment Modes </h2>
                </div>

                <div class="card-body mt-3 mb-3">

                  <form id="paymentmethodform" class="row needs-validation" action="">

                    <div class="form-group col-md-6 ml-3">
                      <lable id="paymentmode" class="form-label">Payment Mode</lable>
                      <input type="text" name="payment_method" id="payment_method"class="form-control border-0 form-control-md input-text" placeholder="Enter Payement Mode" required>
                    </div>

                    <div class="col-md-3 align-self-center text-right">
                      <button type="submit" class="btn btn-primary">
                        Save
                      </button>
                      <div class="loading p-2 col-xs-1" align="right" style="display:none;"><div class="loader"></div></div>

                    </div>

                  </form>

                    <div class="col-12">
                        <h6>Payment Methods</h6>
                    <table id="payment_methods_table" class="table table-bordered table-striped table-light">
                      <thead>
                        <tr>
                          <th>
                            Payment Mode
                          </th>
                          <th>
                            Action
                          </th>
                        </tr>
                      </thead>
                      <tbody id="payment_methods">

                      </tbody>
                    </table>

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
    <script src="middlewares/settings.js"></script>
    <?php require_once('partial-components/settings/settings.php'); ?>
  </body>
</html>
