<div id="sidebar" class="sidebar py-3">
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">MAIN</div>
  <ul class="sidebar-menu list-unstyled">

        <li id="links" class="sidebar-list-item"><a href="manager" class="sidebar-link text-muted
        <?php if($pagename === 'Dashboard'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>
         "><i class="o-home-1 mr-3 text-gray"></i><span>Dashboard</span></a></li>

         <li class="sidebar-list-item">
        <a href="#" data-toggle="collapse" data-target="#membersdata" aria-expanded="false" aria-controls="membersdata" class="membership sidebar-link text-muted
        <?php if($pagename === 'Accounts'): ?>
          <?php echo 'active'; ?>
          <?php endif; ?>">
         <i class="o-user-1 mr-3 text-gray"></i>
         <span>Members</span></a>

        <!-- <div id="pages" class="collapse"> -->
         <ul id="membersdata" class="sidebar-menu list-unstyled collapse border-left border-primary border-thick">
           <li class="sidebar-list-item "><a href="manmembers" class="sidebar-link text-muted pl-lg-5 Individual">Individual</a></li>
           <li class="sidebar-list-item"><a href="mangroups" class="sidebar-link text-muted pl-lg-5">Groups</a></li>
         </ul>
           <!-- </div> -->
         </li>

        <li class="sidebar-list-item"><a href="savings-manager" class="sidebar-link text-muted <?php if($pagename === 'Savings'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-database-1 mr-3 text-gray"></i><span>Savings</span></a></li>
        <li class="sidebar-list-item"><a href="withdraws-manager" class="sidebar-link text-muted <?php if($pagename === 'Withdraws'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-user-details-1 mr-3 text-gray"></i><span>Withdraws</span></a></li>
         <li class="sidebar-list-item" id="loans-bar"><a href="loans-application-manager" class="sidebar-link text-muted <?php if($pagename === 'Loans'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-repository-1 mr-3 text-gray"></i><span>Loans Applications</span></a></li>
        <li class="sidebar-list-item" id="loans-active-bar"><a href="loans-active-manager" class="sidebar-link text-muted <?php if($pagename === 'Loans-Active'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-statistic-1 mr-3 text-gray"></i><span>Active Loans</span></a></li>


    <li class="sidebar-list-item">
    <a href="#" data-toggle="collapse" data-target="#pages" aria-expanded="false" aria-controls="pages" class="sidebar-link text-muted  <?php if($pagename === 'Settings'): ?>
       <?php echo 'active'; ?>
       <?php endif; ?>">
      <i class="o-settings-window-1 mr-3 text-gray"></i>
    <span>Other Transactions</span></a>
    <!-- <div id="pages" class="collapse"> -->
      <ul class="sidebar-menu list-unstyled border-left border-primary border-thick collapse" id="pages" >
        <!-- <li class="sidebar-list-item"><a href="fixed-all" class="sidebar-link text-muted pl-lg-5">Fixed</a></li> -->
        <!-- <li class="sidebar-list-item"><a href="complusory" class="sidebar-link text-muted pl-lg-5">Compulsory</a></li> -->
        <li class="sidebar-list-item"><a href="allsharesmanager" class="sidebar-link text-muted pl-lg-5">Shares</a></li>
      </ul>
    <!-- </div> -->
  </li>

    <li class="sidebar-list-item">
      <a href="#" data-toggle="collapse" data-target="#expensesmanager" aria-expanded="false" aria-controls="pages" class="sidebar-link text-muted  <?php if($pagename === 'Income'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>">
      <i class="o-clock-1 mr-3 text-gray"></i>
      <span>Income and Expenses</span></a>
      <div id="expensesmanager" class="collapse">
        <ul class="sidebar-menu list-unstyled border-left border-primary border-thick">
          <li class="sidebar-list-item"><a href="incomes" class="sidebar-link text-muted pl-lg-5">Income</a></li>
          <!-- <li class="sidebar-list-item"><a href="complusory" class="sidebar-link text-muted pl-lg-5">Compulsory</a></li> -->
          <li class="sidebar-list-item"><a href="expenses" class="sidebar-link text-muted pl-lg-5">Expenses</a></li>
        </ul>
      </div>
    </li>
  </ul>
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">EXTRAS</div>
  <ul class="sidebar-menu list-unstyled">
        <li class="sidebar-list-item"><a href="password-manager" class="sidebar-link text-muted  <?php if($pagename === 'Password'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-database-1 mr-3 text-gray"></i><span>Password And Pin</span></a></li>
        <li class="sidebar-list-item"><a href="reports-manager" class="sidebar-link text-muted <?php if($pagename === 'Reports'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-paperwork-1 mr-3 text-gray"></i><span>My Reports</span></a></li>
        <li class="sidebar-list-item"><a href="activity-manager" class="sidebar-link text-muted <?php if($pagename === 'Activity'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-wireframe-1 mr-3 text-gray"></i><span>Activity Log</span></a></li>
  </ul>
</div>
