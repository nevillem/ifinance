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
<div class="modal fade" id="addloanmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">New Loan Application</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <form class="container needs-validation" id="addloanform" autocomplete="off" autocapitalize="on" action="#" method="post" novalidate>
        <div class="row">
              <div class="col-md-6">
              <div class="form-group mb-4">
              <select name="account" id="account" onChange="getImages(this.value);"   class="form-control border-0 shadow select2 border-0 form-control-md" data-width="100%" data-live-search="true" style="width: 100%" required>
                <option value="" disabled selected hidden>Choose Account</option>
                </select>     
              </div>
              <div class="form-group mb-4">
              <select name="loantype" id="loantype"  class="form-control border-0 shadow select2 border-0 form-control-md" data-width="100%" data-live-search="true" style="width: 100%" required>
                <option value="" disabled selected hidden>Choose Loan type</option>
                </select>     
              </div>
              <div class="form-group mb-4">
                <input type="date" name="date" id="date_default" class="form-control border-0 shadow form-control-md" required>
                <div class="help-block text-right text-muted text-warning">loan application date</div>
              </div>
              <div class="form-group mb-4">
                <input type="number" name="amount" id="amount" placeholder="Loan Application Amount *" class="form-control border-0 shadow form-control-md" required>
              </div>

              </div>
              <div class="col-md-6 row" id="images-get">
              </div>

              </div>
        <div class="modal-footer">
       <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
       <button id="printReceipt" type="submit" class="btn btn-success login">Submit Application</button>
       <div class="loading p-2 col-xs-1" align-items ="center"><div class="loader"></div></div>
       </div>
        </form>
    </div>
  </div>
</div>
</div>
<div class="modal fade" id="addschedulemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-sm" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Loan Schedule Calculator</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
<div class="modal-body mx-0">
<form class="container needs-validation" id="addloanschedule" autocomplete="off"  action="#" method="post" novalidate>
<div class="form-group mb-4">
              <select name="type" id="loanschedule"  class="form-control border-0 shadow select2 border-0 form-control-md" data-width="100%" data-live-search="true" style="width: 100%" required>
                <option value="" disabled selected hidden>Choose Loan type</option>
                </select>     
              </div>
              <div class="form-group mb-4">
                <input type="date" name="date" id="date_default_schedule" class="form-control border-0 shadow form-control-md" required>
                <div class="help-block text-right text-muted text-warning">start date</div>
              </div>
              <div class="form-group mb-4">
                <input type="number" name="amount" id="amount" placeholder="Loan Application Amount *" class="form-control border-0 shadow form-control-md" required>
              </div>

          <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success login">Calculate</button>
          <div class="loading p-2 col-xs-1" align-items ="center"><div class="loader"></div></div>
          </div>
</form>
</div>
</div>
</div>
</div>
<div class="modal fade" id="editloanappmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-sm" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Edit Loan Application </h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
<div class="modal-body mx-0">
<form class="container needs-validation" id="editloanappform" autocomplete="off"  action="#" method="post" novalidate>
              <div class="form-group mb-4">
                <input type="number" name="amount" id="default_amount" class="form-control border-0 shadow form-control-md"  required>
                <input type="hidden" name="id_update" id="id_update">
              </div>
           
          <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success login">Update</button>
          <div class="loading p-2 col-xs-1" align-items ="center"><div class="loader"></div></div>
          </div>
</form>
</div>
</div>
</div>
</div>
<div class="modal fade" id="loandscheduledisplay" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Loan Schedule</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0 modal-scroll">
      <div class="container-fuild">
    <div class="row">
        <div class="col-12">
            <div class="">
            <style>
.modal-scroll{
  height: 500px;
  overflow-y: auto;
}
#statement-print{
  color: #000;
}
</style>
                <div class=" p-0" id="statement-print">                  
                    <div class="row p-2">
                        <div class="col-md-12">
                            <table class="table tablesorter table-bordered" id="statement-table-sorter">
                                <thead>
                                    <tr class="sorter-header">
                                      <th class="border-0 text-uppercase small font-weight-bold">Period</th>
                                      <th title="sort by date" class="border-0 text-uppercase small font-weight-bold is-date" data-sorter="shortDate" data-date-format="yyyy MM dd">Due Date</th>
                                      <th class="border-0 text-uppercase small font-weight-bold">Interest</th>
                                        <th class="border-0 text-uppercase small font-weight-bold">Principal</th>
                                        <th class="border-0 text-uppercase small font-weight-bold">Balance</th>
                                        <th class="border-0 text-uppercase small font-weight-bold">Total Payment</th>
                                    </tr>
                                </thead>
                                <tbody id="loandschedule_period">
                                                                 
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex flex-row-reverse text-dark p-0">
                  
                        <div class="py-3 px-2 text-right">
                            <div class="mb-2">Total Interest</div>
                            <div class="h6 font-weight-light" id="totalInterest">0</div>
                        </div>

                        <div class="py-3 px-2 text-right">
                            <div class="mb-2">Total Amount Paid</div>
                            <div class="h6 font-weight-light" id="amountpaid">0</div>
                        </div>
                        <div class="py-3 px-2 text-right">
                            <div class="mb-2">Total Principal</div>
                            <div class="h6 font-weight-light" id="principalpaid">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer pl-4 ml-4 col-8">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary login" onClick="generatePDF()">Download</button>
          <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
        </div>
      </div>
    </div>

    </div>
  </div>
</div>