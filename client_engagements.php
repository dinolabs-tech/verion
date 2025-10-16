<?php

/**
 * File: client_engagements.php
 * Purpose: This file displays a list of engagements associated with a specific client.
 * It retrieves the client's engagements from the database and presents them in a table.
 */

session_start();
require_once 'database/db_connection.php';

// Only Client can access this page
/**
 * Check if the user is logged in and has the necessary role (Client).
 * If not, redirect to the login page.
 */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
  header("Location: login.php");
  exit();
}

// Retrieve the user ID from the session.
$user_id = $_SESSION['user_id'];
// Initialize the client ID variable.
$client_id = null;

// Get client_id from users table
/**
 * Prepare a SQL statement to fetch the client ID associated with the user ID.
 */
$client_id_stmt = $conn->prepare("SELECT client_id FROM users WHERE user_id = ?");
$client_id_stmt->bind_param("i", $user_id);
$client_id_stmt->execute();
$client_id_result = $client_id_stmt->get_result();

// If a client ID is found for the user, store it.
if ($client_id_result->num_rows > 0) {
  $client_id = $client_id_result->fetch_assoc()['client_id'];
} else {
  // If no client ID is found, set an error message.
  $error_message = "Client ID not found for this user.";
}
$client_id_stmt->close();

// Initialize an array to store the engagements.
$engagements = [];
// If a client ID is available, fetch the engagements associated with the client.
if ($client_id) {
  // Prepare a SQL statement to fetch engagement details, including client name, auditor username, and reviewer username.
  $stmt = $conn->prepare("
        SELECT e.*, c.client_name,
               ua.username AS auditor_username,
               ur.username AS reviewer_username
        FROM engagements e
        JOIN clients c ON e.client_id = c.client_id
        LEFT JOIN users ua ON e.assigned_auditor_id = ua.user_id
        LEFT JOIN users ur ON e.assigned_reviewer_id = ur.user_id
        WHERE e.client_id = ?
        ORDER BY e.engagement_year DESC, c.client_name
    ");
  $stmt->bind_param("i", $client_id);
  $stmt->execute();
  $result = $stmt->get_result();
  // Store the engagement details in an array.
  while ($row = $result->fetch_assoc()) {
    $engagements[] = $row;
  }
  $stmt->close();
}

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
           <div class="row">
              <div class="col-12">
                <h1 class="mb-4">My Engagements</h1>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h4>Engagements</h4>
              </div>
              <div class="card-body">
                <?php if (empty($engagements)): ?>
                  <p>No engagements found for your client.</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover" id="basic-datatables">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Year</th>
                          <th>Period</th>
                          <th>Type</th>
                          <th>Status</th>
                          <th>Auditor</th>
                          <th>Reviewer</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($engagements as $engagement): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($engagement['engagement_id']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['engagement_year']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['period']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['engagement_type']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['status']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['auditor_username'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($engagement['reviewer_username'] ?? 'N/A'); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
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
