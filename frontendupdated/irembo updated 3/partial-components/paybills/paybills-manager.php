<!-- Add new bill -->

<div class="modal fade" id="saccobills_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add New Bill</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="saccoPaybillsForm" action="#" method="post">
            <div class="form-group col-md-6">
                <label id="" class="form-label">Select a Vendor</label>
                <select name="vendor" id="vendor" data-width="100%"  class="border-1 form-control-md input-text">
                <option disabled selected hidden>Select a Vendor</option>
                </select>
            </div> 

            <div class="form-group col-md-6">
                <label id="" class="form-label">Select Expense Account</label>
                <select name="expenseaccount" id="expenseaccount" data-width="100%"  class="border-1 form-control-md input-text">
                <option disabled selected hidden>Select Expense Account</option>
                </select>
            </div>  
            
            <div class="col-md-6">
            <label for="name" class="form-label">Enter Amount</label>
                <div class="form-group mb-4">
                 <input type="number" name="amount" style="width: 100%;" id="amount" placeholder="Enter Amount" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="email" class="form-label">Date of Transaction</label>
                <div class="form-group mb-4">
                 <input type="date" name="transdate" style="width: 100%;" id="transdate" placeholder="Enter Email Address" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="name" class="form-label">Bill Due Date</label>
                <div class="form-group mb-4">
                 <input type="date" name="duedate" style="width: 100%;" id="duedate" placeholder="Enter Bill Due Dat" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="name" class="form-label">Bill Number</label>
                <div class="form-group mb-4">
                 <input type="text" name="billnumber" style="width: 100%;" id="billnumber" placeholder="Bill Number" class="border-1 form-control-md input-text">
                </div>
            </div>

            <div class="col-md-6">
            <label for="name" class="form-label">Comments/Notes</label>
                <div class="form-group mb-4">
                 <textarea type="text" name="notes" style="width: 100%;" id="notes" placeholder="Write Notes" class="border-1 form-control-md input-text" required></textarea>
                </div>
            </div>
              
            
      <div class="col-md-9 modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Save</button>
        <div style="display:none" class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>