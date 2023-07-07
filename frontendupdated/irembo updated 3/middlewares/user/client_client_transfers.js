$(document).ready(function(){
            getMembers();

              async function getMembers() {
                  $.ajax({
                      url: base+"members",
                      headers: {
                          'Authorization': localStorage.token,
                          'Content-Type': 'application/json'
                      },
                      type: "GET",
                      success: function(response) {

                          nums = response.data.rows_returned;
                          for (var i = 0; i < nums; i++) {
                              var members = '';
                              members += '<option value=' + response.data.members[i].id + '>' + response.data.members[i].firstname + '  '+response.data.members[i].midlename +'  '+response.data.members[i].lastname +'</option>';
                              $('#account_from').append(members);
                              $('#account').append(members);
                          }
                          $('#account_from').select2({
                                    theme: 'bootstrap5',
                                    width: 'resolve',
                                });
                          $('#account').select2({
                                    theme: 'bootstrap5',
                                    width: 'resolve',
                                });


                      },
                      error: function(xhr){
                              if (xhr.status == '401') {
                                  getMembers();
                              }
                      }
                  });
              }

            //get member accounts from
            $('#account_from').on('change', function(){
            var memberid = $(this).val();
            if(memberid){
            async function get_attachedAccounts() {
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
                         $('#member_account_from').html('<option value="">Select Client sending Account</option>');
                         for (var i = 0; i < nums; i++) {
                           var members = '';
                           members += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].code + ' | '+response.data.accounts[i].account +'</option>';
                           $('#member_account_from').append(members);
                         }
                       }
                        $('#member_account_from').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                            dropdownParent: $("#transferToClientForm")
                        });
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                get_attachedAccounts()
                            }
                            else if (xhr.status == '404') {
                              $('#member_account_from').html('<option value="">No sending accounts found</option>');

                       }
                    }
                })
            }
            get_attachedAccounts()
           }else{
             $('#member-account').html('<option value="">Select client from first</option>');
           }
           });

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
                       $('#member-account').html('<option value="">Select Client Receiving Account</option>');
                       for (var i = 0; i < nums; i++) {
                         var members = '';
                         members += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].code + ' | '+response.data.accounts[i].account +'</option>';
                         $('#member-account').append(members);
                       }
                     }
                      $('#member-account').select2({
                          theme: 'bootstrap5',
                          width: 'resolve',
                          dropdownParent: $("#transferToClientForm")
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
           $('#member-account').html('<option value="">Select Client Receiving first</option>');
         }
         });

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

          $(".next").click(function(){
            var form = $("#transferToClientForm");
            form.validate({
              errorElement: 'span',
              errorClass: 'invalid-feedback',
              highlight: function(element, errorClass, validClass) {
                $(element).closest('.form-group').addClass("has-error");
              },
              errorPlacement: function (error, element) {
                if(element.hasClass('select2') && element.next('.select2-container').length) {
                    error.insertAfter(element.next('.select2-container'));
                }
              element.closest('.form-group').append(error);
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
              var accountfrom = $('#account_from option:selected').text();
              $('#summary_deposited').text(accountfrom).show();
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
              $('#transferToClientForm').submit(function(event) {
                    event.preventDefault();
                    if ($('#transferToClientForm').valid()) {
                      transferToClientForm=$(this);

                    var formData=JSON.stringify(transferToClientForm.serializeObject());
                        //   cancelIdleCallback
                        clientToclient();
                        // submit form data to api
                        async function clientToclient() {
                            // start ajax loader
                            $.ajax({
                                url: base+"withdrawclienttoclient",
                                headers: {
                                    'Authorization': localStorage.token,
                                    'Content-Type': 'application/json'
                                },
                                type: "POST",
                                contentType: 'application/json',
                                data: formData,
                                success: function(response) {
                                    transferToClientForm[0].reset();
                                    $('#logo-receipt').html('<img src=' +localStorage.logo+ ' height="70px" class="float-right" alt="no logo">')
                                    $('#sacconames').html(localStorage.sacconame);
                                    $('#tellerresponsible').html(response.data.transaction.clienttransferfrom[0].teller);
                                    $('#accountnumber').html(response.data.transaction.clienttransferfrom[0].account);
                                    $('#accountname').html(response.data.transaction.clienttransferfrom[0].withdraw);
                                    $('#transactionID').html(response.data.transaction.clienttransferfrom[0].transactionID);
                                    $('#timestamp').html(response.data.transaction.clienttransferfrom[0].timestamp);
                                    $('#withdrawamount').html('UGX '+numberWithCommas(response.data.transaction.clienttransferfrom[0].amount));
                                    $('#amountwords').html(toWordsconver(parseInt(response.data.transaction.clienttransferfrom[0].amount))+ ' shillings only');
                                    // $('#withdrawby').html(response.data.transaction.clienttransferfrom[0].withdraw);
                                    $('#charge_price').html('UGX '+numberWithCommas(response.data.transaction.clienttransferfrom[0].charge));
                                    // $('#withdrawnotes').html(response.data.transaction.clienttransferfrom[0].notes);
                                    $('#accountnumberto').html(response.data.transaction.clienttransferto[0].account);
                                    $('#accountnameto').html(response.data.transaction.clienttransferto[0].firstname +' '+response.data.transaction.clienttransferto[0].lastname);
                                    $('#transactionIDto').html(response.data.transaction.clienttransferto[0].transactionID);
                                    $('#receiptsavingsmodal').modal('show');
                                    $("#dataTables-client_2_clienttransfers").DataTable().clear().destroy();
                                    $("#clientToClient_modal").modal('hide');
                                    get_client2client();
                                    var icon = 'success';
                                    var message = 'Transfer made successfully';
                                    sweetalert(icon, message);
                                    return;
                                },
                                error: function(xhr, status, error) {
                                    if (xhr.status === 401) {
                                        authchecker(clientToclient);
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





                 // get client to client transfers
                 get_client2client();
                async function get_client2client() {
                    $.ajax({
                        url: base+"withdrawclienttoclient",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                            var transfer_from = response.data.transactions.clientstransferfrom.length;
                            var no = 0
                            for (var i = 0; i < transfer_from; i++) {
                                no++
                                // clientstransferto

                                var client2client_transfers = '';
                                client2client_transfers += '<tr>';
                                client2client_transfers += '<td> </td>';
                                client2client_transfers += '<td>' +no+ '</td>';
                                client2client_transfers += '<td>' +response.data.transactions.clientstransferfrom[i].firstname+ ' '+response.data.transactions.clientstransferfrom[i].lastname+ '</td>';
                                client2client_transfers += '<td>' +response.data.transactions.clientstransferfrom[i].accountname+ '</td>';
                                client2client_transfers += '<td>' +response.data.transactions.clientstransferfrom[i].amount+ '</td>';

                                // clientstransferto

                                    client2client_transfers += '<td>' +response.data.transactions.clientstransferto[i].firstname+ ' '+response.data.transactions.clientstransferfrom[i].lastname+ '</td>';
                                    client2client_transfers += '<td>' +response.data.transactions.clientstransferto[i].accountname+ '</td>';
                                    client2client_transfers += '<td>' +response.data.transactions.clientstransferto[i].method+ '</td>';
                                    client2client_transfers += '<td>' +response.data.transactions.clientstransferto[i].timestamp+ '</td>';
                                    client2client_transfers += '<td>' +response.data.transactions.clientstransferto[i].transactionID+ '</td>';
                                    client2client_transfers += '<td>' +response.data.transactions.clientstransferto[i].status+ '</td>';

                                client2client_transfers += '</tr>';
                                $("#client_2_clienttransfers").append(client2client_transfers);
                            }

                            $('#dataTables-client_2_clienttransfers').DataTable({
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
                                get_client2client()
                            }
                        }
                    })
                }
                // end get client to client transfers


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
            });
