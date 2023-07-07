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
          <li class="sidebar-list-item "><a href="members-man.ager" class="sidebar-link text-muted pl-lg-5 Individual">Individual</a></li>
          <!--<li class="sidebar-list-item"><a href="groups" class="sidebar-link text-muted pl-lg-5">Groups</a></li>-->
        </ul>
          <!-- </div> -->
        </li>

       

<!-- member savings -->
<li class="sidebar-list-item">
  <a href="#" data-toggle="collapse" data-target="#savingpages" aria-expanded="false" aria-controls="pages" class="sidebar-link text-muted  <?php if($pagename === 'Savings'): ?>
    <?php echo 'active'; ?>
    <?php endif; ?>">
    <i class="o-database-1 mr-3 text-gray"></i>
    <span>Savings</span></a>
    <div id="savingpages" class="collapse">
      <ul class="sidebar-menu list-unstyled border-left border-primary border-thick">
        <li class="sidebar-list-item" id="links"><a href="savings-man.ager" class="sidebar-link text-muted pl-lg-5">Individual Savings</a></li>
        <li class="sidebar-list-item" id="links"><a href="man.ager-savexgroup" class="sidebar-link text-muted pl-lg-5">Group Savings</a></li>
        <li class="sidebar-list-item" id="links"><a href="#man.ager-fixedsavings" class="sidebar-link text-muted pl-lg-5">Fixed Savings</a></li>
      </ul>
    </div>
  </li>
  <!-- member savings end-->

           <!-- fund transfer -->
<li class="sidebar-list-item">
  <a href="#" data-toggle="collapse" data-target="#transferpages" aria-expanded="false" aria-controls="pages" class="sidebar-link text-muted  <?php if($pagename === 'Withdraws'): ?>
    <?php echo 'active'; ?>
    <?php endif; ?>">
    <i class="o-data-storage-1 mr-3 text-gray"></i>
    <span>Transfers</span></a>
    <div id="transferpages" class="collapse">
      <ul class="sidebar-menu list-unstyled border-left border-primary border-thick">
        <!-- <li class="sidebar-list-item" id="links"><a href="clienttransfer" class="sidebar-link text-muted pl-lg-5">Client To Client</a></li> -->
        <li class="sidebar-list-item" id="links"><a href="withdraws-man.ager" class="sidebar-link text-muted pl-lg-5">Cash Withdraw</a></li>
        <!-- <li class="sidebar-list-item" id="links"><a href="clientgrouptransfer" class="sidebar-link text-muted pl-lg-5">Client To Group</a></li> -->
        <!-- <li class="sidebar-list-item" id="links"><a href="ledgerposting" class="sidebar-link text-muted pl-lg-5">Ledger Posting</a></li> -->
        <!-- <li class="sidebar-list-item" id="links"><a href="banktobank" class="sidebar-link text-muted pl-lg-5">Bank To Bank</a></li> -->
      </ul>
    </div>
  </li>
  <!-- fund transfer end-->

 <!--income and expenditure -->
<li class="sidebar-list-item">
  <a href="#" data-toggle="collapse" data-target="#expensepages" aria-expanded="false" aria-controls="pages" class="sidebar-link text-muted  <?php if($pagename === 'Income'): ?>
    <?php echo 'active'; ?>
    <?php endif; ?>">
    <i class="o-wireframe-1 mr-3 text-gray"></i>
    <span>Income & <br> Expenses</span></a>
    <div id="expensepages" class="collapse">
      <ul class="sidebar-menu list-unstyled border-left border-primary border-thick">
        <li class="sidebar-list-item" id="links"><a href="man.agervendors" class="sidebar-link text-muted pl-lg-5">Vendors </a></li>
        <li class="sidebar-list-item" id="links"><a href="man.gerpaybills" class="sidebar-link text-muted pl-lg-5">Pay Bills </a></li>
        <li class="sidebar-list-item" id="links"><a href="man.gerincome" class="sidebar-link text-muted pl-lg-5">Income </a></li>
        <!--<li class="sidebar-list-item" id="links"><a href="mangerbudgeting" class="sidebar-link text-muted pl-lg-5">Budgeting</a></li>-->
        <!--<li class="sidebar-list-item" id="links"><a href="actual_budget" class="sidebar-link text-muted pl-lg-5">Actual Vs.Budget</a></li>-->
      </ul>
    </div>
  </li>
  <!--income and expenditure end-->



 
  </ul>
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">EXTRAS</div>
  <ul class="sidebar-menu list-unstyled">
        <li class="sidebar-list-item"><a href="password-man.ager" class="sidebar-link text-muted  <?php if($pagename === 'Password'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-database-1 mr-3 text-gray"></i><span>Password And Pin</span></a></li>
        <li class="sidebar-list-item"><a href="reports-man.ager" class="sidebar-link text-muted <?php if($pagename === 'Reports'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-paperwork-1 mr-3 text-gray"></i><span>My Reports</span></a></li>
        <li class="sidebar-list-item"><a href="activity-man.ager" class="sidebar-link text-muted <?php if($pagename === 'Activity'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-wireframe-1 mr-3 text-gray"></i><span>Activity Log</span></a></li>
  </ul>
</div>
