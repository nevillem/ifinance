$(document).ready(function(){
    // validate Calculatorform loan disbursing form
      $('#saccoLoanCalculatorform').validate({
          rules: {
            interestrate: {
              required: true,
            },
            installments: {
                required: true
              },
            amount: {
                required: true
              },
            amornitizationinterval: {
                required: true
              },
            loan_rate_type: {
                required: true
              },
            
          },
          messages: {
            interestrate:{
                required: "interest rate missing!"
              },
              installments: {
                required: "please specify installments!",
            },
              amount: {
                required: "please specify amount!",
            },
              amornitizationinterval: {
                required: "amortization interval required!",
            },
              loan_rate_type: {
                required: "loan rate type missing!",
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


    
  
//   get loan products
        async function getloanproductView() {
            $.ajax({
                url: base + "getloanproduct",
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "GET",
                success: function(response) {
                    nums = response.data.rows_returned;
                    for (var i = 0; i < nums; i++) {
                        var loanproducts = '';
                        loanproducts += '<option value=' + response.data.loanproducts[i].id + '>' + response.data.loanproducts[i].productname +'</option>';
                        $('#loanproduct').append(loanproducts);
                    }
                    $('#loanproduct').select2({
                        theme: 'bootstrap5',
                        width: 'resolve',
                    });
                },
                error: function(xhr){
                        if (xhr.status == '401') {
                            getloanproductView()
                        }
                }
            })
        }
        getloanproductView();

  
  
  
      //get loan product info
      $("#loanproduct").on("change", function(){
        var laonId= $(this).val();
        fetchLoanInfo();
        async function fetchLoanInfo() {
            if(loanproduct){
                
                $.ajax({
                    url: base+"getloanproduct/"+laonId,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "GET",
                    success: function(response) {
                        var nums = response.data.rows_returned;
                            $('#interestrate').val(response.data.loanproduct[0].interest_rate);
                            $('#loan_rate_type').val(response.data.loanproduct[0].loan_rate_type);
                    },
                    error: function(xhr){
                            if (xhr.status == '401') {
                                authchecker(fetchLoanInfo);
                            }
                           
                    }
                })
            }
        }       
    });


      
  
    $('#saccoLoanCalculatorform').submit(function(event) {
        event.preventDefault();
        if ($('#saccoLoanCalculatorform').valid()) {
            var saccoLoanCalculatorform = $(this);
            var interestrate = $("#interestrate").val();
            var installments = $("#installments").val();
            var amount = $("#amount").val();
            var amornitizationinterval = $("#amornitizationinterval").val();
            var loan_rate_type = $("#loan_rate_type").val();
            var form_data = JSON.stringify({

                "interestrate":interestrate,
                "installments":installments,
                "amount":amount,
                "amornitizationinterval":amornitizationinterval,
                "loan_rate_type":loan_rate_type,
            }
            );

            trigerLoanCalc();
            async function trigerLoanCalc() {
                $.ajax({
                    url: base + "loancalculator",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        console.log(response);
                        var nums =response.data.rows_returned;
                        $("#loancalculator").empty(); 
                        $(".loan_calculator").removeClass('d-none');
                        $("#tblamount").removeClass('d-none');

                        for (var i=0; i<nums; i++){

                          $("#aplliedfor").html(response.data.loanapplication[i].amountappliedfor);
                          $("#interest").html(response.data.loanapplication[i].totalinterest);
                          $("#totamount").html(numberWithCommas(parseFloat((response.data.loanapplication[i].orgamount).replaceAll(",", "")) + parseFloat((response.data.loanapplication[i].totalinterest).replaceAll(",", ""))));
                              
                         var amountappliedfor =parseFloat((response.data.loanapplication[i].orgamount).replaceAll(",", ""));

                          var schedule =response.data.loanapplication[i].loanpaymentschedule;
                          var loanPschedule ='';
                          for ( var s =0; s <schedule.length; s++){
                         var loanbal=(schedule[s].principalamountpaid).replaceAll(",", "");
    
                           amountappliedfor -= parseFloat(loanbal);

                          loanPschedule +='<tr>';
                          loanPschedule +='<td >'+schedule[s].installmentno;+'</td>';
                          loanPschedule +='<td class="text-right">'+numberWithCommas(parseFloat(amountappliedfor) +parseFloat(loanbal))+'</td>';

                          loanPschedule +='<td class="text-right">'+schedule[s].principalamountpaid;+'</td>';
                          loanPschedule +='<td class="text-right">'+schedule[s].principalinterestpaid;+'</td>';
                          loanPschedule +='<td class="text-right">'+schedule[s].totalprincipalamtpaid;+'</td>';

                          loanPschedule +='<td class="text-right">'+schedule[s].loan_balance;+'</td>';

                          loanPschedule +='</tr>'; 
                          }                            
                          $("#loancalculator").append(loanPschedule);

                          
                      }
                      

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === '401') {
                            authchecker(trigerLoanCalc);
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


        $("#printLoan").click(function(){
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
                var divContents = document.getElementById("loans").innerHTML;
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