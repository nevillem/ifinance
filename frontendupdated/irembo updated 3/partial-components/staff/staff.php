<!-- add branch -->
<div class="modal fade" id="addstaff" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add New Staff</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="staffform" action="#" method="post">
            <div class="col-md-6">
              <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="Full name *" class="form-control border-0 shadow form-control-md">
              </div>
              
              <div class="form-group mb-4">
                <input type="text" name="username" id="username" placeholder="Email or Agent Phone number" class="form-control border-0 shadow form-control-md">
                </div>
              <div class="form-group mb-4">
                <select name="status" id="status" class="form-control border-0 shadow form-control-lg">
                  <option value="" disabled selected hidden>Choose Staff Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-4">
                <input type="tel" name="contact" id="contact" placeholder="Staff Contact e.g.0755876951 *" class="form-control border-0 shadow form-control-md">
              </div>
              
              <div class="form-group mb-4">
              <select name="role" id="role" class="form-control border-0 shadow form-control-lg">
                  <option value="" disabled selected hidden>Choose Staff Role</option>
                  <option value="manager">Manager</option>
                  <option value="teller">Teller</option>
                  <option value="loansofficer">LoansOfficer</option>
                  <option value="agent">Agent</option>
                </select>                
              </div>
              <div class="form-group mb-4">
              <select name="branchid" id="branchid" class="form-control border-0 shadow select2 border-0 form-control-md" style="width: 100%">
                <option value="" disabled selected hidden>Choose Branch</option>
                </select>              
              </div>
            </div>
      <div class="col-md-9 modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">submit</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- edit model -->
<div class="modal fade" id="editstaffmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:70%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Edit Staff Member</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="editstaffform" action="#" method="post">
            <div class="col-md-6">
              <div class="form-group mb-4">
                <input type="text" name="name" id="name_update" class="form-control border-0 shadow form-control-md">
                <input type="hidden" id="id_update">
              </div>
              
              <div class="form-group mb-4">
                <input type="text" name="username" id="username_update" disabled class="form-control border-0 shadow form-control-md">
                </div>
              <div class="form-group mb-4">
                <select name="status" id="status_update" class="form-control border-0 shadow form-control-lg">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-4">
                <input type="tel" name="contact" id="contact_update" class="form-control border-0 shadow form-control-md">
              </div>
              
              <div class="form-group mb-4">
              <select name="role" id="role_update" class="form-control border-0 shadow form-control-lg">
                  <option value="" disabled selected hidden>Choose Staff Role</option>
                  <option value="manager">Manager</option>
                  <option value="teller">Teller</option>
                  <option value="loansofficer">LoansOfficer</option>
                  <option value="agent">Agent</option>
                </select>                
              </div>
              <div class="form-group mb-4">
              <input type="text" id="branch_updated" disabled class="form-control border-0 shadow select2 border-0 form-control-md">            
              <input type="hidden" name="branchid" id="branchid_updated" disabled class="form-control border-0 shadow select2 border-0 form-control-md">            
              </div>
            </div>
      <div class="col-md-10 modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Update</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>