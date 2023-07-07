<?php
      require 'private/initialize.php';
      $pagename = 'ViewMember';
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
                              <div class="view-member-container">
                                 <a class="member" href="members"><i class="fa-solid fa-arrow-left"></i></a>
                             </div>
                             <h2 class="h6 mb-0 text-uppercase text-center">View member file</h2>
                              <!-- <a href="#" data-toggle="modal" data-target="#addmembermodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Add Account</a> -->
                            </div>
                            <!-- <div class="card-body mt-3 mb-3">
                              <div class="filter-container row-container">
                          <div class="doc-button-container">
                              <div class="">
                                  <button type="button" class="doc-button-left doc-buttons">Copy</button>
                              </div>
                              <div class="">
                                  <button type="button" class="doc-button-red doc-buttons">CSV</button>
                              </div>
                              <div class="">
                                  <button type="button" class="doc-button-dark doc-buttons">Excel</button>
                              </div>
                              <div class="">
                                  <button type="button" class="doc-button-blue doc-buttons">PDF</button>
                              </div>
                              <div class="">
                                  <button type="button" class="doc-button-yellow doc-buttons">Print</button>
                              </div>
                              <div class="">
                                  <button type="button" class="doc-button-right doc-buttons">Column Visibilty</button>
                              </div>
                          </div>
                      </div>
                    </div> -->

                    <!-- Member info -->
                <div class="member-info-container row">
                    <!-- Member Bio Data -->
                    <div class="col-lg-6">
                        <div class="bio-data-container">
                            <div class="bio-data-heading">Member Bio Data</div>
                            <div class="bio-data-info">
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div></div>
                                    <div><img src="assets/img/placeholder.png" alt="" class="bio-image"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Full Names</div>
                                    <div id="full_name"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Gender</div>
                                    <div id="gender"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Date of Birth</div>
                                    <div id="dob"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Phone Number</div>
                                    <div id="member_contact"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Date of Registration</div>
                                    <div id="member_join_date"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Registration Status</div>
                                    <div id="status"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">National Identification</div>
                                    <div id="member_identification"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Address</div>
                                    <div id="member_address"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Employment Status</div>
                                    <div id="member_employment_status"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Gross Income/Salary</div>
                                    <div id="member_gross_income"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Marital Status</div>
                                    <div id="member_marital_status"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Group</div>
                                    <div id="member_group"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">N/A</div>
                                    <div id="attach"></div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Attachments</div>
                                    <a href="" class="text-danger">View Attachments</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accounts Information | Next Of Kin | Loan Guaranteed -->
                    <div class="col-lg-6">
                        <!-- Accounts Information -->
                        <div class="bio-data-container">
                            <div class="bio-data-heading">Accounts Information</div>
                            <div class="bio-data-info " id="accounts_info">
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Account Names</div>
                                    <div>Date of Opening</div>
                                </div>

                            </div>
                        </div>
                        <!-- End of accounts Information -->

                        <!-- Next Of Kin -->
                        <div class="bio-data-container">
                            <div class="bio-data-heading">Next of Kin</div>
                            <div class="bio-data-info" id="nextofkin">
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Name</div>
                                    <div class="bio-heading">Contact</div>
                                </div>
                            </div>
                        </div>
                        <!-- End of next of kin -->

                        <!-- Loan Guaranteed -->
                        <div class="bio-data-container">
                            <div class="bio-data-heading">Loan Guaranteed</div>
                            <div class="bio-data-info">
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Applicant</div>
                                    <div class="bio-heading">Amount Applied</div>
                                    <div class="bio-heading">Amount Offered</div>
                                    <div class="bio-heading">Status</div>
                                </div>
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div>no data to show</div>
                                    <!--<div>Sabiiti Jack</div>-->
                                    <!--<div>UGX. 2,000,000</div>-->
                                    <!--<div>UGX. 1,000,000</div>-->
                                    <!--<div>Active</div>-->
                                </div>
                            </div>
                        </div>
                        <!-- End of loan guaranteed -->
                    </div>
                    <!-- Second col-lg-6 container -->
                </div>
                <!-- End of member info containers  -->

                        </div>
                    </div>
                </section>
            </div>
            <?php require_once('./components/footer.php') ?>
        </div>
    </div>
    <!-- JavaScript files-->
    <?php require_once './components/user/javascript.php'; ?>
    <script src="middlewares/user/header.js"></script>
    <script src="middlewares/user/view-member.js?v=1"></script>
    <!-- <?php require_once('./partial-components/members/members.php'); ?> -->
</body>

</html>

<script>

</script>
