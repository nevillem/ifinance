      <?php
            require 'private/initialize.php';
            $pagename = 'Dashboard';
      ?>
      <?php require 'components/head.php'; ?>
      <style>
      .block_savings .border-left-success {     
      border-left: 0.25rem solid #1cc88a!important;
      }

      .block_withdraw .border-left-danger {
          border-left: 0.25rem solid #dc3545!important;
      }

      .block_members .border-left-info {
          border-left: 0.25rem solid #36b9cc!important;
      }
      .block_login .border-left-warning {
          border-left: 0.25rem solid #f6c23e!important;
      }


      .block_savings .pb-2, .py-2 ,.block_withdraw .pb-2, .py-2, .block_members .pb-2, .py-2, .block_login .pb-2, .py-2 {
          padding-bottom: 0.5rem!important;
      }
      .block_savings .pt-2, .py-2, .block_withdraw .pt-2, .py-2, .block_members .pt-2, .py-2 , .block_login .pt-2, .py-2 {
          padding-top: 0.5rem!important;
      }
      .block_savings .h-100, .block_withdraw .h-100, .block_members .h-100, .block_login .h-100 {
          height: 100%!important;
      }
      .block_savings .shadow,.block_withdraw .shadow ,.block_members .shadow,.block_login .shadow {
          box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important;
      }
      .block_savings .card, .block_withdraw .card , .block_members .card , .block_login .card {
          position: relative;
          display: flex;
          flex-direction: column;
          min-width: 0;
          word-wrap: break-word;
          background-color: #fff;
          background-clip: border-box;
          border: 1px solid #e3e6f0;
          border-radius: 0.35rem;
      }

      .block_savings .card  .card-body, .block_withdraw .card  .card-body , .block_members .card  .card-body, .block_login .card  .card-body {
          flex: 1 1 auto;
          min-height: 1px;
          padding: 1.25rem;
      }

      .block_savings .card  .card-footers, .block_withdraw .card  .card-footers{
          flex: 1 1 auto;
          padding: 1.25rem;
          border-radius: 0;
          background: #fff;
          border-top:none;
      }

      .block_savings h5, .h5, .block_withdraw h5, .h5, .block_members h5, .h5, .block_login h5, .h5 {
          font-size: 12px !important;
      }

      </style>
        <body>
          <!-- navbar-->
          <?php require_once('components/user/header.php'); ?>
          <div class="d-flex align-items-stretch">
            <?php require_once('components/user/sidebar.php'); ?>
            <div class="page-holder w-100 d-flex flex-wrap">
              <div class="container-fluid px-xl-5">
                <section class="pt-3">
                  <div class="row">

      <!-- <style>
        .card-new{
          border-radius: 10% !important;
        }
      </style> -->
      <div class="col-xl-3 col-md-6 mb-4 block_savings" >                             
      <div class="card card-new border-left-success shadow h-100 py-2">                                 
      <div class="card-body">                                     
      <div class="row no-gutters align-items-center">                                         
      <div class="col mr-2">                                             
      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">                                                 
      Total Savings
      <!--<div class="dot mr-3 bg-violet"></div><span class="text-gray" id="savings">UGX. 40,000</span>-->
      </div> 
      <div class="h5 mb-0 font-weight-bold text-gray-800">UGX. <span id="savings">0</span></div>                                         
      </div>                                         
      <div class="col-auto icon text-white bg-green">                                             
      <i class="fas fa-dollar-sign fa-2x text-white-300"></i>                                         
      </div>  
      </div>                                 
      </div>  
      <div class="card-footers card-footer py-0 ">                            
      <div class="row align-items-center ">         
      <div class="col-2 text-end text-green"><i class="fas fa-caret-up"></i>                              
      </div>   
      <div class="col-10">                                
      <p class="mb-0 tex-dark"><span id="percantagesavings">0</span>% Saving Rate</p>                              
      </div>                                                      
      </div>                          
      </div>
      </div>                         
      </div>

      <div class="col-xl-3 col-md-6 mb-4 block_withdraw">                             
      <div class="card border-left-danger shadow h-100 py-2">                                 
      <div class="card-body">                                     
      <div class="row no-gutters align-items-center">                                         
      <div class="col mr-2">                                             
      <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">                                                 
      Total Withdraws</div>                                             
      <div class="h5 mb-0 font-weight-bold text-gray-800">UGX. <span id="withdraws">0</span></div>                                         
      </div>                                         
      <div class="col-auto icon text-white bg-danger">                                             
      <i class="fas fa-dollar-sign fa-2x text-white-300"></i>                                         
      </div>                                     
      </div>                                 
      </div>            
      <div class="card-footers card-footer py-0 ">                            
      <div class="row align-items-center ">         
      <div class="col-2 text-end text-red"><i class="fas fa-caret-down"></i>                            
      </div>   
      <div class="col-10">                                
      <p class="mb-0 tex-dark"><span id="percantagewithdraws">0</span>% Withdraw Rate</p>                              
      </div>                                                      
      </div>                          
      </div>

      </div>                         
      </div>

      <div class="col-xl-3 col-md-6 mb-4 block_members">                             
      <div class="card border-left-info shadow h-100 py-2">                                 
      <div class="card-body">                                     
      <div class="row no-gutters align-items-center">                                         
      <div class="col mr-2">                                             
      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">                                                 
      Members / Groups</div>                                             
      <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="members">0</span> / <span id="groups">0</span></div>                                         
      </div>                                         
      <div class="col-auto icon text-white bg-info">                                             
      <i class="fas fa-users fa-2x text-white"></i>                                         
      </div>                                     
      </div>                                 
      </div>
      <div class="bg-white card-footer py-0 ">                            
      <div class="row align-items-center ">         
      <div class="col-2 text-end text-info"><i class="fas fa-caret-right"></i>                            
      </div>   
      <div class="col-10">                                
      <p class="mb-0 tex-dark">
        <span id="totalaccounts" class="font-weight-bold text-dark-800">0</span> Total Accounts</p>                              
      </div>                                                      
      </div>                          
      </div>                             
      </div>                         
      </div>

      <div class="col-xl-3 col-md-6 mb-4 block_login">                             
      <div class="card border-left-warning shadow h-100 py-2">                                 
      <div class="card-body">                                     
      <div class="row no-gutters align-items-center">                                         
      <div class="col mr-2">                                             
      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">                                                 
      Logins</div>                                             
      <div class="h5 mb-0 font-weight-bold text-white-300"><span id="activity">0</span></div>                                         
      </div>                                         
      <div class="col-auto icon text-white bg-warning">
      <i class="fas fa-solid fa-user-check fa- text-white"></i>                                                
      </div>                                     
      </div>                                 
      </div>                             
      </div>                         
      </div>



      <!-- <div class="col-xl-3 col-md-6 mb-4 block_login">
      <div class="card border-left-warning shadow h-100 py-2 bg-blue">
      <div class="card-body">
      <div class="row no-gutters align-items-center">
      <div class="col mr-2">
      <div class="text-xs font-weight-bold text-uppercase mb-1 text-white-300">
      Logins</div>
      <div class="h5 mb-0 font-weight-bold text-white-800">5</div>
      </div>
      <div class="col-auto">
      <i class="fas fa-user text-white-300"></i>
      </div>                                     
      </div>                                 
      </div>                             
      </div>                         
      </div> -->





                  <!-- <div class="col-xl-3 col-lg-6 mb-4 mb-xl-0">
                      <div class="bg-white shadow roundy p-4 h-100 d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1 d-flex align-items-center">
                          <div class="dot mr-3 bg-violet"></div>
                          <div class="text">
                            <h6 class="mb-0">Savings</h6><span class="text-gray" id="savings">0</span>
                          </div>
                        </div>
                        <div class="icon text-white bg-violet"><i class="fas fa-server"></i></div>
                      </div>
                    </div>
                  
                    <div class="col-xl-3 col-lg-6 mb-4 mb-xl-0">
                      <div class="bg-white shadow roundy p-4 h-100 d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1 d-flex align-items-center">
                          <div class="dot mr-3 bg-blue"></div>
                          <div class="text">
                            <h6 class="mb-0">Withdraws</h6><span class="text-gray" id="withdraws">0</span></span>
                          </div>
                        </div>
                        <div class="icon text-white bg-blue"><i class="fa fa-dolly-flatbed"></i></div>
                      </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-4 mb-xl-0">
                      <div class="bg-white shadow roundy p-4 h-100 d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1 d-flex align-items-center">
                          <div class="dot mr-3 bg-green"></div>
                          <div class="text">
                            <h6 class="mb-0">Members</h6><span class="text-gray" id="members">0</span>
                          </div>
                        </div>
                        <div class="icon text-white bg-green"><i class="far fa-clipboard"></i></div>
                      </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-4 mb-xl-0">
                      <div class="bg-white shadow roundy p-4 h-100 d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1 d-flex align-items-center">
                          <div class="dot mr-3 bg-red"></div>
                          <div class="text">
                            <h6 class="mb-0">Logins</h6><span class="text-gray" id="login">0</span>
                          </div>
                        </div>
                        <div class="icon text-white bg-red"><i class="fas fa-receipt"></i></div>
                      </div>
                    </div>-->
                  </div>
                </section>
                <section>
                  <div class="row mb-4">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                      <div class="card">
                        <div class="card-header">
                          <h2 class="h6 text-uppercase mb-0">Withdraws Vs Savings</h2>
                        </div>
                        <div class="card-body">
                          <p class="text-gray">Monthly trend between Savings and Withdraws</p>
                          <div class="chart-holder">
                            <canvas id="lineChart" style="max-height: 14rem !important;" class="w-100"></canvas>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="div col-lg-3">
                    <div class="card mb-4">
                        <div class="card-body">
                          <div class="row align-items-center flex-row">
                            <div class="col-lg-5">
                              <h6 class="mb-2 d-flex align-items-center text-dark text-bold"><span id="daily_deposit_balance">0</span><span class="dot bg-green d-inline-block ml-3"></span></h6>
                              <span class="text-muted text-uppercase small">Daily</span>
                              <hr><small class="text-muted">Deposit Amount</small>
                            </div>
                            <div class="col-lg-7">
                              <canvas id="pieChartHomeDaily"></canvas>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="card mb-4">
                        <div class="card-body">
                          <div class="row align-items-center flex-row">
                            <div class="col-lg-5">
                              <h6 class="mb-2 d-flex align-items-center"><span id="daily_withdraw_balance">0</span><span class="dot bg-red d-inline-block ml-3"></span></h6>
                              <span class="text-muted text-uppercase small">Daily</span>
                              <hr><small class="text-muted">Withdraw Amount</small>
                            </div>
                            <div class="col-lg-7">
                              <canvas id="pieChartHomeWith"></canvas>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-3 mb-4 mb-lg-0">
                        
                    <div class="bg-white shadow px-4 py-3 d-flex align-items-center justify-content-between mb-2">
                    <div class="flex-grow-1 d-flex align-items-center">
                    <div class="dot mr-3 bg-violet"></div>
                    <div class="text">
                    <h6 class="mb-0">TOTAL SMS SENT</h6>
                    </div>
                    </div>
                    <div class="text-dark">
                    <span id="sms">0</span>
                    </div>  
                    </div>

                  <div class="bg-white shadow  px-4 py-3 d-flex align-items-center justify-content-between mb-2">                   
                  <div class="flex-grow-1 d-flex align-items-center">                     
                  <div class="dot mr-3 bg-green"></div>                    
                  <div class="text">                       
                  <h6 class="mb-0 "> <span id="share_balance">0</span>
                    </h6>
                  <span class="text-dark text-uppercase">Share Amount</span>                    
                  </div>                   
                  </div>                   
                  <div class="text-dark"></div>                 
                  </div>
                                  <div class="card">
                                    <div class="card-body">
                                    <canvas id="barChart" ></canvas>
                                    </div>
                                  </div>
                                  
                                </div>
                              </div>
                </section>
                
                <section>
                  <div class="row mb-1">
                    <div class="col-lg-6">
                      <div class="card mb-5 mb-lg-0">
                        <div class="card-header">
                          <h2 class="h6 mb-0 text-uppercase">Recent Deposit Transaction</h2>
                        </div>
                        <div class="card-body" id="daily_deposits_dom">
                          <!-- <p class="text-gray mb-5">Depos Transactions</p> -->
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="card mb-5 mb-lg-0">
                        <div class="card-header">
                          <h2 class="h6 mb-0 text-uppercase">Recent Withdraw Transaction</h2>
                        </div>
                        <div class="card-body" id="daily_withdraws_dom">
                          <!-- <p class="text-gray mb-5">Recent Transactions</p> -->
                        </div>
                      </div>
                    </div>
                    
                  </div>
                </section>
              </div>
              <?php require_once 'components/footer.php'; ?>
            </div>
          </div>
          <!-- JavaScript files-->
          <?php require_once 'components/user/javascript.php'; ?>
          <script src="assets/vendor/chart.js/Chart.min.js"></script>
          <script src="assets/js/charts-home.js"></script>
          <script src="middlewares/user/header.js"></script>
          <script src="middlewares/user/dashboard.js"> </script>
         
          
        </body>
      </html>
