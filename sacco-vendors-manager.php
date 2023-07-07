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
                    <h2 class="h6 mb-0 text-uppercase text-center">vendors</h2>
                    <a href="#" data-toggle="modal" data-target="#sacco-vendorsmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Add Vendors</a>
                </div>
                <div class="card-body mt-3 mb-3">
                <table class="table table-striped table-hover table-bordered" id="dataTables-sacco-vendors">
                        <thead>

                        <tr><th class="text-center" colspan="7"></br>Vendor’s List</th></tr>
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Phone Number</th>
                            <th>Email Address</th>
                            <th>Address</th>
                        </tr>
                        </thead>
                        <tbody id="saccovendors">
                        
                        </tbody>
                    </table>

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
    <?php require_once 'partial-components/vendors/vendors-manager.php'; ?>
    <script src="middlewares/user/header.js"></script>
<script src="middlewares/user/vendors-manager.js"></script>
</body>
</html>
