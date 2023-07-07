<?php
      require 'private/initialize.php';
      $pagename = 'Branches';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Branches (<span id="numofbranches">0</span>) </h2>
                  <a href="#" data-toggle="modal" data-target="#addbranch" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Branch</a>
                </div>
                <div class="card-body mt-3 mb-3">
                    <div class="row branches" id="bra_nch"></div>
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
    <script src="middlewares/branches.js"></script>
    <?php require_once "partial-components/branches/branch.php"; ?>


    </script>
  </body>
</html>
