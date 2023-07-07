<!-- add savings -->

<div class="modal fade" id="addfixedsavingsmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">New Income </h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <form class="container" id="fixedsavingform" action="#" method="post">
          		<!-- <h6 class="w-700 fs-2 text-center"><span class="badge bg-danger">Step 1: Details</span></h6> -->
              <hr>
            <div class="row">
              <div class="col-md-6">
              <div class="form-group mb-4">
                <input type="text" name="title" id="title" placeholder="Title *" class="form-control border-0 shadow form-control-md" required>
              </div>
              <div class="form-group mb-4">
              <select name="type" id="type"  class="form-control border-0 shadow select2 border-0 form-control-md" data-width="100%" data-live-search="true" style="width: 100%" required>
                <option value="" disabled selected hidden>Choose Category</option>
                </select>                   
              </div>
              </div>
              <div class="col-md-6">
              <div class="form-group mb-4">
                <input type="date" name="date" id="date" placeholder="Title *" class="form-control border-0 shadow form-control-md" required>
              </div>
              <div class="form-group mb-4">
                <input type="number" name="amount" id="amount" placeholder="Amount *" class="form-control border-0 shadow form-control-md" required>
              </div>
              </div>
              <div class="col-md-12">
              <div class="form-group mb-4">
                <textarea name="description" id="description" class="form-control" placeholder="Description" cols="30" rows="10"></textarea>
              </div>
              </div>

              </div>
              <div class="modal-footer">
                  <button class="btn btn-success next">Submit</button>
                </div>
            
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
        <div class="col-xl-5">
            <p>Amount In Words</p>  </div>
            <div class="col-xl-7">
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