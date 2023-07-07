<?php
      require 'private/initialize.php';
      $pagename = 'Inquiry';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Member Account Balance</h2>
                  <!-- <a href="#" data-toggle="modal" data-target="#addwithdrawsmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Withdraw</a> -->
                </div>
                <div class="card-body mt-3 mb-3">
                  <form class="row"method="post" id="checkbalanceform">
                    <div class="form-group col-md-5">
                      <select type="text" class="form-control form-control-lg border-1 w-100 text-input" name="memberzName" id="c_balaccounts">
                      <option disabled selected hidden>Choose Member</option>                     
                     </select>
                    </div>
                    <div class="form-group col-md-5">
                      <select type="text" class="form-control form-control-lg border-1 w-100 text-input" name="memberzAccount" id="mAccounts">
                      <option disabled selected hidden>Select Account</option>
                      </select>
                    </div>
                    <div class="form-group col-md-2">
                    <button type="submit"data-toggle="modal" data-target="#checkbalancemodal" data-backdrop="static" data-keyboard="false" class="btn btn-info float-right">Check Balance</button>
                    </div>
                  </form>

                  <div class="balances__ d-none" id="balance">
                  <table border="0" class="balance_ table table-hover table-bordered">
                        <tr>
                            <td colspan="6"style="width:20%"><b>Account Name</b></td>
                            <td colspan="6"id="account_name"></td>
                        </tr>
                        <tr>
                            <td colspan="6"><b>Account Number</b> </td>
                            <td colspan="6" id="account_number"></td>
                        </tr>
                        <tr>
                            <td colspan="6"><b>Available Balance</b></td>
                            <td colspan="6" id="availablebalance"></td>
                        </tr>
                        <tr>
                            <td colspan="6"><b>Actual Balance</b></td>
                            <td colspan="6" id="actualbalance"></td>
                        </tr>
                  </table>
                  </div>
                  <div>
                  <button type="submit"id="printSlip" class="d-none printBtn btn btn-success login float-right">Print <i class="fa fa-print fa-1.5x"></i></button>
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
    <script src="middlewares/user/withdraw.js"></script>
    <?php require_once('partial-components/transactions/withdraw.php'); ?>

    </script>
  </body>
</html>
