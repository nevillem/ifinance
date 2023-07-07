<div id="sidebar" class="sidebar py-3">
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">MAIN</div>
  <ul class="sidebar-menu list-unstyled">

        <li id="links" class="sidebar-list-item"><a href="loans" class="sidebar-link text-muted
        <?php if($pagename === 'Dashboard'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>
         "><i class="o-home-1 mr-3 text-gray"></i><span>Dashboard</span></a></li>

        <!--<li class="sidebar-list-item"><a href="members" class="sidebar-link text-muted -->
        <!--<?php if($pagename === 'Accounts'): ?>-->
        <!-- <?php echo 'active'; ?>-->
        <!-- <?php endif; ?>">-->
        <!--<i class="o-user-1 mr-3 text-gray "></i><span>Members <br>Accounts</span></a></li>-->


    <li class="sidebar-list-item">
    <a href="#" data-toggle="collapse" data-target="#loanspage" aria-expanded="false"
        aria-controls="loanspage" class="loansmenu sidebar-link text-muted
       <?php if($pagename === 'Loans'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>">
        <i class="o-wireframe-1 mr-3 text-gray"></i>
        <span>Loans</span></a>
    
    <!-- <div id="pages" class="collapse"> -->
      <ul class="sidebar-menu list-unstyled collapse border-left border-primary border-thick" id="loanspage" >
        <li class="sidebar-list-item"><a href="application" class="sidebar-link text-muted pl-lg-5 application">Loan Application</a></li>
        <li class="sidebar-list-item"><a href="xgroup" class="sidebar-link text-muted pl-lg-5">Group Application</a></li>
        <!--<li class="sidebar-list-item" id="links"><a href="loanguarantors" class="sidebar-link text-muted pl-lg-5">Loan Guarantors </a></li>-->
        <li class="sidebar-list-item" id="links"><a href="collaterals" class="sidebar-link text-muted pl-lg-5">Collaterals</a></li>
        <li class="sidebar-list-item"><a href="processloaan" class="sidebar-link text-muted pl-lg-5">Process Loans</a></li>
        <li class="sidebar-list-item" id="links"><a href="approvelons" class="sidebar-link text-muted pl-lg-5">Approve Loans</a></li>
        <li class="sidebar-list-item" id="links"><a href="disburseloan" class="sidebar-link text-muted pl-lg-5">Disburse Loans</a></li>
        <li class="sidebar-list-item" id="links"><a href="loancalculator" class="sidebar-link text-muted pl-lg-5">Loan Calculator</a></li>
        <li class="sidebar-list-item" id="links"><a href="cancelloanapp" class="sidebar-link text-muted pl-lg-5">Cancel Loan</a></li>
      </ul>
    <!-- </div> -->
  </li>
  
  </ul>
</div>
