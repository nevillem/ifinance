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
                    <h2 class="h6 mb-0 text-uppercase text-center">Cancel Loan Application</h2>
                    
                  
                </div>
                <div class="card-body mt-3 mb-3">

                <!-- Beginning of Body Content -->
              <div class="row">
                
                <form class="row col-md-12" id="LoanCancelationForm"method="post">
                      
                    <div class="form-group col-md-5">
                        <label id="" class="form-label">Select a Member</label>
                        <select name="memberid" id="memberid" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>Select a Member</option>
                        </select> 
                      
                    </div>
                
                    <div class="form-group col-md-5">
                        <label id="" class="form-label">Select Loan to Cancel</label>
                        <select name="loanappid" id="loanapplicationid" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>Select Loan to Cancel</option>
                        
                        </select> 
                      
                      </div>

                      <div class="form-group col-md-2  text-center">
                        <button type="submit" class="btn btn-primary mt-4 text-center login">Apply changes</button>
                        <div class="cancelLoader d-none loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                      </div>
                      

                <div class="row col-md-12 mt-4 px-5">
                    <h6 class="mb-2">Choose Actions</h6>

                    <div class="form-check col-md-12 mb-3 form-label">
                    <input class="form-check-input" type="checkbox" value="entire loan" name="cancel_action">
                    <label class="form-check-label" for="flexCheckDefault">
                        Cancel Entire Loan
                    </label>
                    </div>
                
                    <div class="form-check col-md-12 mb-3 form-label">
                    <input class="form-check-input" type="checkbox" value="loan processing" name="cancel_action" >
                    <label class="form-check-label" for="flexCheckDefault">
                        Cancel Loan Processing
                    </label>
                    </div>

                    <div class="form-check col-md-12 mb-3 form-label">
                    <input class="form-check-input" type="checkbox" value="loan approval" name="cancel_action" >
                    <label class="form-check-label" for="flexCheckDefault">
                        Cancel Loan Approval
                    </label>
                    </div>

                    <!-- <div class="form-check col-md-12 mb-3 form-label">
                    <input class="form-check-input" type="checkbox" value="loan disbursment" name="cancel_action" >
                    <label class="form-check-label" for="flexCheckDefault">
                        Cancel Loan Disbursment
                    </label>
                    </div> -->


                    
                </div>
                <div class="col-md-6 mt-3">
                        <label for="reason" class="form-label">Reason for Cancelling</label>
                        <div class="form-group mb-4 ml-0">
                        <textarea class="border-1 form-control-md input-text" name="reason" style="width: 100%;" placeholder="State reason for cancelling" id="comment" rows="3"></textarea>
                        </div>
                    </div>
                            
              
            </div>

            </form>

                <!-- End of Body Content -->



        </section>
        </div>
      <?php require_once('components/footer.php') ?>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require_once 'components/user/javascript.php'; ?>
    <script src="middlewares/user/header.js"></script>
    <script src="middlewares/user/loanCancelation.js"></script>


<script></script>
  </body>
</html>
