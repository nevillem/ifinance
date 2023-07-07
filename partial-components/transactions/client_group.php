<!-- Transfer Client to Client -->
<div class="modal fade" id="clientTogroup_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Transfer - Client to Group</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <form class="container" id="transferToGropForm" action="#" method="post">
        <fieldset id="biodata">
          		<!-- <h6 class="w-700 fs-2 h5 text-center"><span class="badge bg-danger">Step 1: Details</span></h6>
              <hr> -->
            <div class="row">
              <div class="col-md-6">
              <label for="selectMemberTransfer" class="form-label">Member to Transfer From</label>
            <div class="form-group mb-4">
              <select name="account_from" id="account_from" class="w-100 border-1 form-control-md input-text" required>
                  <option value="" disabled selected hidden>Select or Search Member</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
                <label for="selectSource" class="form-label">Funds Source Account</label>
            <div class="form-group mb-4">
              <select name="member_account_from" id="member_account_from" class="w-100 border-1 form-control-md input-text" required>
                  <option value="" disabled selected hidden>Select Source Account</option>

                </select>
              </div>
            </div>

            <div class="col-md-6 form-account-add-single-member" id="amount-field">
              <div class="form-group mb-4 input-container">
                <label class="form-label">Amount</label>
                <input  type="number" name="amount" id="amount" placeholder="Amount *"  class="form-control border-0 shadow form-control-md input-text" required>
              </div>
            </div>

            <div class="form-group col-md-6">
            <label for="selectReceiver" class="form-label">Receiving Group*</label>
            <select name="account" id="account" class="w-100 border-1 form-control-md input-text">
                <option value="" disabled selected hidden>Select or Search Group</option>
            </select>
            </div>

            <div class="form-group col-md-6">
            <label for="receivingAccount" class="form-label">Receiving Group Account*</label>
            <select name="member_account" id="member_account" class="member_account2 w-100 border-1 form-control-md input-text">
                <option value="" disabled selected hidden>Select group first</option>
            </select>
            </div>

              <div class="col-md-6 form-account-add-single-member" id="member-accounts-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Mode of Payment</label>
                  <select name="mop" id="mop" class="border-1 form-control-md input-text" required>
                    <option value="" disabled selected hidden>select Mode of payment</option>
                  </select>
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
                   <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">
                     <div class="form-group mb-4 input-container">
                       <label for="paymentDate" class="form-label">Date of Payment</label>
                       <input type="text" onfocus="(this.type = 'date')" name="dow" id="dow" placeholder="Date of payment*" class="form-control border-0 shadow form-control-md input-text" required>
                     </div>
                   </div>

                   <div class="col-md-6 ">
                     <div class="form-group mb-4 input-container">
                    <label for="" class="form-label">Comments/Notes</label>
                    <input name="notes" id="notes" placeholder="Enter Extra Comments/Notes"  class="form-control border-1 form-control-md input-text"/>
                    </div>
                     </div>

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
                            <td colspan="6">WithdrawBy</td>
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
  <div class="modal-dialog modal-md modal-dialog-scrollabl" style="width:80%;" role="document">
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
            <p class="my-0 mx-0 text-center"> <span id="sacconames"></span>'s Client to Client Receipt</p>
            <hr>
            <div class="row">
              <h2>Client Transfer from</h2>
                <ul class="list-unstyled container-fluid">
                    <li class="text-black">Teller: <span id="tellerresponsible"></span></li>
                    <li class="text-black">Account From: <span id="accountnumber"></span></li>
                    <li class="text-black">Account Name: <span id="accountname"></span></li>
                    <li class="text-black mt-1"><span class="text-black">Trans Code:</span> <span id="transactionID"></span></li>
                    <li class="text-black mt-1">Date: <span id="timestamp"></span> </li>
                </ul>
                <hr class="col-xl-11" style="margin-top: -10px;">
                <h2>Client Transfer to</h2>
                  <ul class="list-unstyled container-fluid">
                      <li class="text-black">Account To: <span id="accountnumberto"></span></li>
                      <li class="text-black">Account Name: <span id="accountnameto"></span></li>
                      <li class="text-black mt-1"><span class="text-black">Trans Code:</span> <span id="transactionIDto"></span></li>
                  </ul>
          <hr class="col-xl-11" style="margin-top: -10px;">
          <div class="col-xl-5">
            <p>Amount: </p>
        </div>
        <div class="col-xl-7">
            <p class="float-end" id="withdrawamount"> </p>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-xl-5">
            <p>Amount In Words</p>  </div>
            <div class="col-xl-7">
                <p class="float-end" id="amountwords"> </p>
            </div>
            <hr>
        </div>
        <div class="row">
            <div class="col-xl-5"><p>Withdraw Charge</p> </div>
            <div class="col-xl-7">
                <p class="float-end" id="charge_price"></p>
            </div>
            <hr>
        </div>
        <!-- <div class="row">
            <div class="col-xl-5"><p>Withdraw By</p> </div>
            <div class="col-xl-7">
                <p class="float-end" id="withdrawby"></p>
            </div>
            <hr>
        </div> -->

      <!-- <div class="row text-black">
        <div class="col-xl-12">
            <p class="float-end fw-bold">Withdraw Notes: <span id="withdrawnotes"></span>
          </p>
        </div>
        <hr>
      </div> -->
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
</div>
