<?php
      require 'private/initialize.php';
      $pagename = 'Loans';
?>
<?php require 'components/head.php'; ?>
   <body id="content">
    <!-- navbar-->
    <?php require_once('components/user/header.php'); ?>
    <div class="d-flex align-items-stretch">
      <?php require_once('components/user/sidebar-loans.php'); ?>
      <div class="page-holder w-100 d-flex flex-wrap">
        <div class="container-fluid px-xl-5">
        <section class="py-5 mt-3">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-5 mb-lg-0">
                <div class="card-header">
                    <h2 class="h6 mb-0 text-uppercase text-center">Loan Applications</h2>
                </div>
                <div class="card-body mt-3 mb-3">
                  <form class="row" id="loanAplicationForm" method="post">
                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Select a Member</label>
                      <select name="memberid" id="memberid" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                      <option disabled selected hidden>Select or Search Member Account</option>
                      </select> 
                      
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Select a Loan Product</label>
                      <select name="loanproduct" id="loanproduct" data-width="100%"  class="border-1 form-control-md input-text" required>
                      <option disabled selected hidden>Select Loan Product</option>

                      </select>
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Amount Being Applied For</label>
                      <input type="number" name="amount" id="amount" placeholder="Enter amount of loan needed" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                      <div class="form-group col-md-5">
                      <label id="sub-account-code" class="form-label">Date of Loan Application</label>
                      <input type="date" name="dateapplied" id="dateapplied" placeholder="Date of Loan Application" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Tenure Period (Months)</label>
                      <input type="number" name="tenureperiod" id="tenureperiod" placeholder="Specify Length of Loan e.g 12" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Grace Period (in days)</label>
                      <input type="number" name="graceperiod" id="graceperiod" placeholder="Specify Loan Grace Period" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Armotization Interval</label>
                      <select name="amornitizationinterval" id="amornitizationinterval" data-width="100%"  class="border-1 form-control-md input-text" required>
                      <option disabled selected hidden>Select Armotization Interval</option>
                      <option value="daily">Daily</option>
                      <option value="weekly">Weekly</option>
                      <option value="monthly">Monthly</option>
                      <option value="annualy">Annualy</option>

                      </select>
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Reason for Loan Application</label>
                      <input type="text" name="reason" id="reason" placeholder="Specify reason for applying for loan" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                      <div class="form-group col-md-2  text-center">
                        <button type="submit" class="btn btn-primary text-center login">Save</button>
                        <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                      </div>
                      
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
    <?php require_once 'components/user/javascript.php'; ?>
    <script src="middlewares/user/header.js"></script>
    <script src="middlewares/user/loans.js"></script>
    <?php require_once('partial-components/members/loans.php'); ?>

    </script>
  </body>
</html>
