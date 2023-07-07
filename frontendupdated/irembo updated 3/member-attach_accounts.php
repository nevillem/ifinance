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
                                    <h2 class="h6 mb-0 text-uppercase text-center">ASSIGN MEMBER ACCOUNTS</h2>
                                    <!-- <a href="#" data-toggle="modal" data-target="#addnextofkinmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Add Next Of Kin</a> -->
                                </div>
                                <div class="card-body mt-3 mb-3">
                                  <form action="" id="attachAccounts">
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                          <lable id="f-deposit-account" class="form-label">Select SACCO Member</lable>
                                                          <select name="member" id="member"  class="border-5 form-control-md" required>
                                                          <option disabled selected hidden>Search or Select Account</option>
                                                          </select>
                                                        </div>

                                                        <div class="form-group col-md-6">
                                                          <lable id="f-deposit-account" class="form-label">Date of Opening</lable>
                                                              <input type="date" id="dateofreg" class="form-control border-0 shadow form-control-md input-text" required>
                                                          </select>
                                                        </div>
                                                    </div>

                                                    <div class="row">

                                                          <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">

                                                            <label for="" class="form-label">Customer’s Name</label>
                                                            <div class="form-group mb-1 input-container">
                                                              <input  type="text" id="customername"  readonly class="form-control border-0 shadow form-control-md input-text">
                                                            </div>

                                                            <label class="form-label">Account Number</label>
                                                            <div class="form-group mb-2 input-container">
                                                              <input type="text" id="accoountnumber" readonly class="form-control border-0 shadow form-control-md input-text">
                                                            </div>

                                                              <label id="f-deposit-account" class="form-label">Select Account Name</label>
                                                              <div class="form-group">
                                                                <select id="multipleSelect"  class=" w-100 border-1 form-control-md input-text" multiple name="native-select" placeholder="Select" data-search="true" data-silent-initial-value-set="true" required>
                                                                  <!-- <option value="">Select</option> -->
                                                                </select>
                                                              </div>
                                                          </div>

                                                          <div class="form-group col-md-2">
                                                            <img id="image" src="https://live.irembofinance.com/assets/img/placeholder.png" alt="avatar" class="img-thumbnail">
                                                            <button type="submit" class="mt-2 btn btn-primary btn-block">Submit</button>
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
    <script src="middlewares/user/members.js?v=1"></script>

</body>

</html>
