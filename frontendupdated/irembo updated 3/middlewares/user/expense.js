$(document).ready(function () {
  $("body").children().first().before($(".modal"));
  $('.dropify').dropify();
  
  async function getIncomeTypes() {
    $.ajax({
        url: base + "setting/inpense/expense",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {
            // console.log(response)
            nums = response.data.rows_returned;
            for (var i = 0; i < nums; i++) {
                var members = '';
                members += '<option value=' + response.data.inpenses[i].id + '>' + response.data.inpenses[i].name + ' </option>';
                $('#type').append(members);
            }
            $('#type').select2({
                theme: 'bootstrap5',
                width: 'resolve',
                dropdownParent: $("#fixedsavingform")
            });
        },
        error: function(xhr){
                if (xhr.status == '401') {
                    getMembersView()
                }
        }
    })
}
getIncomeTypes()

      getTransactions()
  async function getTransactions(){
     $.ajax({
    url: base+"expenses",
    headers: {
        'Authorization': localStorage.token,
        'Content-Type': 'application/json'
 },
    type : "GET",
    success: function(response) {
        var nums = response.data.rows_returned;
        // console.log(response)
        var no = 0
        for (var i = 0; i < nums; i++){
            no++
            var transaction = '';
            transaction += '<tr>';
            transaction += '<td></td>';
            transaction += '<td>' +no+ '</td>';
            transaction += '<td>' +response.data.transactions[i].category+ '</td>';           
            transaction += '<td>' +response.data.transactions[i].title+ '</td>';           
            transaction += '<td>' + numberWithCommas(response.data.transactions[i].amount)+ '</td>';                   
            transaction += '<td>' + response.data.transactions[i].date+ '</td>';                           
            transaction += '</tr>';
            $('#income_user_table').append(transaction);
           }
           $('#dataTables-fixed').DataTable({
            responsive: false,
            processing: true,
            serverSide: false,
            retrieve: true,
            autoWidth: true,
            paging: true,
            dom: 'lBfrtip',
            ordering: true,
            info: true,
            select: true,
            keys: true,
            autoFill: true,
            colReorder: true,
            pageLength: 5,
            buttons: [
            { extend: 'copy', className: 'btn btn-success btn-sm mdi mdi-content-copy', exportOptions: { columns: [1 , ':visible' ]}},
            { extend: 'csv', className: 'btn btn-danger btn-sm mdi mdi-file-excel', exportOptions: { columns: [ 1, ':visible' ]} },
            { extend: 'excel', className: 'btn btn-dark btn-sm mdi mdi-file-excel-box', exportOptions: { columns: [1 , ':visible' ]} },
            { extend: 'pdf', className: 'btn btn-info btn-sm mdi mdi-file-pdf', exportOptions: { columns: [ 1, ':visible' ]} },
            { extend: 'print', className: 'btn btn-warning btn-sm mdi mdi-printer', exportOptions: { columns: [ 1, ':visible' ]}},
            { extend: 'colvis', className: 'btn btn-sm btn-white'}
            ],
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            language: {
              "emptyTable": "There Is No Data",
              "zeroRecords":      "No Data That Matches Your Search Query",
              searchPlaceholder: "Search Data",
              search: "Filter Records"
            },
            columnDefs: [ {
              orderable: false,
              className: 'select-checkbox',
              targets:   0
          } ],
          select: {
              style:    'multi',
              selector: 'td:first-child'

          },
          fixedHeader: {
            header: true,
            footer: true
        }
          });
      },
      error: function(xhr,status,error){
        if (xhr.status == '401'){
          getTransactions()
        }
      }
   })
  }

$('#fixedsavingform').submit(function(event) {
  event.preventDefault();
  if ($('#fixedsavingform').valid()) {
      var fixedsavingform = $(this);
      var form_data = JSON.stringify(fixedsavingform.serializeObject());
      addfixedsavingform()
      async function addfixedsavingform() {
          $.ajax({
              url: base + "expenses",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "POST",
              contentType: 'application/json',
              data: form_data,
              success: function(response) {
                  // console.log(response);
                  $('#previous').click();
                  $("#addfixedsavingsmodal").modal('hide')
                  $('#fixedsavingform').trigger('reset');
                  $("#dataTables-fixed").DataTable().clear().destroy();
                  var icon = 'success'
                  var message = 'Expense success'
                  sweetalert(icon,message)
                  getTransactions()
                  return;
              },
              error: function(xhr, status, error) {
                  if (xhr.status === '401') {
                      authchecker(addSavings);
                  } 
                  var icon = 'warning'
                  var message = xhr.responseJSON.messages
                  sweetalert(icon, message)
              }

          });
          return false;
      }
  }
})

//image upolad starts here

$(document).delegate('.viewTransaction', 'click', function(event) {
  event.preventDefault();
  var id = $(this).attr('id')
  
  getTransactionsSingle()
async function getTransactionsSingle(){
  $.ajax({
      url: base + "deposit/" + id,
      method: "GET",
      dataType: "json",
      headers: {
          'Authorization': localStorage.token,
          'Content-Type': 'application/json'
      },
      success: function(response) {
          // console.log(response)
                  $('#logo-receipt').html('<img src=' +localStorage.logo+ ' height="70px" class="float-right" alt="no logo">')
                  $('#sacconames').html(localStorage.sacconame);
                  $('#tellerresponsible').html(response.data.transaction[0].Teller);
                  $('#accountnumber').html(response.data.transaction[0].account);
                  $('#accountname').html(response.data.transaction[0].firstname + ' ' +response.data.transaction[0].lastname);
                  $('#transactionID').html(response.data.transaction[0].transactionID);
                  $('#timestamp').html(response.data.transaction[0].timestamp);
                  $('#depositamount').html(numberWithCommas(response.data.transaction[0].amount));
                  $('#amountwords').html(toWordsconver(parseInt(response.data.transaction[0].amount))+ ' shillings only');
                  $('#depositedby').html(response.data.transaction[0].deposited);
                  $('#depositnotes').html(response.data.transaction[0].notes);
                  $('#receiptsavingsmodal').modal('show');
                  
      },
      error: function (xhr){
          if (xhr.status == '401') {
              authchecker(getTransactionsSingle)
          }

      }
  })
}
})
$("#printReceipt").click(function(){
          // console.log('Print');      
          // printwin.document.write(document.getElementById("receipt").innerHTML);
          // var prtContent = document.getElementById("receipt");
          // var WinPrint = window.open();
          // WinPrint.document.write(prtContent.innerHTML);
          // WinPrint.document.close();
          // WinPrint.focus();
          // WinPrint.print();
          // WinPrint.close();
          function printDiv() { 
              var divContents = document.getElementById("receipt").innerHTML; 
              var a = window.open('', 'PRINT', 'height=500, width=500'); 
              a.document.write('<html><link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">'); 
              a.document.write('<link rel="stylesheet" href="assets/css/style.default.css">'); 
              a.document.write('<body style="background-color: #fff;">'); 
              a.document.write(divContents); 
              a.document.write('</body></html>'); 
              a.document.close(); 
              a.print(); 
              a.focus();
          } 
          printDiv()
})

})