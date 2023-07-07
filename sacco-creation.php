<?php
      require 'private/initialize.php';
      $pagename = 'saccos-all';
?>
<?php require 'components/head.php'; ?>
  <body id="content">
    <!-- navbar-->
    <?php require_once('components/admin/header-admin.php'); ?>
    <div class="d-flex align-items-stretch">
      <?php require_once('components/admin/siderbar-admin.php'); ?>
      <div class="page-holder w-100 d-flex flex-wrap">
        <div class="container-fluid px-xl-5">
        <section class="py-5 mt-3">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-5 mb-lg-0">
                <div class="card-header">
                    <h2 class="h6 mb-0 text-uppercase text-center">Saccos (<span id="numofsaccos">0</span>) </h2>
                  <a href="#" data-toggle="modal" data-target="#createsacco_modal" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Register Sacco</a>
                </div>
                <div class="card-body mt-3 mb-3">
                    


                    <table class="table table-striped table-hover table-bordered" id="dataTables-saccos-all">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th>Sacco Name</th>
                            <th>Phone Number</th>
                            <th>Sacco Email</th>
                            <th>Adress</th>    
                            <th>Action</th>    
                        </tr>
                        </thead>
                        
                        <tbody id="saccos_table">
                        
                        </tbody>
                    </table> 


                  <div class="modal fade" id="createsacco_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg" style="width:80%;" role="document">
                      <div class="modal-content">
                        <div class="modal-header text-center text-white bg-warning">
                          <h4 class="modal-title h5 w-100 font-weight-bold">Enroll New Sacco</h4>
                          <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
                        </div>
                        <div class="modal-body mx-0">
                        <form id="createSaccoForm" class="row needs-validation" method="post">
                            <div class="col-6 form-group">
                            <label for="name" class="form-label">Full Name *</label>
                            <input name="name" id="name"class="form-control border-0 shadow form-control-md input-text" placeholder="Sacco Name" type="text">
                        </div>

                        <div class="col-6 form-group">
                            <label for="name" class="form-label">Short Name *</label>
                            <input name="shortname" id="shortname"class="form-control border-0 shadow form-control-md input-text" placeholder="Sacco short name" type="text">
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label" for="name">Phone Number *</label>
                            <input name="contact"id="contact"class="form-control border-0 shadow form-control-md input-text" placeholder="Phone Number" type="tel">
                        </div>

                        <div class="col-6 form-group">
                            <label for="name" class="form-label">Email*</label>
                            <input name="email"id="email"class="form-control border-0 shadow form-control-md input-text" placeholder="Sacco Email" type="email">
                        </div>
                        
                        <div class="col-6 form-group">
                            <label for="name" class="form-label">Adress*</label>
                            <input name="address"id="address"class="form-control border-0 shadow form-control-md input-text" placeholder="Sacco adress/Location" type="text">
                        </div>

                        <div class="col-6 form-group">
                            <label class="form-label" for="name">Password*</label>
                            <input name="password"type="password" id="password" class="form-control border-0 shadow form-control-md input-text" placeholder="Pasword">
                            <!--<i class="bi toggle-password bi-eye" toggle="#password_field" data-skin="light" data-toggle="m-tooltip" data-placement="bottom" title="" data-original-title="Show/Hide password"></i>-->
                        </div>
                        <div class="col-6 form-group">
                            <select name="status" id="status" class="w-100 border-1 form-control-md input-text" placeholder="Pasword" type="text">
                              <option value="active">Active</option>
                            </select>
                        </div>
                       
                    
                      <div class="col-md-12 modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="printReceipt" type="submit" class="btn btn-success login">Create Sacco</button>
                    <div class="loading p-2 col-xs-1" align-items ="center"><div class="loader"></div></div>
                    </div>
                      </form>
                  </div>
                </div>
              </div>
              </div>
              <!-- sacco modal end -->
                        
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
    <script src="middlewares/admin/header-admin.js"></script>
    <script src="middlewares/admin/global-admin.js"></script>
    <script src="middlewares/admin/create-sacco.js"></script>
    <!-- <script src="middlewares/settings.js"></script> -->

    </script>

  </body>
</html>
