<!-- appended styles -->
<link rel="stylesheet" href="./assets/css/styles/dashboard.css">
<link rel="stylesheet" href="./assets/css/styles/popup.css">


<!--  Modal for  Groups -->
<div class="modal fade" id="membersGroups" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Register New Group</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="saveGroupsForm" action="#" method="post">
            <div class="col-md-6">
                <label for="groupName" class="form-label">Group Name</label>
                <div class="form-group mb-4">
                 <input type="text" name="groupname" style="width: 100%;" id="groupName" placeholder="Enter Group Names" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
                <label for="groupChair" class="form-label">Group Chairperson</label>
                <div class="form-group mb-4">
                 <input type="text" name="chairperson" style="width: 100%;" id="groupChair" placeholder="Enter Group Chairperson" class="border-1 form-control-md input-text">
                </div>
            </div>
            <div class="col-md-6">
            <label for="contactNumber" class="form-label">Phone Contact</label>
                <div class="form-group mb-4">
                 <input type="text" name="contact" style="width: 100%;" id="contactNumber" placeholder="Phone Contact" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="contactNumber" class="form-label">Email</label>
                <div class="form-group mb-4">
                 <input type="email" name="email" style="width: 100%;" id="email" placeholder="Email" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="contactNumber" class="form-label">Adress</label>
                <div class="form-group mb-4">
                 <input type="text" name="address" style="width: 100%;" id="adress" placeholder="Adress" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
                <label for="registrationDate" class="form-label">Date of Registration</label>
                <div class="form-group mb-4">
                  <input type="date" name="doj" id="registrationDate" style="width: 100%;" placeholder="Date of Registration *" class="border-1 form-control-md input-text">
              </div>
            </div>

            <div class="col-md-6">
            <div class="form-group mb-4">
              <label for="groupStatus" class="form-label">Group Status</label>
              <select name="status" id="groupStatus" class="border-1 form-control-md input-text">
                  <option value="" disabled selected hidden>Select Group Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div class="col-md-6">
            <label for="contactNumber" class="form-label">Identification</label>
                <div class="form-group mb-4">
                 <input type="text" name="identification" style="width: 100%;" id="identification" placeholder="identification" class="border-1 form-control-md input-text">
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
</div>


<!-- edit group modal -->
<div class="modal fade" id="editGroupmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
aria-hidden="true">
<div class="modal-dialog modal-lg" style="width:70%;" role="document">
  <div class="modal-content">
    <div class="modal-header text-center text-white bg-warning">
      <h4 class="modal-title h5 w-100 font-weight-bold">Edit Group</h4>
    </div>
    <div class="modal-body mx-0">
      <div class="alert" role="alert"></div>
      <form class="row" id="updateGroupsForm" action="#" method="post">
          <div class="col-md-6">
              <label for="groupName" class="form-label">Group Name</label>
              <div class="form-group mb-4">
               <input type="text" name="groupname" style="width: 100%;" id="editgroupName" placeholder="Enter Group Names" class="border-1 form-control-md input-text">
               <input type="hidden" name="id" style="width: 100%;" id="editId" placeholder="Enter Group Names" class="border-1 form-control-md input-text">
              </div>
          </div>

          <div class="col-md-6">
              <label for="groupChair" class="form-label">Group Chairperson</label>
              <div class="form-group mb-4">
               <input type="text" name="chairperson" style="width: 100%;" id="editgroupChair" placeholder="Enter Group Chairperson" class="border-1 form-control-md input-text">
              </div>
          </div>
          <div class="col-md-6">
          <label for="contactNumber" class="form-label">Phone Contact</label>
              <div class="form-group mb-4">
               <input type="text" name="contact" style="width: 100%;" id="editcontactNumber" placeholder="Phone Contact" class="border-1 form-control-md input-text">
              </div>
          </div>

          <div class="col-md-6">
          <label for="contactNumber" class="form-label">Email</label>
              <div class="form-group mb-4">
               <input type="email" name="email" style="width: 100%;" id="editemail" placeholder="Email" class="border-1 form-control-md input-text">
              </div>
          </div>

          <div class="col-md-6">
          <label for="contactNumber" class="form-label">Adress</label>
              <div class="form-group mb-4">
               <input type="text" name="address" style="width: 100%;" id="editadress" placeholder="Adress" class="border-1 form-control-md input-text">
              </div>
          </div>

          <div class="col-md-6">
              <label for="registrationDate" class="form-label">Date of Registration</label>
              <div class="form-group mb-4">
                <input type="date" name="doj" id="editregistrationDate" style="width: 100%;" placeholder="Date of Registration *" class="border-1 form-control-md input-text">
            </div>
          </div>

          <div class="col-md-6">
          <div class="form-group mb-4">
            <label for="groupStatus" class="form-label">Group Status</label>
            <select name="status" id="editgroupStatus" class="border-1 form-control-md input-text">
                <option value="" disabled selected hidden>Select Group Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="col-md-6">
          <label for="contactNumber" class="form-label">Identification</label>
              <div class="form-group mb-4">
               <input type="text" name="editidentification" style="width: 100%;" id="editidentification" placeholder="identification" class="border-1 form-control-md input-text">
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
</div>
