<?php
      require 'private/initialize.php';
      $pagename = 'Accounts';
?>
<?php require 'components/head.php'; ?>
<style media="screen">
#myTab .nav-item  a {
color: #343A40;
font-weight:600;
text-decoration: none;
}
/* #myTab .nav-item  a:active {
color: #FFFFFF;
font-weight:600;
text-decoration: none;
} */
.nav-pills .nav-link.active, .nav-pills .show > .nav-link {
    color: #FFFFFF !important;
    background-color: #73b41a;
}
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
                  <h2 class="h6 mb-0 text-uppercase text-center mb-4">Accounts Settings</h2>

              </div>
                <div class="card-body mt-2 mb-4">
            <div class="row">
              <div class="col-10 my-3">
                <ul class="nav  nav-pills  nav-justified" id="myTab"  role="tablist">
                <li class="nav-item">
                  <a class="nav-link  border border-radius-1 rounded mx-2 active" id="accountgroups-tab"
                   data-toggle="tab" href="#accountgroups" role="tab" aria-controls="accountgroups" aria-selected="true">Account Groups</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link  border border-radius-1 rounded mx-2 " id="subaccounts-tab"
                  data-toggle="tab" href="#subaccounts" role="tab" aria-controls="subaccounts" aria-selected="false">
                  Sub Account Groups</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link  border border-radius-1 rounded mx-2" id="accounts-tab"
                  data-toggle="tab" href="#accounts" role="tab" aria-controls="accounts" aria-selected="false">Accounts</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link  border border-radius-1 rounded mx-2"
                   id="chartsofaccount-tab" data-toggle="tab" href="#chartsofaccount" role="tab"
                    aria-controls="contact" aria-selected="false">Charts of Accounts</a>
                </li>
              </ul>
              </div>
                <div class="col-12">
                  <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="accountgroups" role="tabpanel" aria-labelledby="accountgroups-tab">
                      <div class="card" style="box-shadow:none;">
                        <div class="card-body">
                          <h2 class="h6 mb-0 text-uppercase text-center mb-4">Accounts Groups </h2>
                          <div class="row">
                            <div class="col-md-6">
                            <h6 class="mb-0 text-uppercase text-center mb-4">Add Account Group</h6>
                            <form class="needs-validation" novalidate id="saveaccountgroup" method="post">
                                  <div class="form-group">
                                  <lable id="group-name" class="form-label">Account Group Name</lable>
                                  <input type="text" name="name" id="group-name" placeholder="Enter Account Group Name" class="form-control border-0 shadow form-control-md input-text" required>
                                  </div>
                                  <div class="form-group">
                                  <lable id="accountgroup-code" class="form-label">Account Group Code</lable>
                                  <input type="text" name="code" id="accountgroup-code" placeholder="Enter Account Group Code" class="form-control border-0 shadow form-control-md input-text" required>
                                  </div>

                                  <div class="form-group  text-right">
                                    <button type="submit" class="btn btn-primary text-center login">Add Account Group</button>
                                    <div class="loading p-2 col-xs-1" align="right" style="display:none;"><div class="loader"></div></div>
                                  </div>

                            </form>
                          </div>
                          <div class="col-md-6">
                          <h6 align="right">Account Groups</h6>
                          <table id="table_group_accounts" class="table table-borderless table-striped">
                            <thead>
                              <tr>
                              <th>#</th>
                              <th>Account Group</th>
                              <th>Code</th>
                              </tr>
                            </thead>

                            <tbody id="group_accountss">

                            </tbody>
                          </table>
                          </div>
                          </div>

                            </div>
                        </div>
                    </div>
                    <!-- //subaccounts -->
                    <div class="tab-pane fade" id="subaccounts" role="tabpanel" aria-labelledby="subaccounts-tab">
                    <div class="card" style="box-shadow:none;">
                      <div class="card-body">
                        <!-- <div class="col-12"> -->
                          <h6 class="mb-0 text-uppercase text-center mb-4">Add Sub Account Group</h6>
                          <form class="row needs-validation" novalidate id="save_subaccount_group" method="post">
                          <div class="form-group col-md-6">
                          <label id="group-name" class="form-label">Select Sacco Branch</label>
                          <select name="branch" id="sacco-branch" data-live-search="true" data-width="100%" class="border-1 form-control-md input-text" required>
                          <option disabled selected hidden>Select Sacco Branch</option>
                          </select>
                          </div>

                          <div class="form-group col-md-6">
                          <label id="sub-account-name" class="form-label">Enter Sub Account Name</label>
                          <input type="text" name="name" id="sub-account-name" placeholder="Enter Sub Account Name" class="form-control border-0 shadow form-control-md input-text" required>
                          </div>

                          <div class="form-group col-md-6">
                          <label id="accountgroup-code" class="form-label">Select Account Group</label>
                          <select name="accountgroup" id="account-group" data-width="100%"  class="border-1 form-control-md input-text" required>
                          <option disabled selected hidden>Select Account Group</option>
                          </select>
                          </div>
                          <div class="form-group col-md-6">
                          <label id="sub-account-code" class="form-label">Enter Sub Account Code</label>
                          <input type="text" name="code" id="sub-account-code" placeholder="Enter Sub Account Code" class="form-control border-0 shadow form-control-md input-text" required>
                          </div>

                          <div class="form-group col-md-12  text-right">
                          <button type="submit" class="btn btn-primary text-center login">Save</button>
                          <div class="loading p-2 col-xs-1" align="right" style="display:none"><div class="loader"></div></div>
                          </div>
                          </form>
                          <div class="col-12">
                          <!-- <h6>Add Account Group</h6> -->
                          <table class="table table-striped table-hover table-bordered" id="dataTables-sub-accounts">
                          <thead>
                          <tr>
                          <!-- <th></th> -->
                          <th> </th>
                          <th>#</th>
                          <th>Code</th>
                          <th>Sub Account</th>
                          <th>AccountGroup</th>
                          <th>Sacco Branch</th>
                          <th>Action</th>
                          </tr>
                          </thead>
                          <tbody id="sub_accountss">
                          </tbody>
                          </table>
                          </div>
                      </div>
                    </div>
                    </div>
                    <!-- //accounts -->
                    <div class="tab-pane fade" id="accounts" role="tabpanel" aria-labelledby="accounts-tab">
                      <div class="card" style="box-shadow:none;">
                        <div class="card-body">
                          <h6 class="mb-0 text-uppercase text-center mb-4">Register New Account</h6>
                  <form class="row needs-validation" novalidate id="save_accounts_form" method="post">
                  <div class="form-group col-md-6">
                  <label id="group-name" class="form-label">Select Account Group</label>
                  <select name="accountgroup" id="accountgroup" data-width="100%" class="border-1 form-control-md input-text" required>
                  <option disabled selected hidden>Select Account Group</option>

                  </select>
                  </div>

                  <div class="form-group col-md-6">
                  <lable id="sub-account-name" class="form-label">Enter Account Name</lable>
                  <input type="text" name="name" id="account-name" placeholder="Enter Account Name" class="form-control border-0 shadow form-control-md input-text" required>

                  </div>
                  <div class="form-group col-md-6">
                  <lable id="subaccount-group" class="form-label">Select sub Account Group</lable>
                  <select name="subaccount" data-width="100%" id="sub-account-group"  class="border-1 form-control-md input-text subaccountgroup" required>
                  <option disabled selected hidden>Select Sub Account Group</option>

                  </select>
                  </div>

                  <div class="form-group col-md-6">
                  <lable id="code" class="form-label">Enter Account Code</lable>
                  <input type="text" name="code" id="account-code" placeholder="Enter Account Code" class="form-control border-0 shadow form-control-md input-text" required>

                  </div>
                  <div class="form-group col-md-6">
                   <lable id="accountgroup-code" class="form-label">Account Type</lable>
                   <select name="account_types" id="account_types" data-width="100%"  class="border-1 form-control-md input-text" required>
                   <option value="" disabled selected hidden>Select</option>
                   <option value="saving">Saving</option>
                   <option value="fixed">Fixed</option>
                   <option value="toto">Toto</option>
                   <option value="current">Current</option>
                   <option value="salary">Salary</option>
                   <option value="bank">Bank</option>
                   <option value="others">Others</option>
                   </select>
                   </div>
                  <div class="form-group col-md-6">
                  <lable id="account-status" class="form-label">Select Account status</lable>
                  <select name="status" id="account-status"  class="border-1 form-control-md input-text" required>
                  <option disabled selected hidden>Select Account-status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  </select>
                  </div>

                  <div class="form-group col-md-6">
                  <lable id="account-opening Balance" class="form-label">Enter Account opening Balance</lable>
                  <input type="text" name="openingbalance" id="account-opening-balance" placeholder="Enter Account opening-balance" class="form-control border-0 shadow form-control-md input-text" required>

                  </div>

                  <div class="form-group col-md-12  text-right">
                  <button type="submit" class="btn btn-primary text-center login">Save</button>
                  <div class="loading p-2 col-xs-1" align="right" style="display:none"><div class="loader"></div></div>
                  </div>

                  </form>


                  <div class="col-12">

                  <!-- <h6>Add Account Group</h6> -->
                  <table class="table table-striped table-hover table-bordered" id="dataTables-accounts">
                  <thead>
                  <tr>
                  <th></th>
                  <th>#</th>
                  <th>Account Name</th>
                  <th>Account Code</th>
                  <th>Sub Account Group</th>
                  <th>Account Group</th>
                  <th>Opening Bal.</th>
                  <th>Status</th>
                  <th>Action</th>
                  </tr>
                  </thead>
                  <tbody id="accountss">
                  </tbody>
                  </table>

                  </div>
                   </div>

                      </div>


                    </div>
                    <div class="tab-pane fade" id="chartsofaccount" role="tabpanel" aria-labelledby="chartsofaccount-tab">
                   <div class="card" style="box-shadow:none;">
                     <div class="card-body">
                       <h2 class="h6 mb-0 text-uppercase text-center mb-4">View Charts of Accounts </h2>
                           <table class="table table-responsive table-bordered display" id="datatable"  style="width:100%">
                         <tr>
                         <td class="col-md-2 text-center"><b>Account Groups</b></td>
                         <td class="col-md-2 text-center"><b>Account Group Code</b></td>
                         <td class="col-md-2 text-center"><b>Sub Accounts</b></td>
                         <td class="col-md-2 text-center"><b>Sub Accounts Code</b></td>
                         <td class="col-md-2 text-center"><b>Accounts</b></td>
                         <td class="col-md-2 text-center"><b>Accounts Code</b></td>
                         </tr>

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
