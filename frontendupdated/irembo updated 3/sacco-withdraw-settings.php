<?php
      require 'private/initialize.php';
      $pagename = 'Settings';
?>
<?php require 'components/head.php'; ?>
<style>
  .addmore .btn:hover, .addmore .btn:focus{
    color:#343a40 !important;
    outline: none;
    box-shadow: none;
  }
  .form-control-sm {
    height: calc(2.2125rem + 2px);
    padding: 0.45rem 0.8rem;
    font-size: 11px;
    line-height: 1.5;
    border-radius: 0.2rem;
    border: 1px solid ##c7c7c7;
}
select.form-control-sm:not([size]):not([multiple]) {
    height: calc(2rem + 2px) !important;
}
.error{
  border-color: #dc3545 !important;
  color: #dc3545 !important;
}

label[for=amountfrom].error,label[for=amountto].error,label[for=charge].error,label[for=modeofdeduction].error {display: none !important;}

</style>
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
                    <h2 class="h6 mb-0 text-uppercase text-center">funds withdraw settings </h2>
                  <!-- <a href="#" data-toggle="modal" data-target="#addstaff" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Staff</a> -->
                </div>

                <div class="card-body mt-3 mb-3">

                <div class="row mt-5">
                    <form class=" needs-validation" novalidate id="saccowitdrawsettingsform" method="post">
                      <div class="row" id="Directory">
                        <div class="col-md-4 form-account-add-single-member" id="gender-field">
                          <div class="form-group mb-4 input-container">
                            <label class="form-label">Select Account</label>
                            <select name="account" id="saccoaccount" class="border-1 form-control-md input-text title select2" required>
                              <option  disabled selected hidden>select account</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-md-4 form-account-add-single-member">
                          <div class="form-group mb-4 input-container">
                            <label class="form-label">Set Minimum Balance</label>
                            <input type="text" name="minimumbalance" id="minimumbalance" placeholder="Enter Minimum Balance*" class="minimumbalance form-control border-0 shadow form-control-md input-text" required />
                          </div>
                        </div>
                        <!-- <div class="form-account-add-single-member col-md-4">
                          <select name="account" id="saccoaccount" class="border-1 form-control-md input-text account" required>
                            <option disabled selected hidden>Select Account</option>
                        </div>-->
                        <div class="col-md-2 form-account-add-single-member addmore">
                            <!-- <label class="form-label">--</label> -->
                          </br>
                          </br>
                            <button type="button" id="addMore" class="btn border bg-white text-dark btn text-center">Add Row <i class="fa fa-plus"></i></button>
                        </div>
                        <div class="col-md-2 form-account-add-single-member">
                          </br>
                          </br>
                            <button type="submit" class=" btn btn-primary text-center login " id="login">Save Settings</button>
                        </div>
                        <div class="  col-md-8">
                          <div class="row-container d-flex align-items-center justify-content-between bio-info">
                              <div class="bio-heading text-let ml-4">From(Amount)</div>
                              <div class="bio-heading">To(Amount)</div>
                              <div class="bio-heading">Charges</div>
                              <div class="bio-heading mr-3">Mode of payment</div>
                          </div>
                        </div>
                <div class="person col-md-12 mb-2  form-inline">
                  <input type="text" name="amountfrom" class="amountfrom form-control form-control-sm  mb-2 mr-2" placeholder="amount from" required/>
                  <input type="text"name="amountto" class="amountto form-control form-control-sm  mb-2  mr-2 " placeholder="amount to"required />
                  <input type="text" name="charge" class="charge form-control form-control-sm mb-2  mr-2 " placeholder="charge" required />
                  <select name="modeofdeduction" class="modeofdeduction border-1 form-control form-control-sm input-text mb-2 mr-2" required>
                    <option value="" disabled selected hidden>select mode of payment</option>
                    <option value="value">Value</option>
                    <option value="percentage">Percentage</option>
                  </select>
                </div>

            </div>
         </form>

                </div>

                <div class="row mt-5">
                  <div class="col-md-12">
                    <table class="table table-striped table-hover table-bordered" id="dataTables-withdrawsetting">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Accounts</th>
                        <th>Minimum Balance</th>
                        <th>Charge</th>
                        <th>Amount From</th>
                        <th>Amount To</th>
                        <th>Mode of Deduction</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="withdrawsetting_table">
                    </tbody>
                  </table>
                  </div>
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
