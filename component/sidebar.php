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
          <!-- <div class="collapse" id="dashboard">
            <ul class="nav nav-collapse">
              <li>
                <a href="dashboard.php">
                  <span class="sub-item">Dashboard 1</span>
                </a>
              </li>
            </ul>
          </div> -->
        </li>
        <li class="nav-section">
          <span class="sidebar-mini-icon">
            <i class="fa fa-ellipsis-h"></i>
          </span>
          <h4 class="text-section"></h4>
        </li>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#Admin">
              <i class="fas fa-layer-group"></i>
              <p>Admin</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="Admin">
              <ul class="nav nav-collapse">
                <li>
                  <a href="register.php">
                    <span class="sub-item">Register User</span>
                  </a>
                </li>
                <li>
                  <a href="manage_users.php">
                    <span class="sub-item">Manage Users</span>
                  </a>
                </li>
                <li>
                  <a href="manage_clients.php">
                    <span class="sub-item">Manage Clients</span>
                  </a>
                </li>
                <li>
                  <a href="manage_engagements.php">
                    <span class="sub-item">Manage Engagements</span>
                  </a>
                </li>
                <!-- <li>
                  <a href="buttons.php">
                    <span class="sub-item">Buttons</span>
                  </a>
                </li>
                <li>
                  <a href="gridsystem.php">
                    <span class="sub-item">Grid System</span>
                  </a>
                </li>
                <li>
                  <a href="panels.php">
                    <span class="sub-item">Panels</span>
                  </a>
                </li>
                <li>
                  <a href="notifications.php">
                    <span class="sub-item">Notifications</span>
                  </a>
                </li>
                <li>
                  <a href="sweetalert.php">
                    <span class="sub-item">Sweet Alert</span>
                  </a>
                </li>
                <li>
                  <a href="font-awesome-icons.php">
                    <span class="sub-item">Font Awesome Icons</span>
                  </a>
                </li>
                <li>
                  <a href="simple-line-icons.php">
                    <span class="sub-item">Simple Line Icons</span>
                  </a>
                </li>
                <li>
                  <a href="typography.php">
                    <span class="sub-item">Typography</span>
                  </a>
                </li> -->
              </ul>
            </div>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'Reviewer' || $_SESSION['role'] === 'Admin'): ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#reviewer">
              <i class="fas fa-th-list"></i>
              <p>Reviewer</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="reviewer">
              <ul class="nav nav-collapse">
                <li>
                  <a href="engagements_for_review.php">
                    <span class="sub-item">Engagements for Review</span>
                  </a>
                </li>
                <li>
                  <a href="open_queries.php">
                    <span class="sub-item">My Queries</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] == 'Auditor' || $_SESSION['role'] == 'Admin'): ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#auditor">
              <i class="fas fa-pen-square"></i>
              <p>Auditor</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="auditor">
              <ul class="nav nav-collapse">
                <li>
                  <a href="my_engagements.php">
                    <span class="sub-item">my engagements</span>
                  </a>
                </li>
                <li>
                  <a href="open_queries.php">
                    <span class="sub-item">Open Queries</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'Client'): ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#client">
              <i class="fas fa-table"></i>
              <p>Client</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="client">
              <ul class="nav nav-collapse">
                <li>
                  <a href="client_engagements.php">
                    <span class="sub-item">My Engagements</span>
                  </a>
                </li>
                <li>
                  <a href="view_reports.php">
                    <span class="sub-item">View Reports</span>
                  </a>
                </li>
                <li>
                  <a href="open_queries.php">
                    <span class="sub-item">My Queries</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php endif; ?>

        <li class="nav-item">
          <a href="documentation.md">
            <i class="fas fa-file"></i>
            <p>Documentation</p>
            <span class="badge badge-secondary">1</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>
<!-- End Sidebar -->