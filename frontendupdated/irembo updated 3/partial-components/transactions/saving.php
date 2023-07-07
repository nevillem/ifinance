
<!-- appended styles -->
<link rel="stylesheet" href="./assets/css/styles/dashboard.css">
<link rel="stylesheet" href="./assets/css/styles/popup.css">

<div class="modal fade" id="addsavingsmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-dark bg-white">
        <h4 class="modal-title h5 w-100 font-weight-bold">Capture New Savings</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <form class="container" id="addsavingform" autocomplete="off" autocapitalize="on" action="#" method="post">
        <fieldset id="biodata">
            <div class="row">
              <div class="col-md-6 form-account-add-single-member" id="member-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Member Account</label>
                  <select name="account" id="account" class="border-1 form-control-md input-text select2" data-width="100%" data-live-search="true" style="width: 100%" required>
                    <option value="" disabled selected hidden>Choose Account</option>
                  </select>
                  <span id="account-error" class="invalid-feedback" style="display: inline;"></span>
                </div>
              </div>
              <div class="col-md-6 form-account-add-single-member" id="member-accounts-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Account to Credit</label>
                  <select name="member_account" id="member-account" class="border-1 form-control-md input-text" required>
                    <option value="" disabled selected hidden>select member first</option>
                  </select>
                </div>
              </div>
              <!-- Date Of Birth -->
              <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Date of Payment</label>
                  <input type="text" onfocus="(this.type = 'date')" name="dop" id="dop" placeholder="Date of payment*" class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>
              <div class="col-md-6 form-account-add-single-member" id="member-accounts-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Mode of Payment</label>
                  <select name="mop" id="mop" class="modeofpayment border-1 form-control-md input-text" required>
                    <option value="" disabled selected hidden>select Mode of payment</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6 form-account-add-single-member">
              <div class="form-group mb-4 input-container">
                <label for="" class="form-label">Amount</label>
                <input  type="number" name="amount" id="amount" placeholder="Amount *"  class="form-control border-0 shadow form-control-md input-text" required>
              </div>
              <div class="form-group mb-4 input-container">
                <label for="" class="form-label">Deposited By</label>
                <input  type="text" name="deposited" id="deposited" placeholder="deposited by *"  class="form-control border-0 shadow form-control-md input-text" required>
              </div>
              <div class="form-group mb-4 input-container" id="bank-details" style="display:none;">
                <label for="" class="form-label">Bank Account</label>
                <select name="bank" id="bank" class="border-1 form-control-md input-text select2" data-width="100%" data-live-search="true" style="width: 100%">
                  <option value="" disabled selected hidden>Choose Bank account</option>
                </select>
                <!-- <input  type="text" name="bank" id="bank" placeholder="Account number *"  class="form-control border-0 shadow form-control-md input-text" required> -->
              </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group mb-4 input-container">
               <label for="" class="form-label">Comments/Notes</label>
               <textarea name="notes" id="notes" placeholder="Enter Extra Comments/Notes" rows="5" class="form-control border-1 form-control-md input-text"></textarea>
               </div>
                </div>

              </div>
              <div class="modal-footer">
                  <a class="btn btn-success next">Next</a>
                </div>
            </fieldset>

          		<fieldset id="contactinfo">
                <h6 class="w-200 fs-6 text-center"><span class="badge bg-danger">Summary</span></h6>
                <hr>
                 <div class="row">
              <div class="col-md-12">
              <div class="form-group mb-4">
                  <table class="table table-hover table-bordered">
                        <tr>
                            <td colspan="6">Account Details</td>
                            <td colspan="6" id="summary_account"></td>
                        </tr>
                        <tr>
                            <td colspan="6">Amount </td>
                            <td colspan="6" id="summary_amount"></td>
                        </tr>
                        <tr>
                            <td colspan="6">Deposited By</td>
                            <td colspan="6" id="summary_deposited"></td>
                        </tr>
                        <tr>
                            <td colspan="6">Notes</td>
                            <td colspan="6" id="summary_notes"></td>
                        </tr>
                  </table>
              </div>
              </div>

              <div class="col-md-8" style="margin-left: 150px;">
              <div class="form-group mb-4">
                <input type="password" maxlength="4" name="pincode" id="pincode" placeholder="Enter Pin Number *" class="form-control border-1 form-control-md input-text" autocomplete="off" required>
              </div>
              </div>
              </div>
                <div class="modal-footer">
                  <a class="btn btn-warning" id="previous">Previous</a>
                  <button type="submit" class="btn btn-success login">Submit</button>
                  <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                </div>
          </fieldset>
    </form>
    </div>
  </div>
</div>
</div>

<div class="modal fade" id="receiptsavingsmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Receipt</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
<div class="modal-body mx-0">
    <div id="receipt">
        <div class="container">
            <div class="row d-flex justify-content-center" id="logo-receipt">
                <!-- <img src="assets/img/favicon.png"  alt="No Logo" height="70px"> -->
            </div>
            <p class="my-0 mx-0 text-center"> <span id="sacconames"></span>'s Deposit Receipt</p>
            <hr>
            <div class="row">
                <ul class="list-unstyled container-fluid">
                    <li class="text-black">Teller: <span id="tellerresponsible"></span></li>
                    <li class="text-black">Account: <span id="accountnumber"></span></li>
                    <li class="text-black">Account Name: <span id="accountname"></span></li>
                    <li class="text-black mt-1"><span class="text-black">Receipt No:</span> <span id="transactionID"></span></li>
                    <li class="text-black mt-1">Date and Time: <span id="timestamp"></span> </li>
                </ul>
                <hr class="col-xl-11" style="margin-top: -10px;">
          <div class="col-xl-7">
            <p>Amount: </p>
        </div>
        <div class="col-xl-5">
            <p class="float-end" id="depositamount"> </p>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-xl-7">
            <p>Amount In Words</p>  </div>
            <div class="col-xl-5">
                <p class="float-end" id="amountwords"> </p>
            </div>
            <hr>
        </div>
        <div class="row">
            <div class="col-xl-5"><p>Deposited By</p> </div>
            <div class="col-xl-7">
                <p class="float-end" id="depositedby"></p>
            </div>
            <hr>
        </div>

      <div class="row text-black">
        <div class="col-xl-12">
            <p class="float-end fw-bold">Deposit Notes: <span id="depositnotes"></span>
          </p>
        </div>
        <hr>
      </div>
      <div class="row text-black">
          <div class="col-xl-12">
              <p class="float-end fw-bold">Signature: <span class="float-end">     _______________________________________</span>
            </p>
        </div>
    </div>
    <hr>
    <div class="text-center" style="margin-top: -5px;">
        <p>Powered By Irembo Finance. </p>
    </div>

</div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <button id="printReceipt" type="submit" class="btn btn-success login">Print <i class="fa fa-print fa-1.5x" aria-hidden="true"></i> </button>
    <div class="loading p-2 col-xs-1" align-items ="center"><div class="loader"></div></div>
</div>
</div>
</div>
</div>
