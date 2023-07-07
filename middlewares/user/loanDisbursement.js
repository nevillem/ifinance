$(document).ready(function(){
    // validate individual loan disbursing form
      $('#individualLoanDisburseForm').validate({
          rules: {
            loanappid: {
              required: true,
            },
            members_accountid: {
              required: true,
            },
            amount: {
                required: true
              },
            accountfrom: {
                required: true
              },
            mop: {
                required: true
              },
            memberaccountid: {
                required: true
              },
            datedisbursed: {
                required: true
              },
          },
          messages: {
            loanappid:{
                required: "please select loan!"
              },
            members_accountid:{
                required: "please select member's account!"
              },
              amount: {
                required: "please specify ammount to disburse!",
            },
              accountfrom: {
                required: "please specify account to disburse from!",
            },
              mop: {
                required: "please specify mode of payment!",
            },
              memberaccountid: {
                required: "please specify member's account!",
            },
              datedisbursed: {
                required: "date of disbursing required!",
            },
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


    // validate group loan disbursing form
      $('#groupLoanDisburseForm').validate({
          rules: {
            loanappid: {
              required: true,
            },
            amount: {
                required: true
              },
            accountfrom: {
                required: true
              },
            mop: {
                required: true
              },
            memberaccountid: {
                required: true
              },
            datedisbursed: {
                required: true
              },
          },
          messages: {
            loanappid:{
                required: "please select loan!"
              },
              amount: {
                required: "please specify ammount to disburse!",
            },
              accountfrom: {
                required: "please specify account to disburse from!",
            },
              mop: {
                required: "please specify mode of payment!",
            },
              memberaccountid: {
                required: "please specify group account!",
            },
              datedisbursed: {
                required: "date of disbursing required!",
            },
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
  
  
  // fetch members info
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
                      $('#members_accountid').append(members);
                  }
                  $('#members_accountid').select2({
                      theme: 'bootstrap5',
                      width: 'resolve',
                  });
              },
              error: function(xhr){
                      if (xhr.status == '401') {
                          getMembersView()
                      }
              }
          })
      }
      getMembersView();


  // fetch sacco accounts info
        async function getSccoAccunts() {
          $.ajax({
              url: base + "getsaccoaccounts",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "GET",
              success: function(response) {
                nums = response.data.rows_returned;
                for (var i = 0; i < nums; i++) {
                  var accounts = '';
                  accounts += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].code + ' | '+response.data.accounts[i].account +' | '+response.data.accounts[i].account_group_name+'</option>';
                      $('#accountfrom').append(accounts);
                      $('#groupaccountfrom').append(accounts);
                  }
                  $('#accountfrom').select2({
                      theme: 'bootstrap5',
                      width: 'resolve',
                  });
                  $('#groupaccountfrom').select2({
                      theme: 'bootstrap5',
                      width: 'resolve',
                  });
              },
              error: function(xhr){
                      if (xhr.status == '401') {
                          getSccoAccunts()
                      }
              }
          })
      }
      getSccoAccunts();

  // fetch payment info
        async function getmethods() {
          $.ajax({
              url: base + "getpaymentmethod",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "GET",
              success: function(response) {
                  nums = response.data.rows_returned;
                  for (var i = 0; i < nums; i++) {
                      var mop = '';
                      mop += '<option value=' + response.data.paymentmethods[i].paymentmethod + '>' + response.data.paymentmethods[i].paymentmethod +'</option>';
                      $('#m_mop').append(mop);
                  }
                  $('#m_mop').select2({
                      theme: 'bootstrap5',
                      width: 'resolve',
                  });
              },
              error: function(xhr){
                      if (xhr.status == '401') {
                        getmethods()
                      }
              }
          })
      }
      getmethods();
  
  
      //get member loans
      $("#members_accountid").on("change", function(){
        var memId= $(this).val();
        fetchLoansApplications();
        async function fetchLoansApplications() {
            if(members_accountid){
                
                $.ajax({
                    url: base+"getmemberloans/disbursedloans/"+memId,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "GET",
                    success: function(response) {
                       var nums = response.data.rows_returned;
                       if (nums !=0) {
                       $('#indivi_loanappid').html('<option value="">Choose loan aplication</option>');
                       for (var i = 0; i < nums; i++) {
                        var applications = response.data.member[i].loanapplications;
                        individual_application='';
                        for (a =0; a < applications.length; a++){
                          individual_application += '<option value="' + applications[a].id + '">'+ applications[a].loanproduct +'</option>';
                          
                        }
                        
                        $('#indivi_loanappid').append(individual_application);
                      }
                    }
                    else  {
                      $('#indivi_loanappid').html('<option value="">No loan applications found</option>');
                    }
                        $('#indivi_loanappid').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                        });
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                authchecker(fetchLoansApplications);
                            }
                            
                           
                    }
                })
            }
            else{
            $('#indivi_loanappid').html('<option value="">Select Member First</option>'); 
            }
        }       
    });


      //get member disburse accounts
      $("#members_accountid").on("change", function(){
        var memId= $(this).val();
        fetchmemberAccounts();
        async function fetchmemberAccounts() {
            if(members_accountid){
                
                $.ajax({
                    url: base+"getmemberaccounts/"+memId,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "GET",
                    success: function(response) {
                       var nums = response.data.rows_returned;
                       if (nums !=0) {
                       $('#memberaccountid').html('<option value="">Choose member account</option>');
                       for (var i = 0; i < nums; i++) {
                        var accounts='';
                        accounts += '<option value="' + response.data.accounts[i].id + '">'+  response.data.accounts[i].account +' | '+  response.data.accounts[i].code +'</option>';

                        $('#memberaccountid').append(accounts);
                      }
                    }
                    
                        $('#memberaccountid').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                        });
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                authchecker(fetchmemberAccounts);
                            }
                            else if (xhr.status == '404') {
                                $('#memberaccountid').html('<option value="">No accounts found</option>');
                              }
                    }
                })
            }
            else{
            $('#memberaccountid').html('<option value="">Select Member First</option>'); 
            }
        }       
    });
  
      $('#individualLoanDisburseForm').submit(function(event) {
        event.preventDefault();
        if ($('#individualLoanDisburseForm').valid()) {
            var individualLoanDisburseForm = $(this);
            var form_data = JSON.stringify(individualLoanDisburseForm.serializeObject())
            disburseMemberLoan();
            // submit form data to api
            async function disburseMemberLoan() {
                // start ajax loader
                $.ajax({
                    url: base + "loan-disburse",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                      individualLoanDisburseForm[0].reset();
                        var icon = 'success';
                        var message = 'Loan Dibursed Successfuly!'
                        sweetalert(icon, message)
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(disburseMemberLoan);
                        } 
                        else {
                            var icon = 'warning'
                            var message = xhr.responseJSON.messages
                            sweetalert(icon, message)
                        }
                    }
    
                });
                return false;
            }
        }
    });





  // fetch group info
  async function getGroupsView() {
    $.ajax({
        url: base + "groups",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {
            // console.log(response)
            nums = response.data.rows_returned;
            for (var i = 0; i < nums; i++) {
                var groups = '';
                groups += '<option value=' + response.data.groups[i].id + '>' + response.data.groups[i].account + ' | '+response.data.groups[i].groupname +' | '+response.data.groups[i].chairperson+ ' | 0'+response.data.groups[i].contact+'</option>';
                $('#groupId').append(groups);
            }
            $('#groupId').select2({
                theme: 'bootstrap5',
                width: 'resolve',
            });
        },
        error: function(xhr){
                if (xhr.status == '401') {
                  getGroupsView()
                }
        }
    })
}
getGroupsView();

  // fetch payment info
        async function getPaymodes() {
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
                      var mop = '';
                      mop += '<option value=' + response.data.paymentmethods[i].paymentmethod + '>' + response.data.paymentmethods[i].paymentmethod +'</option>';
                      $('#g_mop').append(mop);
                  }
                  $('#g_mop').select2({
                      theme: 'bootstrap5',
                      width: 'resolve',
                  });
              },
              error: function(xhr){
                      if (xhr.status == '401') {
                        getPaymodes()
                      }
              }
          })
      }
      getPaymodes();
  
  
      //get group loans
      $("#groupId").on("change", function(){
        var gId= $(this).val();
        
        fetchGroupApplications();
        async function fetchGroupApplications() {
            if(groupId){
                
                $.ajax({
                    url: base+"getgrouploans/disbursedloans/"+gId,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "GET",
                    success: function(response) {
                       var nums = response.data.rows_returned;
                       if (nums !=0) {
                       $('#groupLoan').html('<option value="">Choose group loan</option>');
                       for (var i = 0; i < nums; i++) {
                        var group_applications = response.data.member[i].loanapplications;
                        group_application='';
                        for (a =0; a < group_applications.length; a++){
                          group_application += '<option value="' + group_applications[a].id + '">'+ group_applications[a].loanproduct +'</option>';
                          
                        }
                        
                        $('#groupLoan').append(group_application);
                      }
                    }
                    else if (nums ==0) {
                      $('#groupLoan').html('<option value="">No loan applications found</option>');
                    }
                        $('#groupLoan').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                        });
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                authchecker(fetchGroupApplications);
                            }
                        
                    }
                })
            }
            else{
            $('#groupLoan').html('<option value="">Select group First</option>'); 
            }
        }       
    });


      //get group disburse accounts
      $("#groupId").on("change", function(){
        var gID= $(this).val();
        fetchgroupAccounts();
        async function fetchgroupAccounts() {
            if(groupId){
                
                $.ajax({
                    url: base+"getmemberaccounts/"+gID,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "GET",
                    success: function(response) {
                       var nums = response.data.rows_returned;
                       if (nums !=0) {
                       $('#groupaccountid').html('<option value="">Choose group account</option>');
                       for (var i = 0; i < nums; i++) {
                        var accounts='';
                        accounts += '<option value="' + response.data.accounts[i].id + '">'+  response.data.accounts[i].account +' | '+  response.data.accounts[i].code +'</option>';
                        // console.log(accounts)
                        $('#groupaccountid').append(accounts);
                      }
                    }
                    
                        $('#groupaccountid').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                        });
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                authchecker(fetchgroupAccounts);
                            }
                            else if (xhr.status == '404') {
                                $('#groupaccountid').html('<option value="">No accounts found</option>');
                              }
                    }
                })
            }
            else{
            $('#groupaccountid').html('<option value="">Select group First</option>'); 
            }
        }       
    });
  
      $('#groupLoanDisburseForm').submit(function(event) {
        event.preventDefault();
        if ($('#groupLoanDisburseForm').valid()) {
            var groupLoanDisburseForm = $(this);
            // var form_data=$("#groupaccountid").val();
            var form_data = JSON.stringify(groupLoanDisburseForm.serializeObject())
            // console.log(form_data);
            disburseGroupLoan();
            // submit form data to api
            async function disburseGroupLoan() {
                // start ajax loader
                $.ajax({
                    url: base + "grouploan-disburse",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                      groupLoanDisburseForm[0].reset();
                        var icon = 'success';
                        var message = 'Loan Dibursed Successfuly!'
                        sweetalert(icon, message)
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(disburseGroupLoan);
                        } 
                        else {
                            var icon = 'warning'
                            var message = xhr.responseJSON.messages
                            sweetalert(icon, message)
                        }
                    }
    
                });
                return false;
            }
        }
    });
  
  
  
  })