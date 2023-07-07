<!-- Loan Guarantors -->

<div class="modal fade" id="loanGuarantors" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add Loan Guarantor</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="loanGuarantorForm" action="#" method="post">
            <div class="col-md-6">
            <div class="form-group mb-4">
              <label for="selectMemberTransfer" class="form-label">Select a Member</label>
                <select name="memberid" id="memberid" class="border-1 form-control-md input-text">
                  <option value="" disabled selected hidden>Select or Search Member</option>
                  <option value="manager">Manager</option>
                </select>                
              </div>
            </div>
            <div class="col-md-6">
            <div class="form-group mb-4">
              <label for="selectLoan" class="form-label">Select a Loan to be Guaranteed</label>
              <select name="application" id="application" class="border-1 form-control-md input-text">
                  <option value="" disabled selected hidden>Select Loan to be Guaranteed</option>
                  <option value="manager">Manager</option>
                  <option value="teller">Teller</option>
                </select>                
              </div>
            </div>

            <div class="col-md-6">
            <label for="amount" class="form-label">Amount Applied For</label>
                <div class="form-group mb-4">
                 <input type="text" name="" style="width: 100%;" id="" placeholder="450,000" class="border-1 form-control-md input-text" disabled>
                </div>
            </div> 

            <div class="col-md-6">
            <div class="form-group mb-4">
                <label for="selectGuarantor" class="form-label">Choose Guarantor</label>
              <select name="selectGuarantor" id="" class="border-1 form-control-md input-text">
                  <option value="" disabled selected hidden>Choose Loan Guarantor</option>
                  <option value="manager">Manager</option>
                  <option value="teller">Teller</option>
                </select> 
            </div>
             
            </div>

            <div class="col-md-6">
                <div class="form-group mb-4">
                <label for="howToGuarantee" class="form-label">How to Guarantee Loan (Savings/Collateral)</label>
                <select name="guarantingway" id="guarantingway" class="border-1 form-control-md input-text">
                  <option value="" disabled selected hidden>Savings</option>
                  <option value="">Savings</option>
                  <option value="">Collateral</option>
                </select>
                </div>
            </div> 

            <div class="col-md-6">
            <label for="amount" class="form-label">Amount Guaranteed So Far</label>
                <div class="form-group mb-4">
                 <input type="text" name="amount" style="width: 100%;" id="name" placeholder="600,000" class="border-1 form-control-md input-text" disabled>
                </div>
            </div>

            <div class="col-md-6">
            <label for="amount" class="form-label">Guarantor's Available Balance</label>
                <div class="form-group mb-4">
                 <input type="text" name="amount" style="width: 100%;" id="name" placeholder="2,000,000" class="border-1 form-control-md input-text" disabled>
                </div>
            </div>

            <div class="col-md-6">
            <label for="amount" class="form-label">Amount Guarantor Wishes To Guarantee</label>
                <div class="form-group mb-4">
                 <input type="text" name="amounttoguarant" style="width: 100%;" id="amounttoguarant" placeholder="Enter Amount" class="border-1 form-control-md input-text">
                </div>
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