$(document).ready(function(){

    $('#mop').on('change', function(event) {
      if ($(this).val()==="Bank Transfer"){
        $('.bankdiv').removeAttr("style");
      }
      else if ($(this).val()==="Bank Deposit"){
        $('.bankdiv').removeAttr("style");
      }
      else{
        $('.bankdiv').attr("style", "display:none");
      }
    });

  getgroups();
  async function getgroups() {
    $.ajax({
        url: base+"groups",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {
            nums = response.data.rows_returned;
            for (var i = 0; i < nums; i++) {
                var groups = '';
                groups += '<option value=' + response.data.groups[i].id + '>' + response.data.groups[i].account +'-' + response.data.groups[i].groupname + '</option>';
                $('#account').append(groups);
            }

            $('#account').select2({
                      theme: 'bootstrap5',
                      width: 'resolve',
                  });

        },
        error: function(xhr){
                if (xhr.status == '401') {
                    getgroups();
                }
        }
    });
}
  getpaymethod();
  async function getpaymethod() {
    $.ajax({
        url: base+"getpaymentmethod",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {

            nums = response.data.paymentmethods.length;
            for (var i = 0; i < nums; i++) {
                var method = '';
                method += '<option value="' + response.data.paymentmethods[i].paymentmethod + '">' + response.data.paymentmethods[i].paymentmethod + '</option>';
                $('#mop').append(method);
            }

            $('#mop').select2({
                      theme: 'bootstrap5',
                      width: 'resolve',
                  });

        },
        error: function(xhr){
                if (xhr.status == '401') {
                  getpaymethod();
                }
        }
    });
}

// sub account groups for accounts
$("#account").on("change", function(){
                var account= $(this).val();
                if(account){
                async function get_attachedAccounts() {
                        $.ajax({
                            url: base+"getmemberaccounts/"+account,
                            headers: {
                                'Authorization': localStorage.token,
                                'Content-Type': 'application/json'
                            },
                            type: "GET",
                            success: function(response) {
                                nums = response.data.rows_returned;
                                if (nums !=0) {
                                $('#group_account').html('<option value="">Select account</option>');
                                for (var i = 0; i < nums; i++) {
                                    var groupAccounts = '';
                                    groupAccounts += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].account + ' | '+response.data.accounts[i].code +' </option>';
                                    $('#group_account').append(groupAccounts);
                                }
                              }
                                $('#group_account').select2({
                                    theme: 'bootstrap5',
                                    width: 'resolve',
                                });
                            },
                            error: function(xhr){
                                    if (xhr.status == '401') {
                                        get_attachedAccounts()
                                    }
                                    else if (xhr.status == '404') {
                                      $('#group_account').html('<option value="">No accounts found</option>');

                               }
                            }
                        })
                }
                get_attachedAccounts();
              }
              else{
                $('#group_account').html('<option value="">Select Group first</option>');
              }
            });

            //get bankaccount
            async function getBankAccounts() {
                $.ajax({
                    url: base + "getsaccobankaccounts",
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
                            members += '<option value="' + response.data.bankaccounts[i].account + '">' + response.data.bankaccounts[i].account +'</option>';
                            $('#bank').append(members);
                        }
                        $('#bank').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                            dropdownParent: $("#addsavingform")
                        });
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                getBankAccounts()
                            }
                    }
                })
            }
            getBankAccounts()

            $('#groupSavingsform').submit(function(event) {
                    event.preventDefault();
                    if ($('#groupSavingsform').valid()) {
                      groupSavingsform=$(this);
                      console.log("heyeyyyyyyyyy");


                    var formData=JSON.stringify(groupSavingsform.serializeObject());
                        //   cancelIdleCallback
                        addgroupSaving();
                        // submit form data to api
                        async function addgroupSaving() {
                            // start ajax loader
                            $.ajax({
                                url: base+"depositgroup",
                                headers: {
                                    'Authorization': localStorage.token,
                                    'Content-Type': 'application/json'
                                },
                                type: "POST",
                                contentType: 'application/json',
                                data: formData,
                                success: function(response) {
                                    groupSavingsform[0].reset();
                                    $("#dataTables_group_savings").DataTable().clear().destroy();
                                    get_groupsavings();
                                    $("#addgroupsavingsmodal").modal('hide');
                                    var icon = 'success';
                                    var message = 'Group Saving Made!';
                                    sweetalert(icon, message);
                                    return;
                                },
                                error: function(xhr, status, error) {
                                    if (xhr.status === 401) {
                                        authchecker(addgroupSaving);
                                    } else {
                                        var icon = 'warning';
                                        var message = xhr.responseJSON.messages;
                                        sweetalert(icon, message);

                                    }
                                }

                            });
                            return false;
                        }
                    }
                });


                // get group savings
                get_groupsavings()
                async function get_groupsavings() {
                    $.ajax({
                        url: base+"depositgroup",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                            var nums = response.data.transactions.length;
                            $('#numofsavings').html(nums);
                            var no = 0
                            for (var i = 0; i < nums; i++) {
                                no++
                                var group_savings = '';
                                group_savings += '<tr>';
                                group_savings += '<td> </td>';
                                group_savings += '<td>' +no+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].account+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].firstname+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].amount+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].method+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].timestamp+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].status+ '</td>';
                                group_savings += '<td class="fw-60 text-center"><a href="#" data-toggle="modal" class=\'viewTransaction\' data-target=\'#receiptsavingsmodal\' id=' + response.data.transactions[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-danger fa fa-file-pdf fa-1.5x"></i></a></td>';
                                group_savings += '</tr>';

                                $("#groupsavings").append(group_savings);
                            }

                            $('#dataTables_group_savings').DataTable({
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
                                pageLength: 10,
                                buttons: [
                                    { extend: 'copy', className: 'btn btn-success btn-sm mdi mdi-content-copy', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'csv', className: 'btn btn-danger btn-sm mdi mdi-file-excel', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'excel', className: 'btn btn-dark btn-sm mdi mdi-file-excel-box', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'pdf', className: 'btn btn-info btn-sm mdi mdi-file-pdf', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'print', className: 'btn btn-warning btn-sm mdi mdi-printer', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'colvis', className: 'btn btn-sm btn-white' }
                                    // 'pdf',  'excel', 'csv', 'print', 'copy',
                                ],
                                "lengthMenu": [
                                    [5, 10, 25, 50, -1],
                                    [5, 10, 25, 50, "All"]
                                ],
                                language: {
                                    "emptyTable": "There Is No Data",
                                    "zeroRecords": "No Data That Matches Your Search Query",
                                    searchPlaceholder: "Search Data",
                                    search: "Filter Records"
                                },
                                columnDefs: [{
                                    orderable: false,
                                    className: 'select-checkbox',
                                    targets: 0
                                }],
                                select: {
                                    style: 'multi',
                                    selector: 'td:first-child'

                                }
                                ,
                                fixedHeader: {
                                    header: true,
                                    footer: true
                                }
                            });


                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == '401') {
                                get_groupsavings()
                            }
                        }
                    })
                }

                $(".next").click(function(){
                  var form = $("#groupSavingsform");
                  form.validate({
                    errorElement: 'span',
                    errorClass: 'invalid-feedback',
                    errorPlacement: function (error, element) {
                      if(element.hasClass('select2') && element.next('.select2-container').length) {
                          error.insertAfter(element.next('.select2-container'));
                      }
                    element.closest('.form-group').append(error);
                  },
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
                    }

                    next_fs.show();
                    current_fs.hide()
                    var account = $('#account option:selected').text();
                    $('#summary_account').text(account).show();
                    var amount = numberWithCommas($('#amount').val());
                    $('#summary_amount').text(amount).show();
                    var deposit = $('#deposited').val();
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
              });
                // end get group savings

                $(document).delegate('.viewTransaction', 'click', function(event) {
                  event.preventDefault();
                  var id = $(this).attr('id')

                  getTransactionsSingle()
              async function getTransactionsSingle(){
                  $.ajax({
                      url: base + "depositgroup/" + id,
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
                                  $('#depositamount').html('UGX '+numberWithCommas(response.data.transaction[0].amount));
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
              });
            });
