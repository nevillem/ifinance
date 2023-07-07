
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
        var base = "https://api.v4.irembofinance.com/";
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
                    url: base+"generaladmin/"+localStorage.id,
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
                    error: function(){
                        localStorage.clear();
                        window.location.href="admin";
                    }
          
                });
              }
        
        function numberWithCommas(x) {
          var oldamount = Number(x); 
          var amount = oldamount.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",");
          
          return amount;
      }  
// $(function () {
//     $("#links > a").click(function(event) {
//         event.preventDefault(); //so the browser doesn't follow the link
//         var href = $(this).attr('href');
//         $("#content").load(this.href, function() {
//             //execute here after load completed
//             window.history.pushState("#", "#", href);
               
//         });
//     });
// })