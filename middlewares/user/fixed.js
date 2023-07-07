


$(document).ready(function(){
  $('#mop').on('change', function(event) {
      console.log($(this).val())
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
                              $('#membersid').append(members);
                          }
                          $('#membersid').select2({
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

          // sub member attached accounts
          $("#membersid").on("change", function(){
                var memberid= $(this).val();
                // console.log(membersid);
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
                         $('#member_account').html('<option value="">Select account</option>');
                         for (var i = 0; i < nums; i++) {
                           var members = '';
                           members += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].code + ' | '+response.data.accounts[i].account +'</option>';
                           $('#member_account').append(members);
                         }
                       }
                        $('#member-account').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                            dropdownParent: $("#fixedsavingform")
                        });
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                getAccountsView()
                            }
                            else if (xhr.status == '404') {
                              $('#member_account').html('<option value="">No accounts found</option>');

                       }
                    }
                })
            }
            getAccountsView()
           }else{
             $('#member_account').html('<option value="">Select member first</option>');
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
            getBankAccounts();
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


          $('#fixedsavingform').submit(function(event) {
                    event.preventDefault();
                    if ($('#fixedsavingform').valid()) {
                      fixedsavingsform=$(this);


                    var formData=JSON.stringify(fixedsavingsform.serializeObject());
                    console.log(formData);
                        //   cancelIdleCallback
                        addfixedSaving();
                        // submit form data to api
                        async function addfixedSaving() {
                            // start ajax loader
                            $.ajax({
                                url: base+"deposit-fixed",
                                headers: {
                                    'Authorization': localStorage.token,
                                    'Content-Type': 'application/json'
                                },
                                type: "POST",
                                contentType: 'application/json',
                                data: formData,
                                success: function(response) {
                                    fixedsavingsform[0].reset();
                                    $("#dataTables-fixed-saving").DataTable().clear().destroy();
                                    get_fixedsavings();
                                    $("#addfixedsavingsmodal").modal('hide');
                                    var icon = 'success';
                                    var message = 'Fixed Saving!';
                                    sweetalert(icon, message);
                                    return;
                                },
                                error: function(xhr, status, error) {
                                    if (xhr.status === 401) {
                                        authchecker(addfixedSaving);
                                    } else {
                                        var icon = 'warning';
                                        var message = xhr.responseJSON.messages;
                                        console.log(message);
                                        sweetalert(icon, message);

                                    }
                                }

                            });
                            return false;
                        }
                    }
                });

        // get fixed savings
          get_fixedsavings()
                async function get_fixedsavings() {
                    $.ajax({
                        url: base+"deposit-fixed",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                            var nums = response.data.fixedtransactions.length;
                            $("#numoffixed").html(nums);
                            var no = 0
                            for (var i = 0; i < nums; i++) {
                                no++
                                var fixed_savings = '';
                                fixed_savings += '<tr>';
                                fixed_savings += '<td> </td>';
                                fixed_savings += '<td>' +no+ '</td>';
                                fixed_savings += '<td>' +response.data.fixedtransactions[i].account+ '</td>';
                                fixed_savings += '<td>' +response.data.fixedtransactions[i].firstname+ '</td>';
                                fixed_savings += '<td>' +response.data.fixedtransactions[i].amount+ '</td>';
                                fixed_savings += '<td>' +response.data.fixedtransactions[i].expected+ '</td>';
                                fixed_savings += '<td>' +response.data.fixedtransactions[i].startdate+ '</td>';
                                fixed_savings += '<td>' +response.data.fixedtransactions[i].deposit_method+ '</td>';
                                // fixed_savings += '<td>' +response.data.fixedtransactions[i].timestamp+ '</td>';
                                fixed_savings += '<td>' +response.data.fixedtransactions[i].status+ '</td>';
                                fixed_savings += '</tr>';

                                $("#fixedsavings").append(fixed_savings);
                            }

                            $('#dataTables-fixed-savings').DataTable({
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
                                get_fixedsavings()
                            }
                        }
                    });
                }
                // end get fixed savings

                $(".next").click(function(){
                  var form = $("#fixedsavingform");
                  form.validate({
                    errorElement: 'span',
                    errorClass: 'invalid-feedback',
                    errorPlacement: function (error, element) {
                      if(element.hasClass('select2') && element.next('.select2-container').length) {
                        error.insertAfter(element.next('.select2-container'));
                      }
                      element.closest('.form-group').append(error);
                    },
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
                      next_fs = $('#documentsinfo')
                    }

                    next_fs.show();
                    current_fs.hide()
                    var account = $('#membersid option:selected').text();
                    $('#summary_account').text(account).show();
                    var amount = numberWithCommas($('#amount').val());
                    $('#summary_amount').text(amount).show();
                    var startdate = $('#startdate').val();
                    $('#summary_deposited').text(startdate).show();
                    var enddate = $('#period').val();
                    $('#summary_notes').text(enddate +" months").show();
                    // var percentage = $('#percentage').val();
                    // $('#summary_percentage').text(percentage).show();
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
            });
