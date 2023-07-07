<?php
      require 'private/initialize.php';
      $pagename = 'Settings';
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
                    <h2 class="h6 mb-0 text-uppercase text-center">Bussiness Profile </h2>
                  <!-- <a href="#" data-toggle="modal" data-target="#addstaff" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Staff</a> -->
                </div>
                <div class="card-body mt-3 mb-3">
            <div class="row">
                <div class="col-12">
            <div class="tab-content" id="v-pills-tabContent">
          <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">

                        <div class="row">
                          <div class="col-4" id="image-logo">
                          </div>
                            <form class="col-4 mt-3 needs-validation" id="logo-upload" action="#">
                              <div class="row">
                                <label class="col-6 form-group btn btn-outline-dark">
                                  <span class="mdi mdi-file-image"> Select Logo</span> <input type="file" name="logo" id="logo-images" class="form-control" style="display: none;" required>
                                </label>
                                <div class="col-6">
                                  <button type="submit" class="btn btn-dark login">Upload Logo</button>
                                </div>
                                <div class="help-block">Maximum Image size 500KB</div>
                              </div>
                              </form>
                        </div>
                        <div class="row">
                          <div class="col-9">
                          <form class="row mt-5 needs-validation" novalidate id="updatesacco" method="post">
                            <div class="col-md-6 form-account-add-single-member">
                              <div class="form-group mb-4 input-container">
                                <label class="form-label">Bussiness Name</label>
                                <input type="text" name="name" id="name_sacco" placeholder="Enter Bussiness Name *" class="form-control border-0 shadow form-control-md input-text" required>
                                <div class="invalid-feedback">please this field is required</div>
                              </div>
                            </div>
                            <div class="col-md-6 form-account-add-single-member">
                              <div class="form-group mb-4 input-container">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="contact" id="contact_sacco" placeholder="Enter Phone number *" class="form-control border-0 shadow form-control-md input-text" required>
                                <div class="invalid-feedback">please this field is required</div>
                              </div>
                            </div>
                            <div class="col-md-6 form-account-add-single-member">
                              <div class="form-group mb-4 input-container">
                                <label class="form-label">Bussiness Short Name(Acronym)</label>
                                <input type="tel" name="shortname" id="shortname_sacco" placeholder="Enter Bussiness Short Name *" class="form-control border-0 shadow form-control-md input-text" required>
                                <div class="invalid-feedback">please this field is required</div>
                              </div>
                            </div>

                            <div class="col-md-6 form-account-add-single-member">
                              <div class="form-group mb-4 input-container">
                                <label class="form-label">Address/City/District</label>
                                <input type="tel" name="address" id="address_sacco" placeholder="Enter Bussiness Short Name *" class="form-control border-0 shadow form-control-md input-text" required>
                                <div class="invalid-feedback">please this field is required</div>
                              </div>
                            </div>

                            <div class="form-group col-12 text-right">
                              <button type="submit" class="btn btn-primary text-right login">Update</button>
                              <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                            </div>

                          </form>
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
