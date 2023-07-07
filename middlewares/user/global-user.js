// setInterval(function(){location.reload(true);}, 10000);

                // register it
                window.addEventListener('load', () => {
                    registerSW();
                  });

                  // Register the Service Worker
                  async function registerSW() {
                    if ('serviceWorker' in navigator) {
                      try {
                        await navigator
                              .serviceWorker
                              .register('serviceworker.js');
                      }
                      catch (e) {
                        console.log('SW registration failed');
                      }
                    }
                  }
       // Global Functions
        $("body").children().first().before($(".modal"));

        // var base = "https://api.test.irembofinance.com/";
        var base = "http://127.1.1.1/irembo/irembonew/updatedapis/";
         $(document).ajaxStart(function () {
                        $(".login").css("background-color", "#73b41a");
                        $(".loading").show();
                        $(".login").hide();
                        NProgress.start();
                        NProgress.configure({ easing: 'ease', speed: 2000, showSpinner: false });

                });
        $(document).ajaxStop(function () {
                        $(".login").css("background-color", "#73b41a");
                        $(".loading").hide();
                        $(".login").show();
                        NProgress.done();
                });

                // NProgress.configure({ easing: 'ease', speed: 5000 });
                // NProgress.set(0.3);     // Sorta same as .start()
                // NProgress.set(0.6);
                // NProgress.set(1.0);     // Sorta same as .done()

     $.fn.serializeObject = function(){
          var o = {};
          var a = this.serializeArray();
          $.each(a, function() {
              if (o[this.name] !== undefined) {
                  if (!o[this.name].push) {
                      o[this.name] = [o[this.name]];
                  }
                  o[this.name].push(this.value || '');
              } else {
                  o[this.name] = this.value || '';
              }
          });
          return o;
        };
        function sweetalert(icon, message){

          const Toast = Swal.mixin({
              toast: true,
              position: 'bottom',
              showConfirmButton: false,
              timer: 1500,
              timerProgressBar: true,
              didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
              }
              })
              Toast.fire({
              icon: icon,
              title: message,
            });
        }
        function authchecker(restartfunction){
                $.ajax({
                    url: base+"usersession/"+localStorage.id,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                 },
                    type : "PATCH",
                    dataType: 'json',
                    data: JSON.stringify({
                           "refresh_token": localStorage.refreshToken
                        }),
                    success: function(response){
                        localStorage.setItem("token", response.data.access_token);
                        localStorage.setItem("refreshToken", response.data.refresh_token);
                        restartfunction();
                    },
                    error: function(xhr,status,error){
                            localStorage.clear();
                            window.location.href="login";
                    }

                });
              }

        function numberWithCommas(x) {
          var oldamount = Number(x);
          var amount = oldamount.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",");

          return amount;
      }
//       $(function () {
//     $("#links > a").click(function(event) {
//         event.preventDefault(); //so the browser doesn't follow the link
//         var href = $(this).attr('href');
//         $("#content").load(this.href, function() {
//             //execute here after load completed
//             window.history.pushState("#", "#", href);

//         });
//     });
// })

// System for American Numbering
var th_val = ['', 'thousand', 'million', 'billion', 'trillion'];
// System for uncomment this line for Number of English
// var th_val = ['','thousand','million', 'milliard','billion'];

var dg_val = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
var tn_val = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
var tw_val = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
function toWordsconver(s) {
  s = s.toString();
    s = s.replace(/[\, ]/g, '');
    if (s != parseFloat(s))
        return 'not a number ';
    var x_val = s.indexOf('.');
    if (x_val == -1)
        x_val = s.length;
    if (x_val > 15)
        return 'too big';
    var n_val = s.split('');
    var str_val = '';
    var sk_val = 0;
    for (var i = 0; i < x_val; i++) {
        if ((x_val - i) % 3 == 2) {
            if (n_val[i] == '1') {
                str_val += tn_val[Number(n_val[i + 1])] + ' ';
                i++;
                sk_val = 1;
            } else if (n_val[i] != 0) {
                str_val += tw_val[n_val[i] - 2] + ' ';
                sk_val = 1;
            }
        } else if (n_val[i] != 0) {
            str_val += dg_val[n_val[i]] + ' ';
            if ((x_val - i) % 3 == 0)
                str_val += 'hundred ';
            sk_val = 1;
        }
        if ((x_val - i) % 3 == 1) {
            if (sk_val)
                str_val += th_val[(x_val - i - 1) / 3] + ' ';
            sk_val = 0;
        }
    }
    if (x_val != s.length) {
        var y_val = s.length;
        str_val += 'point ';
        for (var i = x_val + 1; i < y_val; i++)
            str_val += dg_val[n_val[i]] + ' ';
    }
    return str_val.replace(/\s+/g, ' ');
}

// if (localStorage.getItem("role") === "teller") {
//     $("#loans-bar").hide();
//     $("#loans-active-bar").hide();
//   }
$(document).ready(function () {
  $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
      localStorage.setItem('activeTab', $(e.target).attr('href'));
  });
  var activeTab = localStorage.getItem('activeTab');
  if(activeTab){
      $('#myTab a[href="' + activeTab + '"]').tab('show');
  }
});
