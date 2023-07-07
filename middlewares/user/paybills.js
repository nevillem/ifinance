$(document).ready(function(){
    $('#saccoPaybillsForm').validate({
      rules: {
        vendor: {
          required: true,
        },
        expenseaccount: {
            required: true
          },
          billnumber: {
            required: true
          },
        amount: {
            required: true
          },
        transdate: {
            required: true
          },
        duedate: {
            required: true
          }
      },
      messages: {
        vendor:{
                required: "please select vendor!",
              },
        expenseaccount: {
            required: "please select expense account!"
        },
        billnumber: {
            required: "Bill number missing!"
        },
        amount: {
            required: "please enter amount!"
        },
        transdate: {
            required: "transaction date required please!"
        },
        duedate: {
            required: "due date required please!"
        }
        },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
      },
      highlight: function (element, _errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
    });

    $('#tellerbillpaymentform').validate({
  rules: {
    accounttospendfrom: {
      required: true,
    },
    mop: {
        required: true
      },
      amount: {
        required: true
      },
    transdate: {
        required: true
      },
    billid: {
        required: true
      }
    
  },
  messages: {
    accounttospendfrom:{
            required: "please select account!",
          },
    mop: {
        required: "please choose mode of payment!"
    },
    amount: {
        required: "amount field is mandatory!"
    },
    transdate: {
        required: "transaction date missing!"
    },
    billid: {
        required: "specify bill to clear by cheking the box!"
    }
    },
  errorElement: 'span',
  errorPlacement: function (error, element) {
    error.addClass('invalid-feedback');
    element.closest('.form-group').append(error);
  },
  highlight: function (element, _errorClass, validClass) {
    $(element).addClass('is-invalid');
  },
  unhighlight: function (element, errorClass, validClass) {
    $(element).removeClass('is-invalid');
  }
});

    getbills_();
    async function getbills_() {
        $.ajax({
            url: base + "saccobills",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                nums = response.data.vendors.length;
                for (var i = 0; i < nums; i++) {
                    var vendor = '';
                    vendor += '<option value=' + response.data.vendors[i].id + '>' + response.data.vendors[i].firstname + ' ' + response.data.vendors[i].lastname +' | ' + response.data.vendors[i].companyname +' - ' + response.data.vendors[i].address + '</option>';
                    $('#vendor').append(vendor);
                    $('#vendorbilldata').append(vendor);
                }
                $('#vendor').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    // dropdownParent: $("#saccobills_modal")
                });
                $('#vendorbilldata').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    // dropdownParent: $("#saccobills_modal")
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                      getbills_()
                    }
            }
        });
    }

    getmodeopay_();
    async function getmodeopay_() {
        $.ajax({
            url: base + "getpaymentmethod",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                nums = response.data.paymentmethods.length;
                for (var i = 0; i < nums; i++) {
                    var paymentmethods = '';
                    paymentmethods += '<option value=' + response.data.paymentmethods[i].id + '>' + response.data.paymentmethods[i].paymentmethod + '</option>';
                    $('#mop').append(paymentmethods);
                }
                $('#mop').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getmodeopay_()
                    }
            }
        });
    }


getexpaccounts();
async function getexpaccounts() {
    $.ajax({
        url: base + "getsaccoaccounts",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {
            nums = response.data.accounts.length;
            for (var i = 0; i < nums; i++) {
                if(response.data.accounts[i].account_group_name=="Expenditure" ||response.data.accounts[i].account_group_name=="expenditure" ) {                    
                    var accounts = '';
                    accounts += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].account + '-' + response.data.accounts[i].code + '</option>';
                    $('#expenseaccount').append(accounts);
                    $('#accounttospendfrom').append(accounts);
                }
            }
            $('#expenseaccount').select2({
                theme: 'bootstrap5',
                width: 'resolve',
                // dropdownParent: $("#saccobills_modal")
            });
            $('#accounttospendfrom').select2({
                theme: 'bootstrap5',
                width: 'resolve',
                // dropdownParent: $("#saccobills_modal")
            });
        },
        error: function(xhr){
                if (xhr.status == '401') {
                    authchecker(getexpaccounts);
                }
        }
    });
}



    $("#vendorbilldata").on("change", function(){
            var bill= $(this).val();
            get_billinginfo();
            async function get_billinginfo() {
                if(vendorbilldata){
                    
                    $.ajax({
                        url: base+"saccovendors/"+bill,
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                           var nums = response.data.rows_returned;
                            for (var i = 0; i < nums; i++) {
                            var bills = response.data.vendor[i].bills;
                                for(var b = 0; b < bills.length; b++){
                                
                            var bills_ = '';
                            bills_ += '<tr>';
                            bills_ += '<td>' +bills[b].billnumber+'</td>';                                                     
                            bills_ += '<td>' +bills[b].account+'</td>';                                                     
                            bills_ += '<td>' +bills[b].duedate+'</td>';                                                     
                            bills_ += '<td>' +bills[b].amount+'</td>';                                                     
                            bills_ += '<td>' +bills[b].notes+'</td>';                                                     
                            bills_ += '<td class="fw-60 text-center"><input id="chk" class="single-checkbox" name="checkbox1" type="checkbox"  value="'+bills[b].id+'"></td>';           
                             
                            bills_ += '</tr>';
                            $("#billsData").append(bills_);
                            $("input[type='checkbox']").change(function(){
                            if($(this).is(":checked")){
                                var billid = $(this).val();
                                $('#billid').val(billid);
                            }
                            });

                            }

                            }
                            
                        },
                        error: function(xhr){
                                if (xhr.status == '401') {
                                    authchecker(get_billinginfo);
                                }
                        }
                    })
                }
                else{
                $('#vendorbilldata').html('<option value="">Select Vendor First</option>'); 
                }
            }       
        });




              $('#saccoPaybillsForm').submit(function(event) {
                    event.preventDefault();
                    if ($('#saccoPaybillsForm').valid()) {
                      saccoPaybillsForm=$(this);


                    var formData=JSON.stringify(saccoPaybillsForm.serializeObject());
                      //cancelIdleCallback
                      addBill();
                      // submit form data to api
                        async function addBill() {
                            // start ajax loader
                            $.ajax({
                                url: base+"saccobills",
                                headers: {
                                    'Authorization': localStorage.token,
                                    'Content-Type': 'application/json'
                                },
                                type: "POST",
                                contentType: 'application/json',
                                data: formData,
                                success: function(response) {
                                    saccoPaybillsForm[0].reset();
                                    $("#saccobills_modal").modal('hide');
                                    var icon = 'success';
                                    var message = 'Bill Added!';
                                    sweetalert(icon, message);
                                    return;
                                },
                                error: function(xhr, status, error) {
                                    if (xhr.status === 401) {
                                        authchecker(addBill);
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

                $('#tellerbillpaymentform').submit(function(event) {
                event.preventDefault();
                if ($('#tellerbillpaymentform').valid()) {
                    tellerbillpaymentform=$(this);


                var formData=JSON.stringify(tellerbillpaymentform.serializeObject());
                  //cancelIdleCallback
                  payBill();
                  // submit form data to api
                    async function payBill() {
                        // start ajax loader
                        $.ajax({
                            url: base+"paybill",
                            headers: {
                                'Authorization': localStorage.token,
                                'Content-Type': 'application/json'
                            },
                            type: "POST",
                            contentType: 'application/json',
                            data: formData,
                            success: function(response) {
                                tellerbillpaymentform[0].reset();
                                var icon = 'success';
                                var message = 'Bill Payment Successful!';
                                sweetalert(icon, message);
                                return;
                            },
                            error: function(xhr, status, error) {
                                if (xhr.status === 401) {
                                    authchecker(payBill);
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
                
            });