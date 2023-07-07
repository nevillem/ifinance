<?php
      require 'private/initialize.php';
      $pagename = 'Income';
?>
<?php require 'components/head.php'; ?>
   <body id="content">
    <!-- navbar-->
    <?php require_once('components/user/header.php'); ?>
    <div class="d-flex align-items-stretch">
      <?php require_once('components/user/sidebar.php'); ?>
      <div class="page-holder w-100 d-flex flex-wrap">
        <div class="container-fluid px-xl-5">
        <section class="py-5 mt-3">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-5 mb-lg-0">
                <div class="card-header">
                    <h2 class="h6 mb-0 text-uppercase text-center">Bills</h2>
                    
                  <a href="#" data-toggle="modal" data-target="#saccobills_modal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Add New Bill</a>
                </div>
                <div class="card-body mt-3 mb-3">
                    <form class="row" id="transferToClientForm" action="#" method="post">
                        <div class="col-md-5">
                        <div class="form-group mb-4">
                        <label for="selectMemberTransfer" class="form-label">Select a Vendor</label>
                            <select name="vendor" id="vendorbilldata" class="border-1 form-control-md input-text">
                            <option value="" disabled selected hidden>Select a Vendor</option>
                            </select>                
                        </div>
                        </div>
                    </form>
                    
                    <table class="table table-hover table-bordered" id="dataTables-sacco-bill">
                        <thead>
                        <tr>
                            
                            <th>Bill Ref. No</th>
                            <th>Expense Account</th>
                            <th>Due Date</th>
                            <th>Amount Billed</th>
                            <th>Description</th>
                            <th>Mark</th>
                        </tr>
                        </thead>
                        <tbody id="billsData">
                        
                        </tbody>
                    </table> 

                    <form class="row needs-validation" id="tellerbillpaymentform" method="post">
                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Account to Spend From</label>
                            <select name="accounttospendfrom" id="accounttospendfrom" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select Account to Spend From</option>
                            </select> 
                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Amount to pay</label>
                            <input type="hidden" name="billid" id="billid"  class="form-control border-0 shadow form-control-md input-text" required>                            
                            <input type="number" name="amount" id="amount" placeholder="Amount" class="form-control border-0 shadow form-control-md input-text" required>                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Date of Transaction</label>
                            <input type="date" name="transdate" id="transdate" placeholder="Select Date of Transaction" class="form-control border-0 shadow form-control-md input-text" required>                            
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Mode of Payment</label>
                            <select name="mop" id="mop" data-width="100%"  class="border-1 form-control-md input-text" required>
                            <option disabled selected hidden>Select Mode of Payment</option>
                            </select>
                            </div>

                            <div class="form-group col-md-5">
                            <label id="" class="form-label">Notes/Comments</label>
                            <textarea type="text" name="notes" id="notes" placeholder="Write Notes" class="form-control border-1 shadow form-control-md input-text" required></textarea>                           
                            </div>
                            
                            <div class="form-group col-md-2 ">
                            <button type="submit" class="btn btn-primary text-center mt-4 float-right login">Pay Bill</button>
                            <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                            </div>
                            
                    </form>
                </div>
        </section>
        </div>
      <?php require_once('components/footer.php') ?>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require_once 'partial-components/paybills/paybills.php'; ?>
    <?php require_once 'components/user/javascript.php'; ?>
    <script src="middlewares/user/header.js"></script>
    <script src="middlewares/user/paybills.js"></script>
  </body>
</html>
