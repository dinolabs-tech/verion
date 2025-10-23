<!-- Sidebar -->
<div class="sidebar" data-background-color="dark2">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark2">
      <a href="dashboard.php" class="logo">
        <!-- <img
          src="assets/img/kaiadmin/logo_light.svg"
          alt="navbar brand"
          class="navbar-brand"
          height="20" /> -->
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="gg-menu-right"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="gg-more-vertical-alt"></i>
      </button>
    </div>
    <!-- End Logo Header -->
  </div>
  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">
        <li class="nav-item active">
          <a href="dashboard.php">
            <i class="fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <li class="nav-section">
          <span class="sidebar-mini-icon">
            <i class="fa fa-ellipsis-h"></i>
          </span>
          <h4 class="text-section">Admin</h4>
        </li>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
           <li class="nav-item">
            <a href="manage_users.php">
              <i class="fas fa-users"></i>
              <p>Manage Users</p>
            </a>
          </li>
           <li class="nav-item">
            <a href="manage_clients.php">
              <i class="fas fa-user-tie"></i>
              <p>Manage Clients</p>
            </a>
          </li>
           <li class="nav-item">
            <a href="manage_engagements.php">
              <i class="far fa-comments"></i>
              <p>Manage Engagements</p>
            </a>
          </li>

        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'Reviewer' || $_SESSION['role'] === 'Admin'): ?>
          <li class="nav-section">
            <span class="sidebar-mini-icon">
              <i class="fa fa-ellipsis-h"></i>
            </span>
            <h4 class="text-section">Reviewer</h4>
          </li>
          <li class="nav-item">
            <a href="engagements_for_review.php">
              <i class="fas fa-comments"></i>
              <p>Engagements for Review</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="open_queries.php">
              <i class="fas fa-comment-dots"></i>
              <p>My Queries</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] == 'Auditor' || $_SESSION['role'] == 'Admin'): ?>
          <li class="nav-section">
            <span class="sidebar-mini-icon">
              <i class="fa fa-ellipsis-h"></i>
            </span>
            <h4 class="text-section">Auditor</h4>
          </li>

          <li class="nav-item">
            <a href="my_engagements.php">
              <i class="fas fa-comments"></i>
              <p>My Engagements</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="open_queries.php">
              <i class="fas fa-comment-dots"></i>
              <p>Open Queries</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="view_reports.php">
              <i class="fas fa-file-alt"></i>
              <p>View Reports</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'Client'): ?>
           <li class="nav-item">
            <a href="client_engagements.php">
              <i class="fas fa-comments"></i>
              <p>My Engagements</p>
            </a>
          </li>
           <li class="nav-item">
            <a href="view_reports.php">
              <i class="fas fa-file-alt"></i>
              <p>View Reports</p>
            </a>
          </li>
           <li class="nav-item">
            <a href="open_queries.php">
              <i class="fas fa-comment-dots"></i>
              <p>My Queries</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] ==='Admin') {?>
        <li class="nav-item">
          <a href="documentation/index.php">
            <i class="fas fa-file-archive"></i>
            <p>Documentation</p>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
  </div>
</div>
<!-- End Sidebar -->
