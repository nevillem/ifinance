$(document).ready(function(){
  // validate individual loan processing form
    $('#loanProcessingForm').validate({
        rules: {
          memberid: {
            required: true,
          },
          loanapplicationid: {
              required: true
            }
        },
        messages: {
          memberid:{
              required: "please select member account!"
            },
            loanapplicationid: {
              required: "please select specific loan to process!",
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

  // validate Group loan processing form
    $('#groupLoanProcessingForm').validate({
        rules: {
          groupId: {
            required: true,
          },
          groupLoan: {
              required: true
            }
        },
        messages: {
          groupId:{
              required: "please select group account!"
            },
            groupLoan: {
              required: "please select specific loan to process!",
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
                    $('#memberid').append(members);
                }
                $('#memberid').select2({
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


    //get member loans
    $("#memberid").on("change", function(){
      var memId= $(this).val();
      fetchLoansApplications();
      async function fetchLoansApplications() {
          if(memId){
              
              $.ajax({
                  url: base+"getmemberloans/proccessloan/"+memId,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "GET",
                  success: function(response) {
                     var nums = response.data.rows_returned;
                     if (nums !=0) {
                     $('#loanapplicationid').html('<option value="">Choose loan aplication</option>');
                     for (var i = 0; i < nums; i++) {
                      var applications = response.data.member[i].loanapplications;
                      individual_application='';
                      for (a =0; a < applications.length; a++){
                        individual_application += '<option value="' + applications[a].id + '">'+ applications[a].loanproduct +'</option>';
                        
                      }
                      
                      $('#loanapplicationid').append(individual_application);
                    }
                  }
                  else if (nums ==0) {
                    $('#loanapplicationid').html('<option value="">No loan applications found</option>');
                  }
                      $('#loanapplicationid').select2({
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
          $('#loanapplicationid').html('<option value="">Select Member First</option>'); 
          }
      }       
  });


    //get member loans
    $("#loanapplicationid").on("change", function(){
      var loanID= $(this).val();
      fetchLoanschedule();
      async function fetchLoanschedule() {
          if(loanID){
              
              $.ajax({
                  url: base+"processloan/"+loanID,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "GET",
                  success: function(response) {
                    $("#lschedules").empty(); 
                    $("#scheduletable").removeClass('d-none');
                    $("#lamount").removeClass('d-none');
                    var nums =response.data.rows_returned;
                    for (var i=0; i<nums; i++){
                      $("#membername").html(' '+response.data.loanapplication[i].firstname +' '+ response.data.loanapplication[i].lastname);
                      $("#loanproduct").html(' '+response.data.loanapplication[i].loanproduct);
                  
                      $("#aplliedfor").html(response.data.loanapplication[i].amountappliedfor);
                      $("#totinterest").html(response.data.loanapplication[i].totalinterest);
                      $("#totamount").html(numberWithCommas(parseFloat((response.data.loanapplication[i].amountappliedfor).replaceAll(",", "")) + parseFloat((response.data.loanapplication[i].totalinterest).replaceAll(",", ""))));
                      var amountappliedfor =(response.data.loanapplication[i].amountappliedfor).replaceAll(",", "");
                      var schedule =response.data.loanapplication[i].loanpaymentschedule;
                      var loanPschedule ='';
                       var no=0;

                      for ( var s =0; s <schedule.length; s++){
                         no ++; 
                        var principalamt=schedule[s].principalamountpaid;
                        var loanbal=(schedule[s].principalamountpaid).replaceAll(",", "");;
                        amountappliedfor -= parseFloat(loanbal);

                        loanPschedule +='<tr>';
                        loanPschedule +='<td>'+no+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].paymentdate+'</td>';
                        loanPschedule +='<td class="text-right">'+numberWithCommas(parseFloat(amountappliedfor) +parseFloat(loanbal))+'</td>';
                        
                        loanPschedule +='<td class="text-right">'+schedule[s].principalamountpaid;+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].principalinterestpaid;+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].totalprincipalamtpaid;+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].loan_balance;+'</td>';
                        
                        loanPschedule +='</tr>'; 
                      }                            
                      $("#tableProcess").append(loanPschedule);
                  
                      
                  }

                  },
                  error: function(xhr){
                          if (xhr.status == '401') {
                              authchecker(fetchLoanschedule);
                          }
                         
                  }
              })
          }

      }       
  });

    $('#loanProcessingForm').submit(function(event) {
      event.preventDefault();
      if ($('#loanProcessingForm').valid()) {
          var loanProcessingForm = $(this);
          var loanappId = $("#loanapplicationid").val();
        //   console.log(loanappId);
          processMemberLoan();
          // submit form data to api
          async function processMemberLoan() {
              // start ajax loader
              $.ajax({
                  url: base + "processloan/"+loanappId,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "PATCH",
                  contentType: 'application/json',
                //   data: form_data,
                  success: function(response) {
                    console.log(response);
                    loanProcessingForm[0].reset();
                      var icon = 'success';
                      var message = 'Member Loan Processed!'
                      sweetalert(icon, message)
                      
                      return;
                  },
                  error: function(xhr, status, error) {
                      if (xhr.status === 401) {
                          authchecker(processMemberLoan);
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


    //get group loans
    $("#groupId").on("change", function(){
      var gId= $(this).val();
      
      fetchGroupApplications();
      async function fetchGroupApplications() {
          if(groupId){
              
              $.ajax({
                  url: base+"getgrouploans/proccessloan/"+gId,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "GET",
                  success: function(response) {
                     var nums = response.data.rows_returned;
                     if (nums !=0) {
                     $('#groupLoan').html('<option value="">Choose group aplication</option>');
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

//group schedule
    $("#groupLoan").on("change", function(){
      var loanID= $(this).val();
      fetchLoanschedule();
      async function fetchLoanschedule() {
          if(loanID){
              
              $.ajax({
                  url: base+"groupprocessloan/"+loanID,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "GET",
                  success: function(response) {
                    $("#tablegroupProcess").empty(); 
                    $("#groupdiv").removeClass('d-none');
                    var nums =response.data.rows_returned;
                    for (var i=0; i<nums; i++){
                      $("#groupname").html(' '+response.data.loanapplication[i].firstname +' '+ response.data.loanapplication[i].lastname);
                      $("#gloanproduct").html(' '+response.data.loanapplication[i].loanproduct);
                  
                      $("#groupaplliedfor").html(response.data.loanapplication[i].amountappliedfor);
                      $("#grouptotinterest").html(response.data.loanapplication[i].totalinterest);
                      $("#grouptotamount").html(numberWithCommas(parseFloat((response.data.loanapplication[i].amountappliedfor).replaceAll(",", "")) + parseFloat((response.data.loanapplication[i].totalinterest).replaceAll(",", ""))));
                      var amountappliedfor =(response.data.loanapplication[i].amountappliedfor).replaceAll(",", "");
                      var schedule =response.data.loanapplication[i].loanpaymentschedule;
                      var loanPschedule ='';
                      var no=0;
                      for ( var s =0; s <schedule.length; s++){
                        no++;
                        var principalamt=schedule[s].principalamountpaid;
                        var loanbal=(schedule[s].principalamountpaid).replaceAll(",", "");;
                        amountappliedfor -= parseFloat(loanbal);

                        loanPschedule +='<tr>';
                        loanPschedule +='<td >'+no+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].paymentdate+'</td>';
                        loanPschedule +='<td class="text-right">'+numberWithCommas(parseFloat(amountappliedfor) +parseFloat(loanbal))+'</td>';
                        
                        loanPschedule +='<td class="text-right">'+schedule[s].principalamountpaid;+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].principalinterestpaid;+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].totalprincipalamtpaid;+'</td>';
                        loanPschedule +='<td class="text-right">'+schedule[s].loan_balance;+'</td>';
                        
                        loanPschedule +='</tr>'; 
                      }                            
                      $("#tablegroupProcess").append(loanPschedule);
                  
                      
                  }

                  },
                  error: function(xhr){
                          if (xhr.status == '401') {
                              authchecker(fetchLoanschedule);
                          }
                         
                  }
              })
          }

      }       
  });
// process group loan

    $('#groupLoanProcessingForm').submit(function(event) {
      event.preventDefault();
      if ($('#groupLoanProcessingForm').valid()) {
          var groupLoanProcessingForm = $(this);
          var grouploanId = $("#groupLoan").val();
          // console.log(grouploanId)
          processGroupLoan();
          // submit form data to api
          async function processGroupLoan() {
              // start ajax loader
              $.ajax({
                  url: base + "groupprocessloan/"+grouploanId,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "PATCH",
                  contentType: 'application/json',
                //   data: form_data,
                  success: function(response) {
                    groupLoanProcessingForm[0].reset();
                      var icon = 'success';
                      var message = 'Group Loan Processed!'
                      sweetalert(icon, message)

                      return;
                  },
                  error: function(xhr, status, error) {
                      if (xhr.status === 401) {
                          authchecker(processGroupLoan);
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
// end process group loan


})