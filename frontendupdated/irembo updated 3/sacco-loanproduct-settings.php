<?php
      require 'private/initialize.php';
      $pagename = 'Settings';
?>
<?php require 'components/head.php'; ?>
<style>

  /* Hide all steps by default: */
.tab {
  display: none;
}
</style>
  <body id="content"> 
    <!-- navbar-->
    <?php require_once('components/header.php'); ?>
    <div class="d-flex align-items-stretch">
      <?php require_once('components/sidebar.php'); ?>
      <div class="page-holder w-100 d-flex flex-wrap">
        <div class="container-fluid px-xl-5">
        <section class="py-5 mt-3">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-5 mb-lg-0">
                <div class="card-header">
                    <h2 class="h6 mb-0 text-uppercase text-center">View Loan Products </h2>
                    
                  <a href="#" data-toggle="modal" data-target="#addloanproduct" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">Add Loan Product</a>
                </div>
                <div class="card-body mt-3 mb-3">

                    <!-- <h6>Select Loam Product</h6> -->
                    <form class=" row">
                        <div class="form-group col-md-6">
                            <lable id="loanproductname">Select Loam Product</lable>
                            <select type="text" name="loanproduct" id="loanproduct_s"placeholder="Select or Search Loan Product" class="border-1 form-control-md w-100 input-text" required>
                              <option value=""  disabled selected hidden>Select Or Choose Loan Product</option>
                            </select>                         
                          </div>                    
                        </form>
                        <!-- product div -->
                        <div class="productFrom d-none">
                            <div class="btn-group">
                          <button type="" class="btn btn-info btn-sm">PDF</button>
                          <button type="" class="btn btn-warning btn-sm">Print</button>
                        </div>

                        <br>
                        <br>
                   
                    <div class="row">

                    <!-- loan product Information -->
                    <div class="col-lg-8">
                        <!-- Accounts Information -->
                        <div class="bio-data-container">
                            <div class="bio-data-heading">Loan Product Information</div>
                            <div class="bio-data-info " id="loan_product_info">
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Name of Loan Product</div>
                                    <div id="loan_pname"></div>
                                </div>
                                
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Type of Loan</div>
                                    <div id="loantype"></div>
                                </div>
                                
                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Loan Rate Type</div>
                                    <div id="loanratetype"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Equal Installments</div>
                                    <div id="equalinstallments"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Intrest Rate (p.a)</div>
                                    <div id="interestrate"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Processing Fees</div>
                                    <div id="processingfees"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Minimum Amount that can be applied for</div>
                                    <div id="miniamount"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Maximum ammount that can be applied for</div>
                                    <div id="maxamount"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">No. of guarantors allowed</div>
                                    <div id="num_ofguarantors"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Penalties</div>
                                    <div id="_penalties"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Can a client guarantee him/herself?</div>
                                    <div id="clientGuaranteehimself"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Must have security?</div>
                                    <div id="musthavesecurity"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Deduct intrest charge before disbusment</div>
                                    <div id="deductintrestcharge"></div>
                                </div>

                                <div class="row-container d-flex align-items-center justify-content-between bio-info">
                                    <div class="bio-heading">Does interest rate change when defaulted?</div>
                                    <div id="doesinterestratechange"></div>
                                </div>


                            </div>
                          </div>
                    </div>
                        <!-- loan product Information-->
    
                        <div class="col-md-2 col-sm-2">
                            <button class="mt-2 btn btn-outline-dark">Edit <i class="fa fa-edit"></i></button>
                        </div>
                        <div class="col-md-2 col-sm-2">
                            <button class="mt-2 btn btn-outline-danger">Delete <i class="fa fa-trash-alt"></i></button>
                        </div>
                    </div>
                        </div>
                        <!-- product div end -->
                        


                
        </section>
        </div>
      <?php require_once('components/footer.php') ?>
      </div>
    </div>
    <!-- JavaScript files-->
    <?php require_once 'components/javascript.php'; ?>
    <script src="middlewares/header.js"></script>
    <script src="middlewares/settings.js"></script>


    <script>
    </script>
    <?php require_once('partial-components/settings/settings.php'); ?>
    <?php require_once('partial-components/loans/loanproducts.php'); ?>

    <script>


$(".next").click(function(){
    var form = $("#loanproductform");
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
        next_fs = $('#middledata')
      }else if($('#middledata').is(":visible")){
        current_fs = $('#middledata')
        next_fs = $('#contactinfo')
      }
      else if($('#contactinfo').is(":visible")){
        current_fs = $('#contactinfo')
        next_fs = $('#documentsinfo')
      }

      next_fs.show();
      current_fs.hide()
    }
})

$('#backtoBio').click(function(){
  if($('#middledata').is(":visible")){
    current_fs = $('#middledata');
    next_fs = $('#biodata');
  }
  next_fs.show();
  current_fs.hide();
});
$('#previous').click(function(){
  if($('#contactinfo').is(":visible")){
    current_fs = $('#contactinfo');
    next_fs = $('#middledata');
  }
  next_fs.show();
  current_fs.hide();
});




$('#loanproductform').submit(function(event) {
  event.preventDefault();
  if ($('#loanproductform').valid()) {
      var loanproductform = $(this);
      var form_data = JSON.stringify(loanproductform.serializeObject());
      regLoanproduct();
      async function regLoanproduct() {
          $.ajax({
              url: base + "loanproduct",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "POST",
              contentType: 'application/json',
              data: form_data,
              success: function(response) {
                  // console.log(response);
                  $("#addloanproduct").modal('hide')
                  loanproductform[0].reset();
                  var icon = 'success'
                  var message = 'Loan Product Registered!'
                  sweetalert(icon,message)
                  return;
              },
              error: function(xhr, status, error) {
                console.log(xhr);
                  if (xhr.status === 401) {
                      authchecker(regLoanproduct);
                  }
                  var icon = 'warning'
                  var message = xhr.responseJSON.messages
                  sweetalert(icon, message)
              }

          });
          return false;
      }
  }
});



getloanproductinfo();
      async function getloanproductinfo() {
          $.ajax({
              url: base + "loanproduct",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "GET",
              contentType: 'application/json',
              success: function(response) {
                var loanpro_s = response.data.rows_returned;
                for (var i=0; i<loanpro_s; i++){

                  var loanproducts ='';
                  loanproducts += '<option value='+response.data.loanproducts[i].id+'>'+response.data.loanproducts[i].productname+'</option>';
                  $("#loanproduct_s").append(loanproducts);
                }
                $("#loanproduct_s").select2({
                  theme:'bootstrap5',
                  width:'resolve'
                });
                return;
              },
              error: function(xhr, status, error) {
                console.log(xhr);
                  if (xhr.status === 401) {
                      authchecker(getloanproductinfo);
                  }
                  var icon = 'warning'
                  var message = xhr.responseJSON.messages
                  sweetalert(icon, message);
              }

          });
          return false;
      }


      $("#loanproduct_s").on("change", function(){
            var lnp_id= $(this).val();
            get_loanproductinfo();
            async function get_loanproductinfo() {
                if(loanproduct_s){
                    
                    $.ajax({
                        url: base+"loanproduct/"+lnp_id,
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                           var nums = response.data.rows_returned;
                            for (var i = 0; i < nums; i++) {         
                            
                              $(".productFrom").removeClass('d-none');

                            $("#loan_pname").html(response.data.loanproduct[i].productname);
                            $("#loantype").html(response.data.loanproduct[i].loan_type);
                            // $("#installment").append(response.data.loanproduct[i].installemt_payment);
                            
                            $("#loanratetype").html(response.data.loanproduct[i].loan_rate_type);
                            $("#equalinstallments").html(response.data.loanproduct[i].installemt_payment);
                            $("#interestrate").html(response.data.loanproduct[i].interest_rate);
                            $("#processingfees").html(response.data.loanproduct[i].loan_processing_fee);
                            $("#miniamount").html(response.data.loanproduct[i].min_loan_amt);
                            $("#maxamount").html(response.data.loanproduct[i].max_loan_amt);
                            $("#num_ofguarantors").html(response.data.loanproduct[i].number_of_guarantors);
                            
                            $("#_penalties").html(response.data.loanproduct[i].penalty);
                            $("#clientGuaranteehimself").html(response.data.loanproduct[i].selfguarantor);
                            $("#musthavesecurity").html(response.data.loanproduct[i].must_have_security);
                            $("#deductintrestcharge").html(response.data.loanproduct[i].deductinstallbeforedisbursment);
                            $("#doesinterestratechange").html(response.data.loanproduct[i].doesinterestchangedefaulted);

                            }
                            
                        },
                        error: function(xhr){
                                if (xhr.status == '401') {
                                    authchecker(get_loanproductinfo);
                                }
                        }
                    })
                }
                else{
                $('#loanproduct_s').html('<option value="">Select Vendor First</option>'); 
                }
            }       
        });

    </script>
  </body>
</html>
