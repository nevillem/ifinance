$(document).ready(function () {
          getDashboard()
        async function getDashboard(){
         $.ajax({
        url: base+"dashboard-manager",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
     },
        type : "GET",
        success: function(response) {
          console.log(response)
            $("#savings").html(numberWithCommas(response.data.totaldeposit[0].totaldeposit));
            $("#withdraws").html(numberWithCommas(response.data.totalwithdraw[0].totalwithdraw));
            $("#members").html(response.data.accounts[0].members);
            $("#groups").html(response.data.accounts[0].groups);
            $("#totalaccounts").html(response.data.accounts[0].accounts);
            $("#sms").html(response.data.totalsms);
            $("#activity").html(response.data.totalactivity);
            $("#share_balance").html("UGX "+numberWithCommas(response.data.shares[0]));
            $("#daily_withdraw_balance").html("UGX "+numberWithCommas(response.data.onewithdraws[0]));
            $("#daily_deposit_balance").html("UGX "+numberWithCommas(response.data.onedeposits[0]));
            $("#totalloans").html("UGX "+numberWithCommas(response.data.totalloans[0].totalloans));
            $("#totalloanamount").html(numberWithCommas(response.data.totalloans[0].count));
            $("#pendingloans").html(numberWithCommas(response.data.pendingtotalloans[0].totalcount));
            $("#pendingloansamount").html("UGX "+numberWithCommas(response.data.pendingtotalloans[0].totalamount));
            $("#activeloans").html(numberWithCommas(response.data.activetotalloans[0].totalcount));
            $("#activeloansamount").html("UGX "+numberWithCommas(response.data.activetotalloans[0].totalamount));
            $("#overdueloans").html(numberWithCommas(response.data.overduetotalloans[0].totalcount));
            $("#overdueloansamount").html("UGX "+numberWithCommas(response.data.overduetotalloans[0].totalamount));
            $("#totalloans").html(numberWithCommas(response.data.totalloans[0].totalloans));
            $("#percantageloans").html(numberWithCommas(response.data.totalloans[0].count));
            $("#pendingloans").html(numberWithCommas(response.data.totalloanapp[0].count));
            $("#percantagepending").html(numberWithCommas(response.data.totalloanapp[0].count));
            $("#activeLoans").html(numberWithCommas(response.data.activeloans[0].activeloans));
            $("#totalaccounts").html(numberWithCommas(response.data.activeloans[0].count));
            $("#overdueLoans").html("UGX "+numberWithCommas(response.data.overtotalloanapp[0].totalapp));
           
            let total  = Number(response.data.totaldeposit[0].totaldeposit)  + Number(response.data.totalwithdraw[0].totalwithdraw);
             $("#percantagesavings").html(parseFloat((response.data.totaldeposit[0].totaldeposit/total)*100).toFixed(1));
             $("#percantagewithdraws").html(parseFloat((response.data.totalwithdraw[0].totalwithdraw/total)*100).toFixed(1));
            //  console.log(total)
            let daily = Number(response.data.onedeposits[0]) + Number(response.data.onewithdraws[0]);
            let daily_balance = response.data.onedeposits[0];
            let withdraw_balance = response.data.onewithdraws[0];
            var savingData=[];
            for(var i=0; i< response.data.deposits.length; i++ ){
              savingData.push(response.data.deposits[i]);
            }
            var withdrawData=[];
            for(var i=0; i<response.data.deposits.length; i++ ){
              withdrawData.push(response.data.withdraws[i]);
            }
            var xData=[];
            for(var i=0; i<response.data.months.length; i++ ){
              /* An array of months. */
              xData.push(response.data.months[i]);
            }
            // console.log(savingData);
      // var xValues = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","OCt","Nov","Dec"];
      var xValues = xData;
      var lineDiv = $("#lineChart");
      var newChart = new Chart(lineDiv, {
        type: "line",
        data: {
        labels: xValues,
          datasets: [
          { 
            label: 'Withdraws',
            data:withdrawData,
            borderColor: "red",
            fill: false
          }, { 
            label: 'Savings',
            data:savingData,
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
              text: 'MEMBERS BY GENDER'
          },
      responsive: true,
      maintainAspectRatio: false,
          }
      });    
      
      }
        // daily deposits
    var PIECHART = $('#pieChartHomeDaily');
    var myPieChart = new Chart(PIECHART, {
        type: 'doughnut',
        options: {
            cutoutPercentage: 90,
            legend: {
                display: false
            }
        },
        data: {
            labels: [
                "Total Transactions",
                "Total Deposits"
            ],
            datasets: [{
                data: [daily,daily_balance],
                borderWidth: [0, 0],
                backgroundColor: [
                    "#73b41a",
                    "#F9C404",
                ],
                hoverBackgroundColor: [
                    "#F9C404",
                    "#73b41a",
                ]
            }]
        }
    });
    // the donut chart
    var PIECHART = $('#pieChartHomeWith');
    var myPieChart = new Chart(PIECHART, {
        type: 'doughnut',
        options: {
            cutoutPercentage: 90,
            legend: {
                display: false
            }
        },
        data: {
            labels: [
                "Total Transactions",
                "Total Withdraws"
            ],
            datasets: [{
                data: [daily, withdraw_balance],
                borderWidth: [0, 0],
                backgroundColor: [
                    "#A70000",
                    "#F9C404",
                ],
                hoverBackgroundColor: [
                    "#F9C404",
                    "#454545",
                ]
            }]
        }
    });
    var xNData=[];
    for(var i=0; i<response.data.monthsincome.length; i++ ){
      /* An array of months. */
      xNData.push(response.data.monthsincome[i]);
    }
    var xy1Data=[];
    for(var i=0; i<response.data.incomes.length; i++ ){
      /* An array of months. */
      xy1Data.push(response.data.incomes[i]);
    }
    var xy2Data=[];
    for(var i=0; i<response.data.expenses.length; i++ ){
      /* An array of months. */
      xy2Data.push(response.data.expenses[i]);
    }
var xValu
var xValues = xNData;
// var yValues = [55, 49, 44, 24, 15];

new Chart("income-expense", {
  type: "bar",
  data: {
    labels: xValues,
    datasets: [{
      backgroundColor: "#F9C404",
      data: xy1Data
    },
    {
      backgroundColor: "#73b41a",
      data: xy2Data
    }
  ]
  },
  options: {
    legend: {display: false},
    title: {
      display: true,
      /* The title of the chart. */
      // text: "World Wine Production 2018"
    }
  }
});
      // recent transactions
        for (var i = 0; i < response.data.recentdeposits.length; i++) {
          var recentdeposits = '';
          recentdeposits += '<div class="d-flex justify-content-between align-items-start align-items-sm-center mb-4 flex-column flex-sm-row">';
          recentdeposits += '<div class="left d-flex align-items-center">';
          recentdeposits += '<div class="icon icon-lg shadow mr-3 text-gray"><i class="	fab fa-creative-commons"></i></div>';
          recentdeposits += '<div class="text">';
          recentdeposits += '<h6 class="mb-0 d-flex align-items-center"> <span>'+response.data.recentdeposits[i].account+'</span><span class="dot dot-sm ml-2 bg-green"></span></h6><small class="text-gray">'+response.data.recentdeposits[i].time+'</small>';
          recentdeposits += '</div>';
          recentdeposits += '</div>';
          recentdeposits += '<div class="right ml-5 ml-sm-0 pl-3 pl-sm-0 text-green">';
          recentdeposits += '<p>+ UGX '+numberWithCommas(response.data.recentdeposits[i].amount)+'</p>';
          recentdeposits += '</div>';
          recentdeposits += '</div>';         
          $('#daily_deposits_dom').append(recentdeposits);
        }
        for (var i = 0; i < response.data.recentwithdraws.length; i++) {
          var recentwithdraws = '';
          recentwithdraws += '<div class="d-flex justify-content-between align-items-start align-items-sm-center mb-4 flex-column flex-sm-row">';
          recentwithdraws += '<div class="left d-flex align-items-center">';
          recentwithdraws += '<div class="icon icon-lg shadow mr-3 text-gray"><i class="	fab fa-creative-commons-nc"></i></div>';
          recentwithdraws += '<div class="text">';
          recentwithdraws += '<h6 class="mb-0 d-flex align-items-center"> <span>'+response.data.recentwithdraws[i].account+'</span><span class="dot dot-sm ml-2 bg-red"></span></h6><small class="text-gray">'+response.data.recentwithdraws[i].time+'</small>';
          recentwithdraws += '</div>';
          recentwithdraws += '</div>';
          recentwithdraws += '<div class="right ml-5 ml-sm-0 pl-3 pl-sm-0 text-red">';
          recentwithdraws += '<p>- UGX '+numberWithCommas(response.data.recentwithdraws[i].amount)+'</p>';
          recentwithdraws += '</div>';
          recentwithdraws += '</div>';         
          $('#daily_withdraws_dom').append(recentwithdraws);
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