<div id="sidebar" class="sidebar py-3">
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">MAIN</div>
  <ul class="sidebar-menu list-unstyled">

        <li id="links" class="sidebar-list-item" id="links"><a href="dashboard" class="sidebar-link text-muted
        <?php if($pagename === 'Dashboard'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>
         "><i class="o-home-1 mr-3 text-gray"></i><span>Dashboard</span></a></li>

        <li class="sidebar-list-item" id="links"><a href="branches" class="sidebar-link text-muted
        <?php if($pagename === 'Branches'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>
        "><i class="o-database-1 mr-3 text-gray"></i><span>Branches</span></a></li>

        <li class="sidebar-list-item" id="links"><a href="staff" class="sidebar-link text-muted <?php if($pagename === 'Staff'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-user-1 mr-3 text-gray"></i><span>Staff</span></a></li>
        <li class="sidebar-list-item" id="links"><a href="members-all" class="sidebar-link text-muted <?php if($pagename === 'Members'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-user-details-1 mr-3 text-gray"></i><span>Members/Accounts</span></a></li>
    <li class="sidebar-list-item">
      <a href="#" data-toggle="collapse" data-target="#pages" aria-expanded="false" aria-controls="pages" class="sidebar-link text-muted  <?php if($pagename === 'Settings'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>">
        <i class="o-wireframe-1 mr-3 text-gray"></i>
      <span>Settings</span></a>
      <!-- <div id="pages" class="collapse"> -->
        <ul class="sidebar-menu list-unstyled border-left border-primary border-thick collapse" id="pages">
          <li class="sidebar-list-item" id="links"><a href="sacco" class="sidebar-link text-muted pl-lg-5">Business Profile</a></li>
          <li class="sidebar-list-item" id="links"><a href="defaults" class="sidebar-link text-muted pl-lg-5">System Defaults</a></li>
          <li class="sidebar-list-item" id="links"><a href="accounts" class="sidebar-link text-muted pl-lg-5">Accounts</a></li>
          <!--<li class="sidebar-list-item" id="links"><a href="#" class="sidebar-link text-muted pl-lg-5">Accounts</a></li>-->
          <li class="sidebar-list-item" id="links"><a href="withdrawsettings" class="sidebar-link text-muted pl-lg-5">Set Withdraw Funds</a></li>
          <li class="sidebar-list-item" id="links"><a href="fixeddepositsettings" class="sidebar-link text-muted pl-lg-5">Fixed Deposit Settings</a></li>
          <li class="sidebar-list-item" id="links"><a href="loanproducts" class="sidebar-link text-muted pl-lg-5">Loan Products</a></li>
          <li class="sidebar-list-item" id="links"><a href="paymentmethods" class="sidebar-link text-muted pl-lg-5">Payment Methods</a></li>
        </ul>
      <!-- </div> -->
    </li>
  </ul>
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">EXTRAS</div>
  <ul class="sidebar-menu list-unstyled">
        <li class="sidebar-list-item" id="links"><a href="password" class="sidebar-link text-muted  <?php if($pagename === 'Password'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-database-1 mr-3 text-gray"></i><span>Password</span></a></li>
        <li class="sidebar-list-item" id="links"><a href="reports" class="sidebar-link text-muted <?php if($pagename === 'Reports'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-paperwork-1 mr-3 text-gray"></i><span>Reports</span></a></li>
        <li class="sidebar-list-item" id="links"><a href="communication" class="sidebar-link text-muted <?php if($pagename === 'SMS'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-imac-screen-1 mr-3 text-gray"></i><span>Communcation</span></a></li>
        <li class="sidebar-list-item <?php if($pagename === 'Activity'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>" id="links"><a href="activity" class="sidebar-link text-muted"><i class="o-wireframe-1 mr-3 text-gray"></i><span>Activity Log</span></a></li>
  </ul>
</div>
