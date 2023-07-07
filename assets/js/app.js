(function($) {
  'use strict';
  $(function() {
    var body = $('body');
    var sidebar = $('.sidebar');
    var sidebarlink = $('.sidebar-link');

    //Add active class to nav-link based on url dynamically
    function addActiveClass(element) {
        if (current === "") {
          //for root url
          if ((element.attr('href').indexOf("dashboard-teller.php") !== -1)
          ||(element.attr('href').indexOf("dashboard-manager.php") !== -1)
          ||(element.attr('href').indexOf("dashboard-loans.php") !== -1)) {
            element.parents('.sidebar-list-item').last().addClass('active');
            if (element.parents('.sidebar-menu').length) {
              element.closest('.collapse').addClass('show');
              element.addClass('active');
            }
          }
        } else {
          //for other url
          if (element.attr('href').indexOf(current) !== -1) {
            element.parents('.sidebar-list-item').last().addClass('active');
            if (element.parents('.sidebar-menu').length) {
              element.closest('.collapse').addClass('show');
              element.addClass('active');
            }
            if (element.parents('.sidebar-list-item').length) {
              element.addClass('active');
            }
          }
        }
    }
    if(sidebarlink.hasClass('active')){
      // console.log("true");
      sidebarlink.removeAttr('aria-expanded');
      sidebarlink.attr("aria-expanded","true");
    }

    var current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');
    // console.log(current);
    if(current=="details"){
      $('.membership').addClass('active')
      $('#membersdata').addClass('collapse')
      $('#membersdata').addClass('show')
      $('.Individual').addClass('active')
      $('.loansmenu').addClass('active')
      $('#loanspage').addClass('collapse')
      $('#loanspage').addClass('show')
      $('.application').addClass('active')

      sidebarlink.removeAttr('aria-expanded');
      sidebarlink.attr("aria-expanded","true");
    }
    $('.sidebar-menu li a', sidebar).each(function() {
      var $this = $(this);
      addActiveClass($this);
    });

  });
})(jQuery);
