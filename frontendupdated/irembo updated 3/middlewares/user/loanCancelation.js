$(document).ready(function(){
    // validate individual loan processing form
      $('#LoanCancelationForm').validate({
          rules: {
            loanappid: {
              required: true,
            },
            cancel_action: {
                required: true
              },
            reason: {
                required: true
              },
          },
          messages: {
            loanappid:{
                required: "please, specify a loan to cancel"
              },
              cancel_action: {
                required: "don't leve out this field!",
            },
              reason: {
                required: "cancelation reason mandatory!",
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
        console.log(memId);
        fetchLoansApplications();
        async function fetchLoansApplications() {
            if(memberid){
                
                $.ajax({
                    url: base+"getmemberloans/cancelloan/"+memId,
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


              //for change event
              $('input[name="cancel_action"]').on('change', function(event) {
                event.preventDefault();
              //To allow users select only one checkbox
              $('input[name="cancel_action"]').not(this).prop('checked', false);
              //Get selected checkbox value
              //  alert($('input[name="cancel_action"]:checked').val());
              });

    // cancel loan

      $('#LoanCancelationForm').submit(function(event) {
        event.preventDefault();
        if ($('#LoanCancelationForm').valid()) {

            var LoanCancelationForm = $(this);
            $(".cancelLoader").removeClass('d-none');
            var form_data = JSON.stringify(LoanCancelationForm.serializeObject());
            console.log(form_data);
            cancelIndividualLoan();
            // submit form data to api
            async function cancelIndividualLoan() {
                // start ajax loader
                $.ajax({
                    url: base + "cancelledloanapp",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                      LoanCancelationForm[0].reset();
                        var icon = 'success';
                        var message = 'Member Loan Canceled!'
                        sweetalert(icon, message)
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(cancelIndividualLoan);
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