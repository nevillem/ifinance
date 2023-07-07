
<?php
        require_once __DIR__. "/private/initialize.php";
        $pagename = "Verification";
 ?>

<?php require_once('components/head.php'); ?>
  <body>
    <div class="page-holder d-flex align-items-center">
      <div class="container">
        <div class="d-flex justify-content-center py-5">
          <div class="row card" style="width: 500px;">
            <div class="card-header">
              <div class="col-lg-12 px-lg-5">
                <h1 class="text-base card-title text-warning text-uppercase mb-0">Irembo Finance</h1>
                <p class="text-muted">Verify SACCO account.</p>
                <p class="text-muted">Check for one time code.</p>
            </div>
          </div>
            <div class="card-body">
            <form id="verify" action="#" method="post" class="mt-0 mb-3">
              <div class="form-group mb-3">
                <input type="text" name="otp" id="otp" placeholder="Enter O.T.P" class="form-control border-0 shadow form-control-lg">
              </div>
              <button type="submit" class="btn btn-outline-warning login shadow px-5">Verify</button>
              <div class="loading p-2 col-xs-1" align="center">
                <div class="loader"></div>
              </div>
            </form>
            <form id="resend" action="#" method="post">
                  <input type="submit" id="submitButton" onClick="$(this).click(function() {return false;});" class="float-right btn btn-danger btn-sm mt-2 shadow px-2" disabled="disabled" value="Resend O.T.P">
                  <p><small class="text-sm-danger float-right mt-0 shadow px-2" id="timeLeft"></small></p>
            </form>
          </div>
          </div>
        </div>
        <div class="float-center mr-1">
          <p class="mt-0 mb-0 text-center"><span class="text-dark">Powered by</span> <a href="#" class="external text-warning">MOB<i class="fa fa-info-circle text-success" aria-hidden="true"></i>TUNGO</a> <br/> <span class="text-dark">&copy; <?php echo $year; ?> Ahu</span>riire (U) LTD</p>
      </div>
        </div>
      </div>
    <!-- JavaScript files-->
  <?php require_once('components/javascript.php'); ?>
  <script src="middlewares/verification.js"></script>
        <script type="text/javascript">
        var countdownNum = 60;
            window.onload=function() {
              incTimer();
            }
            function incTimer(){
              setTimeout (function(){
                if(countdownNum != 0){
                  countdownNum--;
                  document.getElementById('timeLeft').innerHTML = 'You can resend O.T.P in: ' + countdownNum + ' seconds';
                  incTimer();
                } else {
                  document.getElementById('submitButton').disabled = null;
                  document.getElementById('timeLeft').innerHTML = 'Click to resend O.T.P';
                }
              },1000);
            }
        </script>
  </body>
</html>
