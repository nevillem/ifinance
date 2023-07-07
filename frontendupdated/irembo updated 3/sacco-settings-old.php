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
                    <h2 class="h6 mb-0 text-uppercase text-center">Sacco Settings </h2>
                  <!-- <a href="#" data-toggle="modal" data-target="#addstaff" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Staff</a> -->
                </div>
                <div class="card-body mt-3 mb-3">
            <div class="row">
                <div class="col-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active p-3 h6 text-center" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">General</a>
                    <a class="nav-link p-3 h6 text-center" id="v-pills-accounts-tab" data-toggle="pill" href="#v-pills-accounts" role="tab" aria-controls="v-pills-accounts" aria-selected="false">Accounts</a>
                    <a class="nav-link p-3 h6 text-center" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Shares</a>
                    <a class="nav-link p-3 h6 text-center" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Loans</a>
                    <a class="nav-link p-3 h6 text-center" id="v-pills-capital-tab" data-toggle="pill" href="#v-pills-capital" role="tab" aria-controls="v-pills-capital" aria-selected="false">Capital</a>
                    <a class="nav-link p-3 h6 text-center" id="v-pills-income-tab" data-toggle="pill" href="#v-pills-income" role="tab" aria-controls="v-pills-income" aria-selected="false">Income &amp; Expenses</a>
                    <a class="nav-link p-3 h6 text-center" id="v-pills-sms-tab" data-toggle="pill" href="#v-pills-sms" role="tab" aria-controls="v-pills-sms" aria-selected="false">Communication</a>
                    <a class="nav-link p-3 h6 text-center" id="v-pills-pay-tab" data-toggle="pill" href="#v-pills-pay" role="tab" aria-controls="v-pills-pay" aria-selected="false">Payment Methods</a>
                </div>
                </div>
                <div class="col-9">
            <div class="tab-content" id="v-pills-tabContent">
          <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                      
                        <div class="row">
                          <div class="col-4" id="image-logo">
                          </div>
                            <form class="col-4 mt-3 needs-validation" id="logo-upload" action="#">
                              <div class="row">
                                <label class="col-6 form-group btn btn-outline-dark">
                                  <span class="mdi mdi-file-image"> Select Logo</span> <input type="file" name="logo" id="logo-images" class="form-control" style="display: none;" required>
                                </label>
                                <div class="col-6">
                                  <button type="submit" class="btn btn-dark login">Upload Logo</button>
                                </div>
                                <div class="help-block">Maximum Image size 500KB</div>
                              </div>
                              </form>
                        </div>
                        <form class="row mt-5 needs-validation" novalidate id="updatesacco" method="post">
                        <div class="form-group col-6">
                          <input type="text" class="form-control" name="name" id="name_sacco" aria-describedby="email" required> 
                          <small id="name" class="form-text text-muted">sacco name</small>
                          <div class="invalid-feedback">please this field is required</div>

                        </div>
                        <div class="form-group col-6">
                          <input type="tel" class="form-control" name="contact" id="contact_sacco" aria-describedby="contact"required>
                          <small id="contact" class="form-text text-muted">sacco contact.</small>
                          <div class="invalid-feedback">please this field is required</div>

                        </div>
                        <div class="form-group col-6">
                          <input type="text" class="form-control" name="shortname" id="shortname_sacco" aria-describedby="shortname"required>
                          <small id="shortname" class="form-text text-muted">sacco shortname.</small>
                          <div class="invalid-feedback">please this field is required</div>
                        </div>
                        <div class="form-group col-6">
                          <input type="text" class="form-control" name="address" id="address_sacco" aria-describedby="address"required>
                          <small id="address" class="form-text text-muted">sacco address.</small>
                          <div class="invalid-feedback">please this field is required</div>

                        </div>
                        <div class="form-group col-9 text-center">
                          <button type="submit" class="btn btn-primary text-center login">Update</button>
                          <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                        </div>
                        
                      </form>
                    </div>
                    <div class="tab-pane fade" id="v-pills-accounts" role="tabpanel" aria-labelledby="v-pills-accounts-tab">
                      <ul class="nav nav-pills mb-3 row" id="pills-tab" role="tablist">
                        <li class="nav-item col-4" role="presentation">
                            <a class="nav-link active p-2 h6 text-center" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Account Types</a>
                        </li>
                        <li class="nav-item col-4" role="presentation">
                            <a class="nav-link p-2 h6 text-center" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Sections</a>
                        </li>
                      
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                        <div class="card mt-3" style="width: 100%;">
                            <div class="card-header h6 bg-light text-dark">
                                Accounts Types
                            <a href="#" data-toggle="modal" data-target="#addaccount" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark btn-sm float-right">Add account type</a>
                            </div>
                            <div class="card-body row" id="profile-display">
                                <div class="col-12">
                                  <table class="table table-bordered" id="dataTables-account">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Account Type Name</th>
                                        <th scope="col">Account Withdraw Charge</th>
                                        <th scope="col">Account Miniminal Balance</th>
                                        <th scope="col">Account Description</th>
                                        <th scope="col">Action</th>
                                      </tr>
                                    </thead>
                                    <tbody id="account_table">
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                          <div class="card mt-3" style="width: 100%;">
                            <div class="card-header h6">
                                Accounts Sections
                            </div>
                            <div class="card-body row" id="profile-display">
                                <div class="col-9">
                                  <table class="table table-bordered">
                                      <tr>
                                        <td>Section</td>
                                        <td>Voluntary Savings</td>
                                      </tr>
                                      <tr>
                                        <td>Section</td>
                                        <td>Compulsory Savings</td>
                                      </tr>
                                      <tr>
                                        <td>Section</td>
                                        <td>Fixed Savings</td>
                                      </tr>
                                  </table>
                                </div>
                                <div class="col-3">
                                    <p class="help-block">
                                      Accounts have a general integration of 3 parts of which each can be used differently with same accoount number
                                    </p>
                                </div>
                            </div>
                          </div>
                        </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                      <div class="card mt-3" style="width: 100%;">
                        <div class="card-header h6 bg-light text-dark">
                            Share Types
                        <a href="#" data-toggle="modal" data-target="#addshare" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark btn-sm float-right">Add Share Type</a>
                        </div>
                        <div class="card-body row" id="profile-display">
                            <div class="col-12">
                              <table class="table table-bordered" id="dataTables-share">
                                <thead>
                                  <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Share Type Name</th>
                                    <th scope="col">Share Price</th>
                                    <th scope="col">Maximum Shares</th>
                                    <th scope="col">Action</th>
                                  </tr>
                                </thead>
                                <tbody id="share_table">
                                </tbody>
                              </table>
                            </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                    <div class="card mt-3" style="width: 100%;">
                            <div class="card-header h6 bg-light text-dark">
                                Loan Types
                            <a href="#" data-toggle="modal" data-target="#addloanmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark btn-sm float-right">Add Loan type</a>
                            </div>
                            <div class="card-body row" id="profile-display">
                                <div class="col-12">
                                  <table class="table table-bordered" id="dataTables-loan">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Interest</th>
                                        <th scope="col">Penalty</th>
                                        <th scope="col">Period</th>
                                        <th scope="col">Frequency</th>
                                        <th scope="col">Service Fee</th>
                                        <th scope="col">Notes</th>
                                        <th scope="col">Action</th>
                                      </tr>
                                    </thead>
                                    <tbody id="loan_table">
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                          </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-capital" role="tabpanel" aria-labelledby="v-pills-capital-tab">
                    <div class="card mt-3" style="width: 100%;">
                            <div class="card-header h6 bg-light text-dark">
                                Capital 
                            <a href="#" data-toggle="modal" data-target="#addcapitalmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark btn-sm float-right">Add Capital</a>
                            </div>
                            <div class="card-body row" id="profile-display">
                                <div class="col-12">
                                  <table class="table table-bordered" id="dataTables-capital">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Date</th>
                                      </tr>
                                    </thead>
                                    <tbody id="capital_table">
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                          </div>
                    </div>
                  <div class="tab-pane fade" id="v-pills-income" role="tabpanel" aria-labelledby="v-pills-income-tab">
                  <ul class="nav nav-pills mb-3 row" id="pills-tab" role="tablist">
                        <li class="nav-item col-4" role="presentation">
                            <a class="nav-link active p-2 h6 text-center" id="pills-income-tab" data-toggle="pill" href="#pills-income" role="tab" aria-controls="pills-income" aria-selected="true">Income</a>
                        </li>
                        <li class="nav-item col-4" role="presentation">
                            <a class="nav-link p-2 h6 text-center" id="pills-expense-tab" data-toggle="pill" href="#pills-expense" role="tab" aria-controls="pills-expense" aria-selected="false">Expense</a>
                        </li>
                      
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-income" role="tabpanel" aria-labelledby="pills-income-tab">
                        <div class="card mt-3" style="width: 100%;">
                            <div class="card-header h6 bg-light text-dark">
                                Income Types
                            <a href="#" data-toggle="modal" data-target="#addincomemodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark btn-sm float-right">Add Income type</a>
                            </div>
                            <div class="card-body row">
                                <div class="col-12">
                                  <table class="table table-bordered" id="dataTables-income">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Income Type Name</th>
                                        <th scope="col">Action</th>
                                      </tr>
                                    </thead>
                                    <tbody id="income_table">
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="pills-expense" role="tabpanel" aria-labelledby="pills-expense-tab">
                        <div class="card mt-3" style="width: 100%;">
                            <div class="card-header h6 bg-light text-dark">
                                Expense Types
                            <a href="#" data-toggle="modal" data-target="#addexpensemodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark btn-sm float-right">Add Expense type</a>
                            </div>
                            <div class="card-body row" id="profile-display">
                                <div class="col-12">
                                  <table class="table table-bordered" id="dataTables-expense">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Expense Type Name</th>
                                        <th scope="col">Action</th>
                                      </tr>
                                    </thead>
                                    <tbody id="expense_table">
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                          </div>
                        </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-sms" role="tabpanel" aria-labelledby="v-pills-sms-tab">
                    <div class="card mt-3" style="width: 100%;">
                            <div class="card-header h6 bg-light text-dark">
                                Communication Methods
                            </div>
                            <div class="card-body row" id="">
                                <div class="col-12">
                                  <table class="table table-bordered" id="">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr>
                                        <td>1</td>
                                        <td>SMS</td>
                                      </tr>
                                      <tr>
                                        <td>2</td>
                                        <td>Email</td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                          </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-pay" role="tabpanel" aria-labelledby="v-pills-pay-tab">
                    <div class="card mt-3" style="width: 100%;">
                    <div class="card-header h6 bg-light text-dark">
                              Payment Methods
                            </div>
                            <div class="card-body row" id="">
                                <div class="col-12">
                                  <table class="table table-bordered">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr>
                                        <td>1</td>
                                        <td>USSD</td>
                                      </tr>
                                      <tr>
                                        <td>2</td>
                                        <td>Mobile Money</td>
                                      </tr>
                                      <tr>
                                        <td>3</td>
                                        <td>Cash</td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                          </div>
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
    <?php require_once 'components/javascript.php'; ?>
    <script src="middlewares/header.js"></script>
    <script src="middlewares/settings.js"></script>
    <script>
    </script>
    <?php require_once('partial-components/settings/settings.php'); ?>
  </body>
</html>
