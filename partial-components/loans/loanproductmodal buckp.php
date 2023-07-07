<!-- appended styles -->
<link rel="stylesheet" href="./assets/css/styles/dashboard.css">
<link rel="stylesheet" href="./assets/css/styles/popup.css">

<!-- add member -->
<div class="modal fade" id="addloanproduct" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add Loan Product</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <div class="row">

        </div>
        <form class="container" id="addloanproductform" action="#" method="post">
        <fieldset id="biodata">
            <div class="row">

            <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label class="form-label"> Name of Loan Product</label>
                  <input type="text" name="loanproduct" id="loanproduct" placeholder="Enter Firstname *" class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>
              <!-- Select loantype -->
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Type of Loan </label>
                  <select name="loantype" id="loantype" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>Select Loan Type</option>
                    <option value="long term">Long term</option>
                    <option value="short term">Short term</option>
                  </select>
                </div>
              </div>

              <!-- Select instalment -->
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Equal Installment?</label>
                  <select name="loantype" id="gender" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>Choose</option>
                    <option value="yes">Yes</option>
                    <option value="no"> No</option>
                  </select>
                </div>
              </div>
              <!-- Select lon rate type -->
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Loan Rate Type</label>
                  <select name="loantype" id="gender" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>Select Loan Rate Type</option>
                    <option value="straight line">Straight line</option>
                    <option value="reducing balance"> Reducing Balance</option>
                  </select>
                </div>
              </div>
              



              <!-- Interesr rate -->
              <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label for="" class="form-label">Interest Rate in (%)</label>
                  <input type="text" name="contact" id="contact" placeholder="Loan Rate Percentage *" maxlength="10" class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>



              <!-- Processing fees -->
              <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label for="" class="form-label">Loan Processing fees</label>
                  <input type="text" name="contact" id="laonprocessingfees" placeholder="Loan Processing fees *" maxlength="10" class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>

              </div>

              <div class="modal-footer">
                  <a class="btn btn-success next">Next</a>
                </div>
            </fieldset>
            <fieldset id="middledata">
                <div class="row">
                  <!-- Date Of Registration -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Date Of Registration</label>
                      <input type="text" onfocus="(this.type = 'date')" name="doj" id="doj" placeholder="Date of Registration *" class="form-control border-0 shadow form-control-md input-text" required>
                    </div>
                  </div>

                  <!-- Address -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Address</label>
                      <input type="text" name="address" id="address" placeholder="Address *" class="form-control border-1 form-control-md input-text" required>
                    </div>
                  </div>
                  <!-- Email Address -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Email Address</label>
                      <input type="email" name="email" id="email" placeholder="Email Address *" class="form-control border-0 shadow form-control-md input-text">
                    </div>
                  </div>
                  <div class="col-md-6" id="gender-field">
                    <div class="form-group mb-4 input-container">
                      <label class="form-label">Employment Status</label>
                      <select name="employment_status" id="employment_status"  class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>Employment status</option>
                        <option value="employed">Employed</option>
                        <option value="self-employed">Self Employed</option>
                        <option value="unemployed">Unemployed</option>
                      </select>
                    </div>
                  </div>
                  <!-- Identification Number -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label class="form-label">National Identification Number</label>
                      <input type="text" name="identification" id="identification" placeholder="Identification number *" maxlength="14" class="form-control border-0 shadow form-control-md input-text" required>
                    </div>
                  </div>

                  <!-- Gross Income/Salary -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label class="form-label">Gross Income/Salary</label>
                      <input type="text" name="gross_income"  placeholder="Gross Income/Salary" class="form-control border-0 shadow form-control-md input-text">
                    </div>
                  </div>


                  </div>
                  <div class="modal-footer">
                    <a class="btn btn-warning mr-auto" id="backtoBio">Previous</a>
                    <a   class="btn btn-success next" >Next</a>
                  </div>
                </fieldset>

          		<fieldset id="contactinfo">
                <!-- <h6 class="w-200 fs-6 text-center"><span class="badge bg-danger">Contact Info</span></h6>
                <hr> -->
                 <div class="row">
                   <!-- Select Marital Status -->
                   <div class="col-md-6 form-account-add-single-member" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Select Marital Status</label>
                       <select name="marital_status" id="marital_status" class="border-1 form-control-md input-text" required>
                         <option disabled selected hidden>select marital status</option>
                         <option value="single">Single</option>
                         <option value="married">Married</option>
                       </select>
                     </div>
                   </div>

                   <div class="col-md-6">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Group</label>
                       <select name="sacco_group" class="custom-select marital-status" title="SACCO Groups" aria-label="SACCO Groups">
                         <option value="">Select Group</option>
                         <!-- <option value="1">SACCO</option> -->
                         <!-- <option value="2">SACCO2</option> -->
                       </select>
                     </div>
                   </div>


                   <div class="col-md-6">
                     <div class="form-group mb-4 input-container">
                       <label for="" class="form-label">Comments/Notes</label>
                       <textarea name="attach" id="attach" placeholder="Enter Extra Comments/Notes" rows="4" class="form-control border-1 form-control-md input-text"></textarea>
                     </div>
                   </div>

                   <div class="col-md-6 form-account-add-single-member" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Member Registration Status</label>
                       <select name="status"  id="status" class="border-1 form-control-md input-text" required>
                         <!-- <option value="">Select Member Status</option> -->
                         <option value="active">Active</option>
                       </select>
                     </div>
                   </div>

              </div>
                <div class="modal-footer">
                  <a class="btn btn-warning mr-auto" id="previous">Previous</a>
                  <button type="submit" class="btn btn-success login">Continue</button>
                  <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                </div>
          </fieldset>
    </form>
    </div>
  </div>
</div>


