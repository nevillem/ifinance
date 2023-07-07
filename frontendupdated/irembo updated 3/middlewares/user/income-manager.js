$(document).ready(function(){

    $('#addsaccoincomeform').validate({
      rules: {
        incomeaccount: {
          required: true,
        },
        amount: {
            required: true
          },
          transdate: {
            required: true
          },
        amount: {
            required: true
          },
        mop: {
            required: true
          },
          receivedfrom: {
            required: true
          },
          icat: {
            required: true
          }
      },
      messages: {
        incomeaccount:{
                required: "please select income account!",
              },
        amount: {
            required: "amount field is mandatory!"
        },
        transdate: {
            required: "transacion date missing!"
        },
        amount: {
            required: "please enter amount!"
        },
        mop: {
            required: "mode of payment required please!"
        },
        receivedfrom: {
            required: "please fill this field!"
        },
        icat: {
            required: "choose income field!"
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
    
    
    
    getincaccounts();
    async function getincaccounts() {
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

                  if(response.data.accounts[i].account_group_name=="Income" ||response.data.accounts[i].account_group_name=="income" ) {
                    var accounts = '';
                    accounts += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].account + '-' + response.data.accounts[i].code + '</option>';
                    $('#incomeaccount').append(accounts);
                }
                }
                $('#incomeaccount').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        authchecker(getincaccounts);
                    }
            }
        });
    }
    
    
    
    getinccategory();
    async function getinccategory() {
        $.ajax({
            url: base + "incomecat",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                nums = response.data.categories.length;
                for (var i = 0; i < nums; i++) {
                    var categories = '';
                    categories += '<option value=' + response.data.categories[i].id + '>' + response.data.categories[i].incomecategory + '</option>';
                    $('#icat').append(categories);
                }
                $('#icat').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        authchecker(getinccategory);
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
                        authchecker(getmodeopay_);
                    }
            }
        });
    }
    
    
    
    
              $('#addsaccoincomeform').submit(function(event) {
                    event.preventDefault();
                    if ($('#addsaccoincomeform').valid()) {
                      addsaccoincomeform=$(this);
    
    
                    var formData=JSON.stringify(addsaccoincomeform.serializeObject());
                      //cancelIdleCallback
                      addIncome();
                      // submit form data to api
                        async function addIncome() {
                            // start ajax loader
                            $.ajax({
                                url: base+"managerincomes",
                                headers: {
                                    'Authorization': localStorage.token,
                                    'Content-Type': 'application/json'
                                },
                                type: "POST",
                                contentType: 'application/json',
                                data: formData,
                                success: function(response) {
                                    addsaccoincomeform[0].reset();
                                    var icon = 'success';
                                    var message = 'Sacco Income Added!';
                                    sweetalert(icon, message);
                                    return;
                                },
                                error: function(xhr, status, error) {
                                    if (xhr.status === 401) {
                                        authchecker(addIncome);
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