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
                    <h2 class="h6 mb-0 text-uppercase text-center">Process Loans</h2>
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
                    <form class="row needs-validation" id="loanProcessingForm" method="post">
                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Choose a Member</label>
                            <select name="selectMember" name="memberid" id="memberid" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Member</option>
                            </select> 
                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Specify Loan to be Processed</label>
                            <select name="loanapplicationid" id="loanapplicationid" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Loan</option>
                            </select>
                            </div>
                            
                            <div class="form-group col-md-2 ">
                            <button type="submit" class="btn btn-primary text-center mt-3 float-right login">Process Loan</button>
                            <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                            </div>
                            
                    </form>
                    </div> 

                    <div id="scheduletable" class=" mt-3 d-none row">
                    <div class="col-md-6 mt-4"> 
                        <label class="form-label">Members Name:</label><span id="membername" class="text-black"> </span>
                    </div> 
                    <div class="col-md-6 mt-4"> 
                        <label class="form-label">Loan Products:</label><span id="loanproduct" class="text-black"> </span>
                    </div>  
                    <div class="col-md-12">
                      <table  class="table lschedules table-bordered" >
                        <thead>
                          <tr>
                            <!-- <th></th> -->
                            
                            <th>Installment Number</th>
                            <th>Date</th>
                            <th>Starting Principal Balance</th>
                            <th>Principal Installment</th>
                            <th>Interest Installment</th>
                            <th>Total Installment</th>
                            <th>Ending Principal Balance</th>
                          </tr>
                        </thead>
                        <tbody id="tableProcess">
                        </tbody>
                      </table>
                        <table class="table table-bordered d-none" id="lamount">
                          <tr>
                          <td colspan="5" >Amount Applied For:</td>
                          <td colspan="2" class="text-right" id="aplliedfor"></td>
                          </tr>

                          <tr>
                          <td colspan="5">Total Interest:</td>
                          <td colspan="2" class="text-right" id="totinterest"></td>
                          </tr>

                          <tr>
                          <td colspan="5">Total Loan Amount:</td>
                          <td colspan="2" class="text-right" id="totamount"></td>
                          </tr>
                        </table>

                      <a href="#" class="btn btn-outline-dark float-right">Download</a>
                    </div>
                    </div>
                  </div>
                    
                </div>

                <div class="tab-pane fade show" id="v-pills-group-loans" role="tabpanel" aria-labelledby="v-pills-group-loans">
                <div class="row mt-3">
                <div class="col-md-12">    
                    <form class="row needs-validation"id="groupLoanProcessingForm"  method="post">
                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Choose a Group</label>
                            <select name="selectGroup" id="groupId" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Group</option>
                            </select> 
                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="account-group" class="form-label">Specify Loan to be Processed</label>
                            <select name="selectLoan" id="groupLoan" data-live-search="true" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select a Loan</option>
                            </select>
                            </div>
                            
                            <div class="form-group col-md-2 ">
                            <button type="submit" class="btn btn-primary text-center float-right mt-3 login">Process Loan</button>
                            <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                            </div>
                            
                    </form>
                    </div> 

                    <div id="groupdiv" class=" mt-3 d-none row">
                    <div class="col-md-6 mt-4"> 
                    <label class="form-label">Group Name:</label><span id="groupname"class="text-black"> </span>
                    </div> 
                    <div class="col-md-6 mt-4"> 
                        <label class="form-label">Loan Products:</label><span id="gloanproduct" class="text-black"> </span>
                    </div> 
                    <div class="col-md-12">
                        
                      <table class="table table-bordered" >
                        <thead>
                          <tr>
                            <!-- <th></th> -->
                            
                            <th>Installment Number</th>
                            <th>Date</th>
                            <th>Starting Principal Balance</th>
                            <th>Principal Installment</th>
                            <th>Interest Installment</th>
                            <th>Monthly Installment</th>
                            <th>Ending Principal Balance</th>
                          </tr>
                        </thead>
                        <tbody id="tablegroupProcess">
                          
                        </tbody>
                        </table>
                        <table class="table table-bordered " id="gamount">
                        <tr>
                            <td colspan="7"></td>
                          </tr>

                          <tr>
                          <td colspan="5">Amount Applied For:</td>
                          <td colspan="2" class="text-right" id="groupaplliedfor"></td>
                          </tr>

                          <tr>
                          <td colspan="5">Total Interest:</td>
                          <td colspan="2" class="text-right" id="grouptotinterest"></td>
                          </tr>

                          <tr>
                          <td colspan="5">Total Loan Amount:</td>
                          <td colspan="2" class="text-right" id="grouptotamount"></td>
                          </tr>
                      </table>

                      <a href="#" class="btn btn-outline-dark float-right">Download</a>
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
    <script src="middlewares/user/loanprocessingIndividual.js"></script>


<script></script>
  </body>
</html>
