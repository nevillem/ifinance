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
                    <h2 class="h6 mb-0 text-uppercase text-center">Loan Calculator</h2>
                    
                  
                </div>
                <div class="card-body mt-3 mb-3">

                <!-- Beginning of Body Content -->
              <div class="row">
                
                <form class="row" id="saccoLoanCalculatorform"method="post">
                      <div class="form-group col-md-5">
                        <label id="" class="form-label">Select Loan Product</label>
                        <select name="loanproduct" id="loanproduct" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>Choose loan product</option>
                        
                        </select> 
                      
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Interest Rate (%) Per Annum</label>
                      <input type="number" name="interestrate" id="interestrate"  class="form-control border-0 shadow form-control-md input-text" readonly>                            
                      </div>

                      <div class="form-group col-md-2  text-center">
                        <button type="submit" class="btn btn-primary mt-3 text-center login">Calculate</button>
                        <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Enter Principal Amount</label>
                      <input type="number" name="amount" id="amount" placeholder="Enter Principal Amount" class="form-control border-0 shadow form-control-md input-text">                            
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Loan Rate Type</label>
                      <input type="text" name="loan_rate_type" id="loan_rate_type"  class="form-control border-0 shadow form-control-md input-text" readonly>                            
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Enter Number of Installments</label>
                      <input type="text" name="installments" id="installments" placeholder="Enter Number of Installments" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                      <div class="form-group col-md-5">
                      <label id="" class="form-label">Select Armotization Interval</label>
                      <select name="amornitizationinterval" id="amornitizationinterval" data-width="100%"  class="border-1 form-control-md input-text">
                      <option disabled selected hidden>Select Armotization Interval</option>
                      <option value="daily">Daily</option>
                      <option value="weekly">Weekly</option>
                      <option value="monthly">Monthly</option>
                      <option value="annualy">Annualy</option>
                      </select>
                      </div>

                      
                      
                </form>

                <div class="col-md-12 mt-3" id="loans">
                      <table class="table loan_calculator table-hover table-bordered d-none" >
                        <thead>
                          <tr>
                            <!-- <th></th> -->
                            
                            <th>Installment Number</th>
                            <th>Starting Principal Balance</th>
                            <th>Principal Installment</th>
                            <th>Interest Installment</th>
                            <th>Total Installment</th>
                            <th>Ending Principal Balance</th>
                          </tr>
                        </thead>
                        <tbody id="loancalculator">
                        </tbody>
                      </table>

                      <table id="tblamount" class="table table-bordered d-none">
                          <tr>
                          <td colspan="4">Total Interest:</td>
                          <td colspan="2" id="interest"></td>
                          </tr>

                          <tr>
                          <td colspan="4">Total Principal + Interest:</td>
                          <td colspan="2" id="totamount"></td>
                          </tr>
                      </table>

                      <a href="#" id="printLoan"class="btn btn-outline-dark float-right">Download</a>
                    </div>
              
            </div>

                
                <!-- End of Body Content -->



        </section>
        </div>
      <?php require_once('components/footer.php') ?>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require_once 'components/user/javascript.php'; ?>
    <script src="middlewares/user/header.js"></script>
    <?php require_once 'partial-components/loans/loan_guarantors.php'; ?>
    <script src="middlewares/user/loanCalculator.js"></script>


<script></script>
  </body>
</html>
