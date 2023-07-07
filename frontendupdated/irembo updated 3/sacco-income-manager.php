<?php
      require 'private/initialize.php';
      $pagename = 'Income';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">income</h2>
                    
                </div>
                <div class="card-body mt-3 mb-3">
                <form class="row" id="addsaccoincomeform" method="post">
                      <div class="form-group col-md-6">
                      <label id="" class="form-label">Select Income Account</label>
                      <select name="incomeaccount" id="incomeaccount" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                      <option disabled selected hidden>Select Income Account</option>
                      </select> 
                      </div>
                      
                      
                      <div class="form-group col-md-6">
                          <label id="" class="form-label">Enter Amount</label>
                          <input type="number" name="amount" id="amount" placeholder="Enter amount" class="form-control border-0 shadow form-control-md input-text" required>                            
                        </div>

                       

                        <div class="form-group col-md-6">
                        <label id="" class="form-label">Date of Transaction</label>
                        <input type="date" name="transdate" id="transdate" placeholder="Date of Transaction" class="form-control border-0 shadow form-control-md input-text" required>                            
                        </div>

                      <div class="form-group col-md-6">
                      <label id="" class="form-label">Mode of Payment</label>
                      <select name="mop" id="mop" data-width="100%"  class="border-1 form-control-md input-text" required>
                      <option disabled selected hidden>Select Mode of Payment</option>

                      </select>
                      </div>


                      <div class="form-group col-md-6">
                      <label id="" class="form-label">Received From</label>
                      <input type="receivedfrom" name="receivedfrom" id="amount" placeholder="Received from" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                    

                      <div class="form-group col-md-6">
                      <label id="" class="form-label">Notes/Comment</label>
                      <input type="text" name="notes" id="notes" placeholder="Write Notes/Comment" class="form-control border-0 shadow form-control-md input-text" required>                            
                      </div>

                      <div class="form-group col-md-2  text-center">
                        <button type="submit" class="btn btn-primary mt-4 text-center login">Add Income</button>
                        <div style="display: none;" class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                      </div>
                      
                </form>
                </div>
        </section>
        </div>
      <?php require_once('components/footer.php') ?>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require_once 'components/user/javascript.php'; ?>
    <script src="middlewares/user/header.js"></script>

    <script src="middlewares/user/income-manager.js"></script>

    </script>
  </body>
</html>
