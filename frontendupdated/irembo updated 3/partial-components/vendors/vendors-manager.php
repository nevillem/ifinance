<!-- sacco vendors  -->
<div class="modal fade" id="sacco-vendorsmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add Vendor</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="vendorsForm" action="#" method="post">
            <div class="col-md-6">
            <label for="name" class="form-label">First Name</label>
                <div class="form-group mb-4">
                 <input type="text" name="firstname" style="width: 100%;" id="firstname" placeholder="Enter First Name" class="border-1 form-control-md input-text">
                </div>
            </div> 

            <div class="col-md-6">
            <label for="amount" class="form-label">Last Name</label>
                <div class="form-group mb-4">
                 <input type="text" name="lastname" style="width: 100%;" id="lastname" placeholder="Enter Last Name" class="border-1 form-control-md input-text">
                </div>
            </div>    
            
            <div class="col-md-6">
            <label for="name" class="form-label">Company Name</label>
                <div class="form-group mb-4">
                 <input type="text" name="companyname" style="width: 100%;" id="companyname" placeholder="Enter Company Name" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="email" class="form-label">Email Address</label>
                <div class="form-group mb-4">
                 <input type="email" name="email" style="width: 100%;" id="email" placeholder="Enter Email Address" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="name" class="form-label">Phone Number</label>
                <div class="form-group mb-4">
                 <input type="number" name="contact" style="width: 100%;" id="contact" placeholder="Enter Phone Number" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="name" class="form-label">Address (Town/City/Country)</label>
                <div class="form-group mb-4">
                 <input type="text" name="address" style="width: 100%;" id="address" placeholder="Enter Address" class="border-1 form-control-md input-text">
                </div>
            </div>
              
            
      <div class="col-md-9 modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Save</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>