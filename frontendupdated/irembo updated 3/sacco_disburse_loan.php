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
                    <h2 class="h6 mb-0 text-uppercase text-center">Disburse Loans</h2>
                </div>
                <div class="card-body mt-3 mb-3">
                    <div class="row">
                    <div class="col-md-12">
                    <div class="nav nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="border nav-link active mr-2 px-3 py-2 h6 text-center" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-individual-loans" role="tab" aria-controls="v-pills-home" aria-selected="true">Individual Loan</a>
                        <a class="border nav-link mr-2 px-3 py-2 h6 text-center" id="v-pills-accounts=processing-tab" data-toggle="pill" href="#v-pills-group-loans" role="tab" aria-controls="v-pills-group-loans" aria-selected="false">Groups Loans</a>
                                              
                      </div>
                      
                    </div>
                    </div>

                <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-individual-loans" role="tabpanel" aria-labelledby="v-pills-individual-loans">
               
                <div class="row mt-3">
                    <div class="col-md-12">    
                    <form class="row needs-validation" id="individualLoanDisburseForm" method="post">
                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Choose a Member</label>
                            <select name="members_accountid" id="members_accountid" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Member</option>
                            </select> 
                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Specify Loan to be Disbursed</label>
                            <select name="loanappid" id="indivi_loanappid" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Loan</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Date of Disbursement</label>
                            <input type="date" name="datedisbursed" id="datedisbursed" placeholder="Date of Disbursement" class="form-control border-0 shadow form-control-md input-text" required>                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Specify Account to be Disburse From</label>
                            <select name="accountfrom" id="accountfrom" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Specify Account to be Disburse From</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Specify Account to be Disburse to</label>
                            <select name="memberaccountid" id="memberaccountid" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Specify Account to be Disburse to</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label class="form-label">Mode of Payment</label>
                            <select name="mop" id="m_mop" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select Mode of Payment</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Amount to Disburse</label>
                            <input type="text" name="amount" id="amount" placeholder="Specify amount to disburse" class="form-control border-0 shadow form-control-md input-text" required>                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Cheque Number</label>
                            <input type="text" name="banks" id="banks" placeholder="Enter Cheque Number" class="form-control border-1 shadow form-control-md input-text">                            
                            </div>

                            <div class="col-md-5">
                            <label for="comment" class="form-label">Comments/Notes</label>
                            <div class="form-group mb-4">
                            <textarea class="border-1 form-control-md input-text"name="notes" style="width: 100%;" placeholder="Write Notes/Comments" id="notes" rows="3"></textarea>
                            </div>
                            </div>
                            
                            <div class="form-group col-md-2 ">
                            <button type="submit" class="btn btn-primary text-center mt-4 float-right login">Disburse Loan</button>
                            <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                            </div>
                            
                    </form>
                    </div> 
                    
                  </div>
                    
                </div>

                <div class="tab-pane fade show" id="v-pills-group-loans" role="tabpanel" aria-labelledby="v-pills-group-loans">
                <div class="row mt-3">
                    <div class="col-md-12">    
                    <form class="row needs-validation" id="groupLoanDisburseForm" method="post">
                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Choose a group</label>
                            <select name="groupId" id="groupId" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Group</option>
                            </select> 
                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Specify Loan to be Disbursed</label>
                            <select name="loanappid" id="groupLoan" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Loan</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Date of Disbursement</label>
                            <input type="date" name="datedisbursed" id="datedisbursed" placeholder="Date of Disbursement" class="form-control border-0 shadow form-control-md input-text" required>                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Specify Account to be Disburse From</label>
                            <select name="accountfrom" id="groupaccountfrom" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Specify Account to be Disburse From</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Specify Account to be Disburse to</label>
                            <select name="memberaccountid" id="groupaccountid" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Specify Account to be Disburse to</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label class="form-label">Mode of Payment</label>
                            <select name="mop" id="g_mop" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select Mode of Payment</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Amount to Disburse</label>
                            <input type="text" name="amount" id="amount" placeholder="Specify amount to disburse" class="form-control border-0 shadow form-control-md input-text" required>                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Cheque Number</label>
                            <input type="text" name="banks" id="banks" placeholder="Enter Cheque Number" class="form-control border-1 shadow form-control-md input-text" >                            
                            </div>

                            
                            <div class="col-md-5">
                            <label for="comment" class="form-label">Comments/Notes</label>
                            <div class="form-group mb-4">
                            <textarea class="border-1 form-control-md input-text"name="notes" style="width: 100%;" placeholder="Write Notes/Comments" id="notes" rows="3"></textarea>
                            </div>
                            </div>
                            
                            <div class="form-group col-md-2 ">
                            <button type="submit" class="btn btn-primary text-center mt-4 float-right login">Disburse Loan</button>
                            <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                            </div>
                            
                    </form>
                    </div> 
                    
                  </div>
                    
                </div>
                    
                </div>
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
    <?php require_once 'partial-components/loans/loan_guarantors.php'; ?>
    <script src="middlewares/user/loanDisbursement.js"></script>


<script></script>
  </body>
</html>
