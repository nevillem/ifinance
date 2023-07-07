<!-- add branch -->
<div class="modal fade" id="addbranch" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:70%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add New Branch</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="branchform" action="#" method="post">
            <div class="col-md-12">
              <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="Branch name *" class="form-control border-0 shadow form-control-md">
              </div>
              
              <div class="form-group mb-4">
                <input type="number" name="code" id="code" placeholder="Branch Code e.g 111*" maxlength="4" class="form-control border-0 shadow form-control-md">
                <input type="hidden" value="active" name="status" id="status">
                </div>
              <div class="form-group mb-4">
                <textarea name="address" id="address" placeholder="Branch address *" class="form-control border-0 shadow form-control-md"></textarea>
              </div>
            </div>
      <div class="ml-5 pl-5 modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">submit</button>
        <div class="loading p-2 col-xs-1" align="center">
                    <div class="loader"></div>
                  </div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- edit model -->
<div class="modal fade" id="editbranchmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:70%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Edit Branch</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="editbranchform" action="#" method="post">
            <div class="col-md-12">
              <div class="form-group mb-4">
                <input type="text" name="name" id="name_update" class="form-control border-0 shadow form-control-md">
                <input type="hidden" name="branch_update_id" id="branch_update_id">
              </div>
              
              <div class="form-group mb-4">
                <input type="number" name="code" id="code_update" class="form-control border-0 shadow form-control-md">
                </div>
              <div class="form-group mb-4">
                <textarea name="address" id="address_update" class="form-control border-0 shadow form-control-md"></textarea>
              </div>
              <div class="form-group mb-4">
                <select name="status" id="status_update" class="form-control border-0 shadow form-control-lg">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
      <div class="ml-5 pl-5 modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning" data-dismiss="modal">Reset</button>
        <button type="submit" class="btn btn-outline-secondary login">Update</button>
        <div class="loading p-2 col-xs-1" text-align="center">
        <div class="loader"></div> </div>
      </div>
    </form>
    </div>
  </div>
</div>