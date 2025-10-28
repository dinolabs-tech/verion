<?php

/**
 * File: dashboard.php
 * Purpose: This file displays the main dashboard for authenticated users.
 * It shows different information based on the user's role.
 */

session_start();
require_once 'database/db_connection.php';

// Redirect to login if not logged in
/**
 * Check if the user is logged in. If not, redirect to the login page.
 */
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Get user role and username from session
/**
 * Retrieve the user's role and username from the session.
 */
$user_role = $_SESSION['role'];
$username = $_SESSION['username'];

// Fetch some data based on role for the dashboard
/**
 * Initialize an array to store the dashboard data.
 */
$dashboard_data = [];

// Admin dashboard data
/**
 * If the user is an Admin, fetch and display the total number of users, engagements, and clients.
 */
if ($user_role === 'Admin') {
  // Example: Count total users, engagements, clients
  /**
   * Prepare and execute a query to count the total number of users.
   */
  $total_users_stmt = $conn->query("SELECT COUNT(*) AS total_users FROM users");
  // Fetch the result and store it in the dashboard data array.
  $dashboard_data['total_users'] = $total_users_stmt->fetch_assoc()['total_users'];

  /**
   * Prepare and execute a query to count the total number of engagements.
   */
  $total_engagements_stmt = $conn->query("SELECT COUNT(*) AS total_engagements FROM engagements");
  // Fetch the result and store it in the dashboard data array.
  $dashboard_data['total_engagements'] = $total_engagements_stmt->fetch_assoc()['total_engagements'];

  /**
   * Prepare and execute a query to count the total number of clients.
   */
  $total_clients_stmt = $conn->query("SELECT COUNT(*) AS total_clients FROM clients");
  // Fetch the result and store it in the dashboard data array.
  $dashboard_data['total_clients'] = $total_clients_stmt->fetch_assoc()['total_clients'];
} elseif ($user_role === 'Auditor') {
  // Example: Engagements assigned to this auditor
  /**
   * Prepare a SQL statement to count the engagements assigned to the auditor.
   */
  $assigned_engagements_stmt = $conn->prepare("SELECT COUNT(*) AS assigned_engagements FROM engagements WHERE assigned_auditor_id = ?");
  $assigned_engagements_stmt->bind_param("i", $_SESSION['user_id']);
  $assigned_engagements_stmt->execute();
  // Fetch the result and store it in the dashboard data array.
  $dashboard_data['assigned_engagements'] = $assigned_engagements_stmt->get_result()->fetch_assoc()['assigned_engagements'];

  // Example: Open queries raised by this auditor
  /**
   * Prepare a SQL statement to count the open queries raised by the auditor.
   */
  $open_queries_stmt = $conn->prepare("SELECT COUNT(*) AS open_queries FROM queries WHERE raised_by_user_id = ? AND status = 'Open'");
  $open_queries_stmt->bind_param("i", $_SESSION['user_id']);
  $open_queries_stmt->execute();
  // Fetch the result and store it in the dashboard data array.
  $dashboard_data['open_queries'] = $open_queries_stmt->get_result()->fetch_assoc()['open_queries'];
} elseif ($user_role === 'Reviewer') {
  // Example: Engagements assigned for review
  /**
   * Prepare a SQL statement to count the engagements assigned for review to the reviewer.
   */
  $engagements_for_review_stmt = $conn->prepare("SELECT COUNT(*) AS engagements_for_review FROM engagements WHERE assigned_reviewer_id = ?");
  $engagements_for_review_stmt->bind_param("i", $_SESSION['user_id']);
  $engagements_for_review_stmt->execute();
  // Fetch the result and store it in the dashboard data array.
  $dashboard_data['engagements_for_review'] = $engagements_for_review_stmt->get_result()->fetch_assoc()['engagements_for_review'];
} elseif ($user_role === 'Client') {
  // Example: Engagements for this client
  $client_id = null;
  /**
   * Prepare a SQL statement to fetch the client ID associated with the user ID.
   */
  $client_id_stmt = $conn->prepare("SELECT client_id FROM users WHERE user_id = ?");
  $client_id_stmt->bind_param("i", $_SESSION['user_id']);
  $client_id_stmt->execute();
  $client_id_result = $client_id_stmt->get_result();
  // If a client ID is found for the user, store it.
  if ($client_id_result->num_rows > 0) {
    $client_id = $client_id_result->fetch_assoc()['client_id'];
  }

  // If a client ID is available, fetch the engagements associated with the client.
  if ($client_id) {
    /**
     * Prepare a SQL statement to count the engagements for the client.
     */
    $client_engagements_stmt = $conn->prepare("SELECT COUNT(*) AS client_engagements FROM engagements WHERE client_id = ?");
    $client_engagements_stmt->bind_param("i", $client_id);
    $client_engagements_stmt->execute();
    // Fetch the result and store it in the dashboard data array.
    $dashboard_data['client_engagements'] = $client_engagements_stmt->get_result()->fetch_assoc()['client_engagements'];
  } else {
    $dashboard_data['client_engagements'] = 0;
  }
}

// Close the database connection.
$conn->close();
?>

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
            <h3 class="fw-bold ">DASHBOARD</h3>

          </div>
          
          <div
            class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Welcome, <?php echo htmlspecialchars($username); ?>!</h3>
              <h6 class="op-7 mb-2">Your role: <strong><?php echo htmlspecialchars($user_role); ?></strong></h6>
            </div>

          </div>

          <?php if ($user_role === 'Admin'): ?>
            <div class="row">
              <div class="col-sm-6 col-md-4">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-primary bubble-shadow-small">
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Total Users</p>
                          <h4 class="card-title"><?php echo $dashboard_data['total_users']; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-info bubble-shadow-small">
                          <i class="fas fa-user-check"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Total Engagements</p>
                          <h4 class="card-title"><?php echo $dashboard_data['total_engagements']; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-success bubble-shadow-small">
                          <i class="fas fa-luggage-cart"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Total Clients</p>
                          <h4 class="card-title"><?php echo $dashboard_data['total_clients']; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          <?php elseif ($user_role === 'Auditor'): ?>
            <div class="row">
              <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-primary bubble-shadow-small">
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Assigned Engagements</p>
                          <h4 class="card-title"><?php echo $dashboard_data['assigned_engagements']; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-info bubble-shadow-small">
                          <i class="fas fa-user-check"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Open Queries</p>
                          <h4 class="card-title"><?php echo $dashboard_data['open_queries']; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>


            </div>
          <?php elseif ($user_role === 'Reviewer'): ?>
            <div class="row">
              <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-primary bubble-shadow-small">
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Engagements for Review</p>
                          <h4 class="card-title"><?php echo $dashboard_data['engagements_for_review']; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>



            </div>
          <?php elseif ($user_role === 'Client'): ?>
            <div class="row">
              <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-primary bubble-shadow-small">
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Your Engagements</p>
                          <h4 class="card-title"><?php echo $dashboard_data['client_engagements']; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>


            </div>
          <?php endif; ?>
          <div class="row">
            <div class="col-md-12">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Quick Links / Recent Activity</div>
                  </div>
                  <!-- <p class="card-text">More content will go here based on user role and recent activities.</p> -->

                </div>

                <div class="card-body">
                  <ul>
                    <?php if ($user_role === 'Admin'): ?>
                      <li><a href="register.php">Register New User</a></li>
                      <li><a href="manage_clients.php">Manage Clients</a></li>
                      <li><a href="manage_engagements.php">Manage Engagements</a></li>
                    <?php elseif ($user_role === 'Auditor'): ?>
                      <li><a href="my_engagements.php">My Engagements</a></li>
                      <li><a href="open_queries.php">Open Queries</a></li>
                    <?php elseif ($user_role === 'Reviewer'): ?>
                      <li><a href="engagements_for_review.php">Engagements for Review</a></li>
                    <?php elseif ($user_role === 'Client'): ?>
                      <li><a href="client_engagements.php">View My Engagements</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

          </div>

        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>


  </div>
  <?php include('component/script.php'); ?>
</body>

</html>
<?php include 'backup.php'; ?>