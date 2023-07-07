var minDate, maxDate;

// Custom filtering function which will search data in column four between two values
$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        var min = minDate.val();
        var max = maxDate.val();
        var date = new Date( data[9] );

        if (
            ( min === null && max === null ) ||
            ( min === null && date <= max ) ||
            ( min <= date   && max === null ) ||
            ( min <= date   && date <= max )
        ) {
            return true;
        }
        return false;
    }
);
$(document).ready(function () {
    $("body").children().first().before($(".modal"));
    $('.dropify').dropify();

    async function getMembersView() {
        $.ajax({
            url: base + "members",
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
                    members += '<option value=' + response.data.members[i].id + '>' + response.data.members[i].firstname + ' | '+response.data.members[i].lastname +' | '+response.data.members[i].account+ ' | 0'+response.data.members[i].contact+'</option>';
                    $('#account').append(members);
                }
                $('#account').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#addwithdrawform")
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getMembersView()
                    }
            }
        })
    }
    getMembersView()

    //get payment payment methods
    async function getPaymentMethods() {
        $.ajax({
            url: base + "getpaymentmethod",
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
                    members += '<option value="' + response.data.paymentmethods[i].paymentmethod + '">' + response.data.paymentmethods[i].paymentmethod + '</option>';
                    $('#mop').append(members);
                }
                $('#mop').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#addwithdrawform")
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getPaymentMethods()
                    }
            }
        })
    }
    getPaymentMethods()
    //get member accounts
    $('#account').on('change', function(){
    var memberid = $(this).val();
    if(memberid){
    async function getAccountsView() {
        $.ajax({
            url: base + "getmemberaccounts/"+memberid,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                // console.log(response)
                nums = response.data.rows_returned;
               if (nums !=0) {
                 $('#member-account').html('<option value="">Select account</option>');
                 for (var i = 0; i < nums; i++) {
                   var members = '';
                   members += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].code + ' | '+response.data.accounts[i].account +'</option>';
                   $('#member-account').append(members);
                 }
               }
                $('#member-account').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#addwithdrawform")
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getAccountsView()
                    }
                    else if (xhr.status == '404') {
                      $('#member-account').html('<option value="">No accounts found</option>');

               }
            }
        })
    }
    getAccountsView()
   }else{
     $('#member-account').html('<option value="">Select member first</option>');
   }
   });
        getTransactions()
    async function getTransactions(){
       $.ajax({
      url: base+"withdraw",
      headers: {
          'Authorization': localStorage.token,
          'Content-Type': 'application/json'
   },
      type : "GET",
      success: function(response) {
          var nums = response.data.rows_returned;
          $('#numofwithdraws').html(nums);
          // console.log(response)
          var no = 0
          for (var i = 0; i < nums; i++){
              no++
              let status = response.data.transactions[i].status=='successful'?'<span class="badge badge-success px-2 py-2"> Success </span>':'<span class="badge badge-danger px-2 py-2"> Failed </span>'
              var transaction = '';
              transaction += '<tr>';
              transaction += '<td></td>';
              transaction += '<td>' +no+ '</td>';
              transaction += '<td>' +response.data.transactions[i].firstname+ ' ' +response.data.transactions[i].lastname + '</td>';
              transaction += '<td>' +response.data.transactions[i].account_type+ '</td>';
              transaction += '<td>' + numberWithCommas(response.data.transactions[i].amount)+ '</td>';
              transaction += '<td><span class="badge badge-info py-2 px-2">'+response.data.transactions[i].method+ '</span></td>';
              // transaction += '<td>' +response.data.transactions[i].withdraw+ '</td>';
              transaction += '<td>' +response.data.transactions[i].transactionID+ '</td>';
              transaction += '<td>' +response.data.transactions[i].timestamp+ '</td>';
              transaction += '<td>' +status+'</td>';
              // transaction += '<td>' +response.data.transactions[i].transactionID+ '</td>';
              transaction += '<td class="fw-60 text-center"><a href="#" data-toggle="modal" class=\'viewTransaction\' data-target=\'#receiptsavingsmodal\' id=' + response.data.transactions[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-danger fa fa-file-pdf fa-1.5x"></i></a></td>';
              transaction += '</tr>';
              $('#withdraws_table').append(transaction);
             }
          minDate = new DateTime($('#min'), {
              format: 'YYYY MMMM Do'
          })
          maxDate = new DateTime($('#max'), {
              format: 'YYYY MMMM Do'
          })
          $('#min, #max').on('change', function () {
            $('#dataTables-withdraws').DataTable().draw();
        }),
             $('#dataTables-withdraws').DataTable({
              responsive: false,
              processing: true,
              serverSide: false,
              retrieve: true,
              autoWidth: true,
              paging: true,
              dom: 'PlBfrtip',
              searchPanes: {
                initCollapsed: true,
                count: '{total} found',
                countFiltered: '{shown} / {total}'
            },language: {

            },
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


    $(".next").click(function(){
      var form = $("#addwithdrawform");
      form.validate({
        errorElement: 'span',
        errorClass: 'invalid-feedback',
        highlight: function(element, errorClass, validClass) {
          $(element).closest('.form-group').addClass("has-error");
        },
        unhighlight: function(element, errorClass, validClass) {
          $(element).closest('.form-group').removeClass("has-error");
        },
        rules: {


        },
        messages: {

        }
      })
      if (form.valid() === true){
        if ($('#biodata').is(":visible")){
          current_fs = $('#biodata')
          next_fs = $('#contactinfo')
        }else if($('#contactinfo').is(":visible")){
          current_fs = $('#contactinfo')
          next_fs = $('#documentsinfo')
        }

        next_fs.show();
        current_fs.hide()
        var account = $('#account option:selected').text();
        $('#summary_account').text(account).show();
        var amount = numberWithCommas($('#amount').val());
        $('#summary_amount').text(amount).show();
        var deposit = $('#withdraw').val();
        $('#summary_deposited').text(deposit).show();
        var notes = $('#notes').val();
        $('#summary_notes').text(notes).show();
      }
  })

  $('#previous').click(function(){
    if($('#contactinfo').is(":visible")){
      current_fs = $('#contactinfo');
      next_fs = $('#biodata');
    }
    next_fs.show();
    current_fs.hide();
  })

  $('#addwithdrawform').submit(function(event) {
    event.preventDefault();
    if ($('#addwithdrawform').valid()) {
        var addwithdrawform = $(this);
        var form_data = JSON.stringify(addwithdrawform.serializeObject());
        addWithdraw()
        async function addWithdraw() {
            $.ajax({
                url: base + "withdraw",
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
                    $("#addwithdrawsmodal").modal('hide')
                    $('#addwithdrawform').trigger('reset');
                    $('#logo-receipt').html('<img src=' +localStorage.logo+ ' height="70px" class="float-right" alt="no logo">')
                    $('#sacconames').html(localStorage.sacconame);
                    $('#tellerresponsible').html(response.data.transaction.withdraw[0].teller);
                    $('#accountnumber').html(response.data.transaction.number);
                    $('#accountname').html(response.data.transaction.firstname + ' ' +response.data.transaction.lastname);
                    $('#transactionID').html(response.data.transaction.withdraw[0].transactionID);
                    $('#timestamp').html(response.data.transaction.withdraw[0].timestamp);
                    $('#withdrawamount').html('UGX '+numberWithCommas(response.data.transaction.withdraw[0].amount));
                    $('#amountwords').html(toWordsconver(parseInt(response.data.transaction.withdraw[0].amount))+ ' shillings only');
                    $('#withdrawby').html(response.data.transaction.withdraw[0].withdraw);
                    $('#charge_price').html('UGX '+numberWithCommas(response.data.transaction.withdraw[0].charge));
                    $('#depositnotes').html(response.data.transaction.withdraw[0].notes);
                    $('#receiptsavingsmodal').modal('show');
                    $("#dataTables-withdraws").DataTable().clear().destroy();
                    var icon = 'success'
                    var message = 'please print receipt'
                    sweetalert(icon,message)
                    getTransactions()
                    return;
                },
                error: function(xhr, status, error) {
                    if (xhr.status === '401') {
                        authchecker(addWithdraw);
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
        url: base + "withdraw/" + id,
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
                    $('#withdrawamount').html('UGX '+numberWithCommas(response.data.transaction[0].amount));
                    $('#amountwords').html(toWordsconver(parseInt(response.data.transaction[0].amount))+ ' shillings only');
                    $('#withdrawby').html(response.data.transaction[0].withdraw);
                    $('#charge_price').append('UGX '+numberWithCommas(response.data.transaction[0].charge));
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
