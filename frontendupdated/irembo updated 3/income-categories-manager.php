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
                    <h2 class="h6 mb-0 text-uppercase text-center">Income categories</h2>
                  <!-- <a href="#" data-toggle="modal" data-target="#addfixedsavingsmodal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Income</a> -->
                </div>
                <div class="card-body mt-3 mb-3">
                <table class="table table-striped table-hover table-bordered" id="dataTables-incomecats">
                    <thead>
                      <tr>
                        <th></th>
                        <th>#</th>
                        <th>Income Category</th>
                        
                        <!-- <th>Action</th> -->
                      </tr>
                    </thead>
                    <tbody id="income_user_table">
                    </tbody>
                  </table>

                  <p>Add Income category</p>
                  <form class="row" action="" id="incomecategoryform">
                      <div class="form-group col-md-6">
                        <!-- <label for="categories" class="form-label">Add income category</label> -->
                        <input type="text" name="category" class="form-control input-text">
                      </div>
                      <div class="form-group col-md-6 mb-2">
                        <button type="submit" class="btn btn-primary">Save</button>
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

    <script src="middlewares/user/incomecatgory.js"></script>

    </script>
  </body>
</html>
