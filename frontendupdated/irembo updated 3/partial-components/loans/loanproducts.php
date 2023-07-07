<!-- appended styles -->
<link rel="stylesheet" href="./assets/css/styles/dashboard.css">
<link rel="stylesheet" href="./assets/css/styles/popup.css">

<!-- add member -->
<div class="modal fade" id="addloanproduct" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Register Loan Product</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <div class="row">

        </div>
        <form class="container" id="loanproductform" action="#" method="post">
        <fieldset id="biodata">

            <div class="row">

            <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Name of Loan Product</label>
                  <input type="text" name="product_name" id="product_name" placeholder="Enter Name of Loan Product" class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>
              <!-- Select loan type -->
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Type of Loan </label>
                  <select name="loan_type" id="loan_type" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>Select Type of Loan</option>
                    <option value="long term">Long term</option>
                    <option value="short term">Short term</option>
                  </select>
                </div>
              </div>
              <!-- comfirm installment -->
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Equal Installment </label>
                  <select name="install_payment" id="install_payment" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>Select</option>
                    <option value="yes"> Yes</option>
                    <option value="no"> No</option>
                  </select>
                </div>
              </div>

              <!-- Select loan_rate_type -->
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Loan Rate Type </label>
                  <select name="loan_rate_type" id="loan_rate_type" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>Select Loan Rate Type</option>
                    <option value="straight line"> Straight line</option>
                    <option value="reducing balance"> Reducing Balance</option>
                  </select>
                </div>
              </div>

              <!-- interest_rate -->
              <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Interest Rate (p.a) in %</label>
                  <input type="text" name="interest_rate" id="interest_rate" placeholder="Enter interest rate" class="form-control border-0 shadow form-control-md input-text">
                </div>
              </div>


              <!-- loan_processing_fees -->
              <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Loan Processing fees</label>
                  <input type="number" name="loan_processing_fees" id="loan_processing_fees" placeholder="Enter Loan processing fees for this product" class="form-control border-0 shadow form-control-md input-text" required>
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
                      <label for="" class="form-label">Minimum amount that can be applied for</label>
                      <input type="text"  name="minmum_amount" id="minmum_amount" placeholder="Specify minimum amount client can apply for" class="form-control border-0 shadow form-control-md input-text" required>
                    </div>
                  </div>

                  <!-- Address -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Maximum amount that can be applied for</label>
                      <input type="text" name="maxmum_amount" id="maxmum_amount" placeholder="Specify maximum amount client can apply for" class="form-control border-1 form-control-md input-text" required>
                    </div>
                  </div>
                  <!-- Email Address -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Number of guarantors allowed</label>
                      <input type="text" name="number_of_guarantors" id="number_of_guarantors" placeholder="Specify number of guarantors allowed" class="form-control border-0 shadow form-control-md input-text">
                    </div>
                  </div>
                  <!-- Email Address -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Penalties</label>
                      <input type="text" name="penalties" id="penalties" placeholder="Set penalty to be incured on late payments" class="form-control border-0 shadow form-control-md input-text">
                    </div>
                  </div>

                  <div class="col-md-6 form-account-add-single-member" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Can a client guarantee him/herself?</label>
                       <select name="can_client_be_self_guarantor" id="can_client_be_self_guarantor" class="border-1 form-control-md input-text" required>
                         <option disabled selected hidden>Select</option>
                         <option value="yes">Yes</option>
                         <option value="no">No</option>
                       </select>
                     </div>
                   </div>

                  <div class="col-md-6 form-account-add-single-member" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Must have Secutiy?</label>
                       <select name="must_have_security" id="must_have_security" class="border-1 form-control-md input-text" required>
                         <option disabled selected hidden>Select</option>
                         <option value="yes">Yes</option>
                         <option value="no">No</option>
                       </select>
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
                       <label class="form-label">Deduct intrest before disbusment </label>
                       <select name="deduct_installment_beofore_disbursment" id="deduct_installment_beofore_disbursment" class="border-1 form-control-md input-text" required>
                         <option disabled selected hidden>Select</option>
                         <option value="yes">Yes</option>
                         <option value="No">No</option>
                       </select>
                     </div>
                   </div>
                   <!-- Select Marital Status -->
                   <div class="col-md-6 form-account-add-single-member" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Does interest rate change when defaulted?</label>
                       <select name="does_interest_change_defaulted" id="does_interest_change_defaulted" class="border-1 form-control-md input-text" required>
                         <option disabled selected hidden>Specify</option>
                         <option value="yes">Yes</option>
                         <option value="no">No</option>
                       </select>
                     </div>
                   </div>




                   <div class="col-md-6">
                     <div class="form-group mb-4 input-container">
                       <label for="" class="form-label">New Interest Rate p.a in %</label>
                    <input name="new_interest_rate" id="new_interest_rate" placeholder="New Interest Rate p.a in %" rows="4" class="form-control border-1 form-control-md input-text"<input>
                     </div>
                   </div>



              </div>
                <div class="modal-footer">
                  <a class="btn btn-warning mr-auto" id="previous">Previous</a>
                  <button type="submit" class="btn btn-success login">Save</button>
                  <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                </div>
          </fieldset>
    </form>
    </div>
  </div>
</div>



          <script>
            function generatePDF() {
              const {
                jsPDF
              } = window.jspdf;

              var doc = new jsPDF('l', 'pt', [751.89, 795.28]);
              var pdfjs = document.querySelector('#statement-print');
              var randomnumber = Math.floor(Math.random() * 1000000000000);
              doc.html(pdfjs, {
                callback: function(doc) {
                  doc.save('' + randomnumber + '-statement.pdf');
                },
                x: 10,
                y: 10
              });
            }
          </script>
