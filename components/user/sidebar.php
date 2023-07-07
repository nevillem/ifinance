<div id="sidebar" class="sidebar py-3">
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">MAIN</div>
  <ul class="sidebar-menu list-unstyled">

        <li id="links" class="sidebar-list-item"><a href="teller" class="sidebar-link text-muted
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
       <a href="#" data-toggle="collapse" data-target="#membersdata" aria-expanded="false" aria-controls="membersdata" class="membership sidebar-link text-muted
       <?php if($pagename === 'Accounts'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>">
        <i class="o-user-1 mr-3 text-gray"></i>
        <span>Members</span></a>

       <!-- <div id="pages" class="collapse"> -->
        <ul id="membersdata" class="sidebar-menu list-unstyled collapse border-left border-primary border-thick">
          <li class="sidebar-list-item "><a href="members" class="sidebar-link text-muted pl-lg-5 Individual">Individual</a></li>
          <li class="sidebar-list-item"><a href="groups" class="sidebar-link text-muted pl-lg-5">Groups</a></li>
        </ul>
          <!-- </div> -->
        </li>

        <li class="sidebar-list-item">
        <a href="#" data-toggle="collapse" data-target="#accounts" aria-expanded="false"
         aria-controls="accounts" class="sidebar-link text-muted  <?php if($pagename === 'MemberAccounts'): ?>
           <?php echo 'active'; ?>
           <?php endif; ?>">
          <i class="o-document-1 mr-3 text-gray"></i>
        <span>Member accounts</span></a>
        <!-- <div id="pages" class="collapse"> -->
          <ul class="sidebar-menu list-unstyled border-left border-primary border-thick collapse" id="accounts" >
            <li class="sidebar-list-item"><a href="attach" class="sidebar-link text-muted pl-lg-5">Individual</a></li>
            <!-- <li class="sidebar-list-item"><a href="complusory" class="sidebar-link text-muted pl-lg-5">Compulsory</a></li> -->
            <li class="sidebar-list-item"><a href="chkgroup" class="sidebar-link text-muted pl-lg-5">Groups</a></li>
          </ul>
        <!-- </div> -->
      </li>
        <!-- Next Of Kin -->
      <li class="sidebar-list-item"><a href="kin" class="sidebar-link text-muted <?php if ($pagename === 'NextOfKin') : ?>
      <?php echo 'active'; ?>
      <?php endif; ?>"><i class="o-user-1 mr-3 text-gray"></i><span>Next Of Kin</span></a></li>

      <li class="sidebar-list-item">
      <a href="#" data-toggle="collapse" data-target="#saving" aria-expanded="false"
       aria-controls="saving" class="sidebar-link text-muted  <?php if($pagename === 'Savings'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>">
        <i class="o-database-1 mr-3 text-gray"></i>
      <span>Savings</span></a>
      <!-- <div id="pages" class="collapse"> -->
        <ul class="sidebar-menu list-unstyled border-left border-primary border-thick collapse" id="saving" >
          <li class="sidebar-list-item"><a href="savings-all" class="sidebar-link text-muted pl-lg-5">Individual</a></li>
          <!-- <li class="sidebar-list-item"><a href="complusory" class="sidebar-link text-muted pl-lg-5">Compulsory</a></li> -->
          <li class="sidebar-list-item"><a href="savexgroup" class="sidebar-link text-muted pl-lg-5">Group</a></li>
          <li class="sidebar-list-item"><a href="fixed-all" class="sidebar-link text-muted pl-lg-5">Fixed Account</a></li>
        </ul>
      <!-- </div> -->
    </li>

    <li class="sidebar-list-item">
    <a href="#" data-toggle="collapse" data-target="#transfer" aria-expanded="false"
     aria-controls="transfer" class="sidebar-link text-muted  <?php if($pagename === 'Withdraws'): ?>
       <?php echo 'active'; ?>
       <?php endif; ?>">
      <i class="o-user-details-1 mr-3 text-gray"></i>
    <span>Tranfers</span></a>
    <!-- <div id="pages" class="collapse"> -->
      <ul class="sidebar-menu list-unstyled border-left border-primary border-thick collapse" id="transfer" >
        <li class="sidebar-list-item"><a href="clienttoclient" class="sidebar-link text-muted pl-lg-5">Client to Client</a></li>
        <li class="sidebar-list-item"><a href="withdraws-all" class="sidebar-link text-muted pl-lg-5">Cash Withdraw</a></li>
        <li class="sidebar-list-item"><a href="oclienttogroup" class="sidebar-link text-muted pl-lg-5">Client to Group</a></li>
        <li class="sidebar-list-item"><a href="#" class="sidebar-link text-muted pl-lg-5">Ledger Posting</a></li>
        <li class="sidebar-list-item"><a href="#" class="sidebar-link text-muted pl-lg-5">Bank to Bank</a></li>
      </ul>
    <!-- </div> -->
  </li>

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
          <li class="sidebar-list-item"><a href="shares" class="sidebar-link text-muted pl-lg-5">Shares</a></li>
        </ul>
      <!-- </div> -->
    </li>
  </ul>
  <div class="text-gray-400 text-uppercase px-3 px-lg-4 py-4 font-weight-bold small headings-font-family">EXTRAS</div>
  <ul class="sidebar-menu list-unstyled">
        <li class="sidebar-list-item"><a href="upassword" class="sidebar-link text-muted  <?php if($pagename === 'Password'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-database-1 mr-3 text-gray"></i><span>Password And Pin</span></a></li>
        <li class="sidebar-list-item"><a href="ureports" class="sidebar-link text-muted <?php if($pagename === 'Reports'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-paperwork-1 mr-3 text-gray"></i><span>My Reports</span></a></li>
        <li class="sidebar-list-item"><a href="uactivity" class="sidebar-link text-muted <?php if($pagename === 'Activity'): ?>
         <?php echo 'active'; ?>
         <?php endif; ?>"><i class="o-wireframe-1 mr-3 text-gray"></i><span>Activity Log</span></a></li>
  </ul>
</div>
