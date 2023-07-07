<div id="sidebar" class="sidebar py-3">
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">MAIN</div>
  <ul class="sidebar-menu list-unstyled">
   
        <li id="links" class="sidebar-list-item" id="links"><a href="adminall" class="sidebar-link text-muted
        <?php if($pagename === 'Admin-Dashboard'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>
         "><i class="o-home-1 mr-3 text-gray"></i><span>Dashboard</span></a></li>
    
        <li class="sidebar-list-item" id="links"><a href="saccoxcreate" class="sidebar-link text-muted 
        <?php if($pagename === 'saccos-all'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>
        "><i class="o-database-1 mr-3 text-gray"></i><span>Create Sacco</span></a></li>

<!--statements -->
<!-- <li class="sidebar-list-item">
  <a href="#" data-toggle="collapse" data-target="#statementpages" aria-expanded="false" aria-controls="pages" class="sidebar-link text-muted  <?php if($pagename === 'Loans'): ?>
    <?php echo 'active'; ?>
    <?php endif; ?>">
    <i class="o-wireframe-1 mr-3 text-gray"></i>
    <span>Loan Reports</span></a>
    <div id="statementpages" class="collapse">
      <ul class="sidebar-menu list-unstyled border-left border-primary border-thick">
        <li class="sidebar-list-item" id="links"><a href="accountstatement" class="sidebar-link text-muted pl-lg-5">Account Statement</a></li>
        <li class="sidebar-list-item" id="links"><a href="generalstatement" class="sidebar-link text-muted pl-lg-5">General Statement </a></li>
        <li class="sidebar-list-item" id="links"><a href="groupstatusreport" class="sidebar-link text-muted pl-lg-5">Group Status Report </a></li>
        <li class="sidebar-list-item" id="links"><a href="accountbalance" class="sidebar-link text-muted pl-lg-5">Account Balances</a></li>
        <li class="sidebar-list-item" id="links"><a href="profit_loss_statement" class="sidebar-link text-muted pl-lg-5">Profit & Loss Statement</a></li>
        <li class="sidebar-list-item" id="links"><a href="generalledger" class="sidebar-link text-muted pl-lg-5">General Ledger</a></li>
        <li class="sidebar-list-item" id="links"><a href="trialbalance" class="sidebar-link text-muted pl-lg-5">Trial Balance</a></li>
        <li class="sidebar-list-item" id="links"><a href="cashbook" class="sidebar-link text-muted pl-lg-5">Cash Book</a></li>
        <li class="sidebar-list-item" id="links"><a href="balancesheet" class="sidebar-link text-muted pl-lg-5">Balance Sheet</a></li>
        <li class="sidebar-list-item" id="links"><a href="creditscoring" class="sidebar-link text-muted pl-lg-5">Credit Scoring</a></li>
      </ul>
    </div>
  </li> -->
  <!--statements end-->

  </ul>
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">EXTRAS</div>
  <ul class="sidebar-menu list-unstyled">
        <li class="sidebar-list-item" id="links"><a href="#" class="sidebar-link text-muted  <?php if($pagename === 'Password'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-database-1 mr-3 text-gray"></i><span>Password</span></a></li>
       
  </ul>
</div>
