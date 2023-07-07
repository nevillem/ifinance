<?php
      require 'private/initialize.php';
      $pagename = 'Settings';
?>
<?php require 'components/head.php'; ?>
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
                    <h2 class="h6 mb-0 text-uppercase text-center">System Defaults </h2>
                    
                  <!-- <a href="#" data-toggle="modal" data-target="#addstaff" data-backdrop="static" data-keyboard="false" class="btn btn-outline-dark float-right">New Staff</a> -->
                </div>
                <div class="card-body mt-3 mb-3">
                  
                  <form class="row mt-5 needs-validation" novalidate id="saccoDefaultsettingForm" method="post">
                    
                    <div class="form-group col-6">
                      <lable id="currency" class="form-label">Choose Prefered Currency</lable>
                      <select name="currency" id="currency"  class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>select currency</option>
                        <option value="UGX"selected="selected">Ugandan Shillings</option>
                        <option value="USD" >United States Dollars</option>
                        <option value="EUR">Euro</option>
                        <option value="GBP">United Kingdom Pounds</option>
                        <option value="DZD">Algeria Dinars</option>
                        <option value="ARP">Argentina Pesos</option>
                        <option value="AUD">Australia Dollars</option>
                        <option value="ATS">Austria Schillings</option>
                        <option value="BSD">Bahamas Dollars</option>
                        <option value="BBD">Barbados Dollars</option>
                        <option value="BEF">Belgium Francs</option>
                        <option value="BMD">Bermuda Dollars</option>
                        <option value="BRR">Brazil Real</option>
                        <option value="BGL">Bulgaria Lev</option>
                        <option value="CAD">Canada Dollars</option>
                        <option value="CLP">Chile Pesos</option>
                        <option value="CNY">China Yuan Renmimbi</option>
                        <option value="CYP">Cyprus Pounds</option>
                        <option value="CSK">Czech Republic Koruna</option>
                        <option value="DKK">Denmark Kroner</option>
                        <option value="NLG">Dutch Guilders</option>
                        <option value="XCD">Eastern Caribbean Dollars</option>
                        <option value="EGP">Egypt Pounds</option>
                        <option value="FJD">Fiji Dollars</option>
                        <option value="FIM">Finland Markka</option>
                        <option value="FRF">France Francs</option>
                        <option value="DEM">Germany Deutsche Marks</option>
                        <option value="XAU">Gold Ounces</option>
                        <option value="GRD">Greece Drachmas</option>
                        <option value="HKD">Hong Kong Dollars</option>
                        <option value="HUF">Hungary Forint</option>
                        <option value="ISK">Iceland Krona</option>
                        <option value="INR">India Rupees</option>
                        <option value="IDR">Indonesia Rupiah</option>
                        <option value="IEP">Ireland Punt</option>
                        <option value="ILS">Israel New Shekels</option>
                        <option value="ITL">Italy Lira</option>
                        <option value="JMD">Jamaica Dollars</option>
                        <option value="JPY">Japan Yen</option>
                        <option value="JOD">Jordan Dinar</option>
                        <option value="KRW">Korea (South) Won</option>
                        <option value="LBP">Lebanon Pounds</option>
                        <option value="LUF">Luxembourg Francs</option>
                        <option value="MYR">Malaysia Ringgit</option>
                        <option value="MXP">Mexico Pesos</option>
                        <option value="NLG">Netherlands Guilders</option>
                        <option value="NZD">New Zealand Dollars</option>
                        <option value="NOK">Norway Kroner</option>
                        <option value="PKR">Pakistan Rupees</option>
                        <option value="XPD">Palladium Ounces</option>
                        <option value="PHP">Philippines Pesos</option>
                        <option value="XPT">Platinum Ounces</option>
                        <option value="PLZ">Poland Zloty</option>
                        <option value="PTE">Portugal Escudo</option>
                        <option value="ROL">Romania Leu</option>
                        <option value="RUR">Russia Rubles</option>
                        <option value="SAR">Saudi Arabia Riyal</option>
                        <option value="XAG">Silver Ounces</option>
                        <option value="SGD">Singapore Dollars</option>
                        <option value="SKK">Slovakia Koruna</option>
                        <option value="ZAR">South Africa Rand</option>
                        <option value="KRW">South Korea Won</option>
                        <option value="ESP">Spain Pesetas</option>
                        <option value="XDR">Special Drawing Right (IMF)</option>
                        <option value="SDD">Sudan Dinar</option>
                        <option value="SEK">Sweden Krona</option>
                        <option value="CHF">Switzerland Francs</option>
                        <option value="TWD">Taiwan Dollars</option>
                        <option value="THB">Thailand Baht</option>
                        <option value="TTD">Trinidad and Tobago Dollars</option>
                        <option value="TRL">Turkey Lira</option>
                        <option value="VEB">Venezuela Bolivar</option>
                        <option value="ZMK">Zambia Kwacha</option>
                        <option value="EUR">Euro</option>
                        <option value="XCD">Eastern Caribbean Dollars</option>
                        <option value="XDR">Special Drawing Right (IMF)</option>
                        <option value="XAG">Silver Ounces</option>
                        <option value="XAU">Gold Ounces</option>
                        <option value="XPD">Palladium Ounces</option>
                        <option value="XPT">Platinum Ounces</option>

                      </select>
                        </div>

                        <!--  -->

                        <div class="form-group col-6">
                        <lable id="account" class="form-label">Choose Prefered Shares Account</lable>
                        <select name="shares_account" id="shares_account"  class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>select shares account</option>

                      </select>

                        </div>

                        <div class="form-group col-6">
                        <lable id="Loan" class="form-label">Choose Prefered Loan Interest Account</lable>
                        <select name="loan_interest_account" id="loan_interest_account"  class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>select loan interest account</option>

                        </select>

                        </div>

                        <div class="form-group col-6">
                        <lable id="loans" class="form-label">No Of Loans One can guarantee</lable>
                        <input type="number" name="numberofloanstoguarantee" id="numberofloanstoguarantee" placeholder="Enter No." class="form-control border-0 shadow form-control-md input-text">
                        </div>

                        <div class="form-group col-6">
                        <lable id="funds" class="form-label">Default Account For Funds Withdraw Fees</lable>
                        <select name="withdraws_account" id="withdraws_account"  class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>select withdraw account</option>

                        </select>
                        </div>

                        <div class="form-group col-6">
                        <lable id="accounting-period" class="form-label">Select When Accounting Period Starts</lable>
                        <input type="text" onfocus="(this.type = 'date')" name="accounting_period" id="accounting_period" placeholder="Choose Date *" class="form-control border-0 shadow form-control-md input-text" required>                            

                        </div>
                        <div class="form-group col-md-12 text-right">
                          <button type="submit" class="btn btn-primary text-center login">Update</button>
                          <div class="loading p-2 col-xs-1" align="right"><div class="loader"></div></div>
                        </div>
                        
                      </form>
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
  </body>
</html>
