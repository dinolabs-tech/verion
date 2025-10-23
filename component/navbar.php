      <div class="main-header bg-black">
        <div class="main-header-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="index.php" class="logo">
              <img
                src="assets/img/kaiadmin/logo_light.svg"
                alt="navbar brand"
                class="navbar-brand"
                height="20" />
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
        <!-- Navbar Header -->
        <nav
          class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
          <div class="container-fluid">
            <nav
              class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
            </nav>

            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
              <li
                class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
             
              </li>

              <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item topbar-user dropdown">
                  <a
                    class=" nav-link dropdown-toggle"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false">

                    <span class="profile-username text-white me-3">
                      <span> <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>) </span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">

                      <li>
                        <a class="dropdown-item" href="profile.php">
                          <i class="fas fa-cog text-primary"></i>
                          Manage profile</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                          <i class="fas fa-power-off text-primary"></i>
                          Logout</a>
                      </li>
                    </div>
                  </ul>

                </li>
               <?php else: ?>
                <li class="nav-item">
                  <a class="nav-link" href="login.php">Login</a>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        </nav>
        <!-- End Navbar -->
      </div>