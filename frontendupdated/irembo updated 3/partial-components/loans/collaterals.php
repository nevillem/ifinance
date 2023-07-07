<!-- Modal for  collaterals -->
<div class="modal fade" id="collateralsmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Capture Collateral</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="colateralForm" action="#" method="post">

        <div class="col-md-6">
            <div class="form-group mb-4">
              <label for="groupStatus" class="form-label">Select Member</label>
              <select name="memberid" id="memberid" class="border-1 form-control-md input-text">
                  <option value="" disabled selected hidden>Select or search Member</option>
                  
                </select>                
              </div>
            </div>

            <div class="col-md-6">
                <label for="groupChair" class="form-label">Collateral Name</label>
                <div class="form-group mb-4">
                 <input type="text" name="collateralname" style="width: 100%;" id="collateralname" placeholder="Collateral Name" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="contactNumber" class="form-label">Registration or Serial No</label>
                <div class="form-group mb-4">
                 <input type="text" name="serialnumber" style="width: 100%;" id="serialnumber" placeholder="Registration or Serial No" class="border-1 form-control-md input-text">
                </div>
            </div> 

            <div class="col-md-6">
            <label for="contactNumber" class="form-label">Value of collateral (in cash)</label>
                <div class="form-group mb-4">
                 <input type="text" name="valueprice" style="width: 100%;" id="valueprice" placeholder="Value" class="border-1 form-control-md input-text">
                </div>
            </div> 

            <div class="col-md-6">
            <label for="contactNumber" class="form-label">Additional Description</label>
                <div class="form-group mb-4">
                 <textarea type="text" name="entranotice" style="width: 100%;" id="entranotice"placeholder="Description"  class="border-1 form-control-md input-text">
                </textarea>
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


