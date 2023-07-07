
text/x-generic dashboard.php ( exported SGML document, ASCII text )
	<?php include "header.php" ?>

	<div class="main-wrapper">

	<?php include "nav_bar.php" ?>

	<!-- partial -->
		<div class="page-wrapper">

			<!-- partial:partials/_navbar.html -->
	<?php include"right_nav.php";?>
			<!-- partial -->
			<div class="page-content">

        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
          <div>
            <h4 class="mb-3 mb-md-0">Aggregator Dashboard</h4>
          </div>
          <div class="d-flex align-items-center flex-wrap text-nowrap">
            <div class="input-group date datepicker wd-200 me-2 mb-2 mb-md-0" id="dashboardDate">
              <span class="input-group-text input-group-addon bg-transparent border-success">
			  <i data-feather="calendar" class=" text-primary"></i></span>
              <input type="text" class="form-control border-success bg-transparent">
            </div>
            <button type="button" class="btn btn-outline-success btn-icon-text me-2 mb-2 mb-md-0">
              <i class="btn-icon-prepend" data-feather="printer"></i>
              Print
            </button>
            <button type="button" class="btn btn-success btn-icon-text mb-2 mb-md-0">
              <i class="btn-icon-prepend" data-feather="download-cloud"></i>
              Download Report
            </button>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
              <div class="col-md-4 grid-margin stretch-card">
                <div class="card border-left-success">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline">
                      <h6 class="card-title mb-0">Groups</h6>
                      <div class="dropdown mb-2">
                        <button class="btn p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <a class="dropdown-item d-flex align-items-center" href="groups"><i data-feather="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                      </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6 col-md-12 col-xl-5">
                        <h3 class="mb-2 group_total"></h3>
                        <div class="d-flex align-items-baseline">
                          <p class="text-success">
                            <span class="gpercentage"></span>
                            <i data-feather="arrow-up" class="icon-sm mb-1"></i>
                          </p>
                        </div>
                      </div>
                      <div class="col-6 col-md-12 col-xl-7">
                        <div id="groupsChart" class="mt-md-3 mt-xl-0"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4 grid-margin stretch-card">
                <div class="card border-left-warning">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline">
                      <h6 class="card-title mb-0">Farmers</h6>
                      <div class="dropdown mb-2">
                        <button class="btn p-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                          <a class="dropdown-item d-flex align-items-center" href="farmers"><i data-feather="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6 col-md-12 col-xl-5">
                        <h3 class="mb-2 total_farmers"></h3>
                        <div class="d-flex align-items-baseline">
                          <p class="text-danger">
                            <span class="percentage"></span>
                            <i data-feather="arrow-up" class="icon-sm mb-1"></i>
                          </p>
                        </div>
                      </div>
                      <div class="col-6 col-md-12 col-xl-7">
                        <div id="famersAgg" class="mt-md-3 mt-xl-0"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4 grid-margin stretch-card">
                <div class="card border-left-primary">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline">
                      <h6 class="card-title mb-0">Agents</h6>
                      <div class="dropdown mb-2">
                        <button class="btn p-0" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                          <a class="dropdown-item d-flex align-items-center" href="users"><i data-feather="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                  	</div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6 col-md-12 col-xl-5">
                        <h3 class="mb-2 total_agents"></h3>
                        <div class="d-flex align-items-baseline">
                          <p class="text-success">
                            <span class="apercentage"></span>
                            <i data-feather="arrow-up" class="icon-sm mb-1"></i>
                          </p>
                        </div>
                      </div>
                      <div class="col-6 col-md-12 col-xl-7">
                        <div id="agentsChart" class="mt-md-3 mt-xl-0"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> <!-- row -->

        <div class="row">
          <div class="col-lg-7 col-xl-8 grid-margin stretch-card">
            <div class="card">
			<div class="card-header">
			  <div class="d-flex justify-content-between align-items-baseline mb-2">
                  <h6 class="card-title mb-0">Chart showing Farmers</h6>
                  <div class="dropdown mb-2">
                    <button class="btn p-0" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                      <a class="dropdown-item d-flex align-items-center" href="famers"><i data-feather="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                      <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-feather="download" class="icon-sm me-2"></i> <span class="">Download</span></a>
                    </div>
                  </div>
                </div>
			</div>
              <div class="card-body">

                <canvas id="chartjsArea"></canvas>

              </div>
            </div>
          </div>
          <div class="col-lg-5 col-xl-4 grid-margin stretch-card">
            <div class="card">
			<div class="card-header">
			  <div class="d-flex justify-content-between align-items-baseline mb-2">
                  <h6 class="card-title mb-0">Pie Chart showing Farmers based on gender </h6>
                  <div class="dropdown mb-2">
                    <button class="btn p-0" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                      <a class="dropdown-item d-flex align-items-center" href="farmers"><i data-feather="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                </div>
                  </div>
                </div>
							</div>
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline">
                <div id="apexPie"></div>
                <div class="row mb-3">
                  <div class="col-6 d-flex justify-content-end">
                    <div>
                                         <!--content here-->

                    </div>
                  </div>
                  <div class="col-6">
                   <!--content here-->
                  </div>
                </div>
                <div class="d-grid">
                   <!--content here-->
                </div>
              </div>
            </div>
          </div>
        </div> <!-- row -->


		</div>



<?php include"footer.php"; ?>
</body>
</html>
<script>
$(document).ready(function() {
    
   //get agents

    $.ajax({
       url: base +"agents",
	headers: { Authorization: localStorage.pass},
        method: "GET",
		dataType: 'json',
        success: function(response) {
            nums = response.data.rows_returned;
            $(".total_agents").html(ReplaceNumberWithCommas(nums));
            var percent = nums/100;
          	$(".apercentage").html(percent +"%");
            // for (var i = 0; i < nums; i++) {
            // var agent = '';
            // agent += '<option value=' + response.data.agents[i].id + '>'+ response.data.agents[i].agentid +'-'+ response.data.agents[i].name +'</option>';
            // $('.agents_data_g').append(agent);
            // }
			 //$('.agents_data_g').select2({
		  //  placeholder: "Select Agent",
    //         allowClear: true,
    //         width: "100%",	     
    //   dropdownParent: $("#addGroup")
    //     });
        },
        error: function(xhr, status, error){
        if (xhr.status == '401') {
        //authchecker(getAgents)
        }
        }
    })

    
    
	function ReplaceNumberWithCommas(groupagg) {
      //Seperates the components of the number
      var n= groupagg.toString().split(".");
      //Comma-fies the first part
      n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return n.join(".");
  }
//edit agent
async function getgroups(){
	$.ajax({
	url:base +"groups",
	method: "GET",
	dataType: "json",
	dataSrc:"data",
	headers: { Authorization: localStorage.pass},
	success: function (response) {
  var num_rows = response.data.rows_returned;
	$(".group_total").html(ReplaceNumberWithCommas(num_rows));
	var percent = num_rows/100;
	$(".gpercentage").html(percent +"%");
 },
error: function(xhr, status, error) {
 //   console.log(xhr.status)
if (xhr.status == '401') {
authchecker(getgroups)
}
}
});
}
getgroups();

});
</script>