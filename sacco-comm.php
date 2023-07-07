<?php
      require 'private/initialize.php';
      $pagename = 'SMS';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Communications</h2>
                </div>
                <div class="card-body mt-3 mb-3">
                <table class="table table-bordered">
          <thead class="thead-dark">
            <tr>
              <th scope="col">To</th>
              <th scope="col">Communicate</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>members</td>
              <td>
                  <a href="#" data-toggle="modal" data-target="#allmembersmsmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-secondary btn-sm">Create</a>
              </td>
            </tr>
            <tr>
              <td>Staffs</td>
              <td>
              <a href="#" data-toggle="modal" data-target="#allstaffsmsmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-secondary btn-sm">Create</a>
              </td>
            </tr>
            <tr>
              <td>Market Campaign</td>
              <td>
              <a href="#" data-toggle="modal" data-target="#allmarketsmsmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-secondary btn-sm">Create</a>
              </td>
            </tr>                                       
          </tbody>
        </table>
              <p>NB: To send Message to a particular Member in the SACCO/MFI, click on View Member and send SMS.</p>
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
    /* Loading the javascript files. */
    <script src="middlewares/header.js"></script>
    <script src="middlewares/sms.js"></script>
    <?php require_once('partial-components/communication/sms.php'); ?>
  </body>
</html>
