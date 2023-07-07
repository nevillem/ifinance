$(document).ready(function () {
          getDashboard()
        async function getDashboard(){
         $.ajax({
        url: base+"dashboard-loans",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
     },
        type : "GET",
        success: function(response) {
          // console.log(response)
            $("#totalloans").html(numberWithCommas(response.data.totalloans[0].totalloans));
            $("#percantageloans").html(numberWithCommas(response.data.totalloans[0].count));
            $("#pendingloans").html(numberWithCommas(response.data.totalloanapp[0].totalapp));
            $("#percantagepending").html(numberWithCommas(response.data.totalloanapp[0].count));
            $("#activeLoans").html(numberWithCommas(response.data.activeloans[0].activeloans));
            $("#totalaccounts").html(numberWithCommas(response.data.activeloans[0].count));
            $("#sms").html(response.data.totalsms);
            $("#activity").html(response.data.totalactivity);
            $("#overdueLoans").html("UGX "+numberWithCommas(response.data.overtotalloanapp[0].totalapp));
            var ActiveData=[];
            for(var i=0; i< response.data.loans.length; i++ ){
              ActiveData.push(response.data.loans[i]);
            }
            var pendingData=[];
            for(var i=0; i<response.data.loan_app.length; i++ ){
              pendingData.push(response.data.loan_app[i]);
            }
            // console.log(savingData);
            var xData=[];
            for(var i=0; i<response.data.months.length; i++ ){
              /* An array of months. */
              xData.push(response.data.months[i]);
            }
      // var xValues = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","OCt","Nov","Dec"];
      var xValues = xData;
      var lineDiv = $("#lineChart");
      var newChart = new Chart(lineDiv, {
        type: "line",
        data: {
        labels: xValues,
          datasets: [
          { 
            label: 'Pending Loans',
            data:pendingData,
            borderColor: "blue",
            fill: false
          }, { 
            label: 'Active Loans',
            data:ActiveData,
            borderColor: "green",
            fill: false
          }
          ]
        },
        options: {
          legend: {display: true},
          responsive: true,
          maintainAspectRatio: false,
        }
      });

      var m=response.data.gender[0].male;
      var f=response.data.gender[0].female;
      chartData(m, f)
      function chartData(m, f){
      var chartDiv = $("#barChart");
      var myChart = new Chart(chartDiv, {
      type: 'pie',
      data: {
          labels: ["Male", "Female"],
          datasets: [
          {
              data: [m,f],
              backgroundColor: [
              "#FFC107", "#0D6EFD"
              ]
          }]
      },
      options: {
          title: {
              display: true,
              text: 'Loans By Gender'
          },
      responsive: true,
      maintainAspectRatio: false,
          }
      });    
      
      }

          },
          error: function(xhr, status, error){
            if(xhr.status == '401'){
              authchecker(getDashboard)
            }
          }
    })
  }
});