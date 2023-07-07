<?php
require 'private/initialize.php';
$pagename = 'MemberAccounts';
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
                                    <h2 class="h6 mb-0 text-uppercase text-center">ASSIGN GrOUP ACCOUNTS</h2>
                                    <!-- <a href="#" data-toggle="modal" data-target="#addnextofkinmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Add Next Of Kin</a> -->
                                </div>
                                <div class="card-body mt-3 mb-3">
                                  <form action="" id="attachGroups">
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                          <label id="" class="form-label">Select SACCO Group</label>
                                                          <select name="group" id="group"  class="w-100 border-1 form-control-md input-text" required>
                                                          <option disabled selected hidden>Search SACCO Account</option>
                                                          </select>
                                                        </div>

                                                        <div class="form-group col-md-6">
                                                          <label for="group_name" class="form-label form-label">Date of Opening</label>
                                                            <input type="date" class="w-100 border-1 form-control-md input-text" id="dateofreg" placeholder="Group's Name" required>
                                                        </div>


                                                    </div>

                                                    <div class="row">

                                                          <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">

                                                            <label for="group_name" class="form-label  col-form-label">Group's Name</label>
                                                            <div class="form-group mb-2 input-container">
                                                              <input type="text" readonly class="w-100 border-1 form-control-md input-text" id="gName" placeholder="Group's Name">
                                                            </div>

                                                            <label for="acc_number" class="form-label  col-form-label">Account Number</label>
                                                            <div class="form-group mb-2 input-container">
                                                              <input type="text" readonly class="w-100 border-1 form-control-md input-text"  id="aNumber" placeholder="Account Number">
                                                            </div>

                                                            <label for="accountname" class="form-label col-form-label">Sacco Account Name</label>
                                                              <div class="form-group">
                                                            <select id="saccoAcountName" class=" w-100 border-1 form-control-md input-text" multiple name="account" placeholder="Select" data-search="true" data-silent-initial-value-set="true" required>
                                                            </select>
                                                              </div>
                                                          </div>

                                                            <div class="mb-4 col-sm-10">
                                                            <button type="Submit" class="btn btn-primary">Submit</button>
                                                            </div>


                                                    </div>

                                                  </form>
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
    <script src="middlewares/user/groups.js?v=1"></script>

</body>

</html>
