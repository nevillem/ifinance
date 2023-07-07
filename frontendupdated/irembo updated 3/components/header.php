<header class="header sticky-top">
  <nav class="navbar navbar-expand-lg px-4 py-2 bg-white shadow">
    <a href="#" class="sidebar-toggler text-gray-500 mr-4 mr-lg-5 lead">
      <i class="fas fa-align-left"></i></a>
      <a href="dashboard" class="navbar-brand font-weight-bold text-uppercase text-base">
         <img class="img-fluid" style="max-height:8rem;" src="assets/img/logo.png" alt="Irembo Finance">
      </a>
    <ul class="ml-auto d-flex align-items-center list-unstyled mb-0">
      <li class="nav-item">
        <div class="d-none d-lg-block">
          <div class="position-relative mb-0">
            <p  class="p-2 mt-3 mr-5">USSD code: <span id="saccocode"></span> </p>
          </div>
      </div>
      </li>
      <li class="nav-item dropdown mr-3"><a id="notifications" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle text-gray-400 px-1"><i class="fa fa-bell"></i><span class="notification-icon"></span></a>
              <div aria-labelledby="notifications" class="dropdown-menu"><a href="#" class="dropdown-item">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-sm bg-violet text-white"><i class="fas fa-money-bill-alt"></i></div>
                    <div class="text ml-2">
                      <p class="mb-0">Savings (0)</p>
                    </div>
                  </div></a><a href="#" class="dropdown-item">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-sm bg-green text-white"><i class="fas fa-envelope"></i></div>
                    <div class="text ml-2">
                      <p class="mb-0">Message (0)</p>
                    </div>
                  </div></a><a href="#" class="dropdown-item">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-sm bg-blue text-white"><i class="fas fa-upload"></i></div>
                    <div class="text ml-2">
                      <p class="mb-0">Member (0)</p>
                    </div>
                  </div></a><a href="#" class="dropdown-item">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-sm bg-danger text-white"><i class="fas fa-money-check"></i></div>
                    <div class="text ml-2">
                      <p class="mb-0">Withdraws (0)</p>
                    </div>
                  </div></a>
                <div class="dropdown-divider"></div><a href="#" class="dropdown-item text-center"><small class="font-weight-bold headings-font-family text-uppercase">Notifications(0)</small></a>
              </div>
            </li>
      <li class="nav-item dropdown ml-auto">
        <a id="userInfo" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">
        <img src="assets/img/favicon.png" alt="John Doe" style="max-width: 2.5rem;" class="img-fluid rounded-circle shadow"></a>
        <div aria-labelledby="userInfo" class="dropdown-menu">
          <a href="#" class="dropdown-item">
          <strong class="d-block text-uppercase headings-font-family" id="sacconame"></strong>
          <small id="saccoemail"></small></a>
          <div class="dropdown-divider"></div><a href="sacco" class="dropdown-item">Settings</a>
          <a href="activity" class="dropdown-item">Activity log</a>
          <div class="dropdown-divider"></div>
          <a href="#" id="logout" class="dropdown-item">Logout</a>
        </div>
      </li>
    </ul>
  </nav>
</header>
