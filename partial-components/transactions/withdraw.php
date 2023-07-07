<!-- add savings -->
<script>

      function getImages(val) {
         $('#images-get').html("")
        $.ajax({
        type: "GET",
        url: base+"members/"+val,
        headers: {'Authorization': localStorage.token},
        success: function(response){
          var nums = response.data.members[0].images.length
          //   console.log(response.data.members[0].images[i].imageurl)
          for (var i = 0; i < nums; i++){
            $.get({
                    url: response.data.members[0].images[i].imageurl,
                    xhrFields: {
                    responseType: "blob",
                    },
                        headers: {'Authorization': localStorage.token},
                    success: function(blobData) {
                    const objectURL = URL.createObjectURL(blobData)
                    $('#images-get').append('<img id="image1" src='+objectURL+' class="col-6" height="150px">')
                },
            })
          }
         }
        })
        }

</script>
<div class="modal fade" id="addwithdrawsmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">New Withdraw</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <form class="container" id="addwithdrawform" action="#" method="post">
        <fieldset id="biodata">
          		<!-- <h6 class="w-700 fs-2 h5 text-center"><span class="badge bg-danger">Step 1: Details</span></h6>
              <hr> -->
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
                  <label class="form-label">Account to Withdraw from</label>
                  <select name="member_account" id="member-account" class="border-1 form-control-md input-text" required>
                    <option value="" disabled selected hidden>select member first</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Amount</label>
                  <input  type="number" name="amount" id="amount" placeholder="Amount *"  class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>
              <div class="col-md-6 form-account-add-single-member" id="member-accounts-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Mode of Payment</label>
                  <select name="mop" id="mop" class="border-1 form-control-md input-text" required>
                    <option value="" disabled selected hidden>select Mode of payment</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6 form-account-add-single-member" id="dateofpayment-field">
                <div class="form-group mb-4 input-container">
                  <label for="" class="form-label">Withdrew By</label>
                  <input  type="text" name="withdraw" id="withdraw" placeholder="withdrew by *"  class="form-control border-0 shadow form-control-md input-text" required>
                </div>
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Date of Withdraw</label>
                  <input type="text" onfocus="(this.type = 'date')" name="dow" id="dow" placeholder="Date of payment*" class="form-control border-0 shadow form-control-md input-text" required>
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
            <p class="my-0 mx-0 text-center"> <span id="sacconames"></span>'s Withdraw Receipt</p>
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
        <div class="row">
            <div class="col-xl-5"><p>Withdraw By</p> </div>
            <div class="col-xl-7">
                <p class="float-end" id="withdrawby"></p>
            </div>
            <hr>
        </div>

      <div class="row text-black">
        <div class="col-xl-12">
            <p class="float-end fw-bold">Withdraw Notes: <span id="withdrawnotes"></span>
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
