<?php
      require 'private/initialize.php';
      $pagename = 'Reports';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Reports</h2>
                </div>
                <div class="card-body mt-3 mb-3">
                <ul class="nav nav-pills mb-3 row" id="pills-tab" role="tablist">
                        <li class="nav-item col-4" role="presentation">
                          <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Branch Members</a>
                        </li>
                        <li class="nav-item col-4" role="presentation">
                          <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">More Reports</a>
                        </li>

                      </ul>
                <div class="tab-content" id="pills-tabContent">
                  <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                  <div class="card-body mt-3 mb-3">
                <table class="table table-striped table-hover table-bordered" id="dataTables-members">
                    <thead>
                      <tr>
                        <th></th>
                        <th>#</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Account Savings</th>
                        <th>Contact</th>
                        <th>Gender</th>
                        <th>Identification</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody id="member_table">
                    </tbody>
                  </table>
                </div> 
                      
              </div>
                  <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                        <form action="" class="row" method="post">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="">From</label>
                              <input type="date" class="form-control" name="from" id="from" placeholder="From">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="">To</label>
                              <input type="date" class="form-control" name="from" id="from" placeholder="From">
                            </div>
                          </div>
                          <div class="col-md-4">
                          <div class="form-group">
                            <label for="exampleFormControlSelect1">Select Report</label>
                            <select class="form-control-md" id="exampleFormControlSelect1">
                              <option></option>
                              <option>Loans Statement</option>
                              <option>Group Loans Statement</option>
                              <option>Individual Loans Statement</option>
                            </select>
                            </div>
                            <a class="btn btn-secondary" href="Download Not Permitted.pdf" download>Generate</a>
                          </div>
                        </form>
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
    <script src="middlewares/user/reports.js"></script>
    </script>
  </body>
</html>
