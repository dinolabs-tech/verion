<!DOCTYPE html>
<html lang="en">
  <?php include('component/head.php'); ?>
  <body>
    <div class="wrapper">
      <?php include('component/sidebar.php'); ?>

      <div class="main-panel">
        <?php include('component/navbar.php'); ?>

        <div class="container">
          <div class="page-inner">
            <div class="page-header">
              <h4 class="page-title">Dashboard</h4>
              <ul class="breadcrumbs">
                <li class="nav-home">
                  <a href="#">
                    <i class="icon-home"></i>
                  </a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                  <a href="#">Pages</a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                  <a href="#">Starter Page</a>
                </li>
              </ul>
            </div>
            <div class="page-category">Inner page content goes here</div>
          </div>
        </div>

        <?php include('component/footer.php'); ?>
      </div>
    </div>
    <?php include('component/script.php'); ?>
  </body>
</html>
