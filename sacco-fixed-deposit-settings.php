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
                    <h2 class="h6 mb-0 text-uppercase text-center">Fixed Deposit Settings </h2>
                </div>

                <div class="card-body mt-3 mb-3">
                  <form class="row mt-2 needs-validation" novalidate id="fixeddepositform" method="post">

                                          <div class="form-group col-6">
                                          <lable id="f-deposit-account" class="form-label">Select a fixed deposit account</lable>
                                          <select name="account" id="fixedepositaccount"  class="border-1 form-control-md input-text" required>
                                          <option disabled selected hidden>Search or Select Account</option>

                                        </select>
                                          </div>

                                          <div class="form-group col-6">
                                          <lable id="rate-anum" class="form-label">Interest Rate Per Anum</lable>
                                          <input type="number" name="rate" id="rate" required placeholder="Enter rate." class="form-control border-0 shadow form-control-md input-text">
                                          </div>

                                          <div class="form-group col-6">
                                          <lable id="calmode" class="form-label">Interest Calculation Mode</lable>
                                          <select name="interest_calc_mode" id="calc-mode"  class="border-1 form-control-md input-text" required>
                                          <option disabled selected hidden>Select Mode</option>
                                          <option value="simple">Simple</option>
                                          <option value="compound">Compound</option>

                                        </select>

                                          </div>

                                          <div class="form-group col-6">
                                          <lable id="Loan" class="form-label">Interest Expense Account</lable>
                                          <select name="interest_expense_account" id="exp_interest"  class="border-1 form-control-md input-text" required>
                                          <option disabled selected hidden>Select Expense Account</option>

                                          </select>

                                          </div>

                                          <div class="form-group col-6">
                                          <lable id="p-Account" class="form-label">Interest Payable Account</lable>
                                          <select name="interest_payable_acc" id="payableAccount"  class="border-1 form-control-md input-text" required>
                                          <option disabled selected hidden>Select Account</option>

                                          </select>
                                          </div>

                                          <div class="form-group col-6">
                                          <lable id="p-Account" class="form-label">How Interest is Earned</lable>
                                          <select name="interest_accumulation_interval" id="interest-interval"  class="border-1 form-control-md input-text" required>
                                          <option disabled selected hidden>Select Interval</option>
                                          <option value="weekly">Weekly</option>
                                          <option value="monthly">Monthly</option>
                                          <option value="daily">Daily</option>

                                          </select>
                                          </div>

                                          <div class="form-group col-md-12 text-right">
                                            <button type="submit" class="align-self-start btn btn-primary text-center login">Save Settings</button>
                                            <div class="loading p-2 col-xs-1" style="display:none" align="right"><div class="loader"></div></div>
                                          </div>

                                        </form>
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
