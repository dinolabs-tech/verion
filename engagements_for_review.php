<?php

/**
 * File: engagements_for_review.php
 * Purpose: This file displays a list of engagements that are assigned to a reviewer for review.
 * It retrieves engagement details from the database and presents them in a table.
 */

session_start();
require_once 'database/db_connection.php';

// Only Reviewer or Admin can access this page
/**
 * Check if the user is logged in and has the necessary role (Reviewer or Admin).
 * If not, redirect to the login page.
 */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Reviewer' && $_SESSION['role'] !== 'Admin')) {
  header("Location: login.php");
  exit();
}

// Retrieve the user ID and role from the session.
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Initialize an array to store the engagements.
$engagements = [];
// Initialize the prepared statement variable.
$stmt = null;

// Fetch engagements based on user role
/**
 * If the user is a Reviewer, fetch only the engagements assigned to them.
 */
if ($user_role === 'Reviewer') {
  // Prepare a SQL statement to fetch engagements assigned to the reviewer.
  $stmt = $conn->prepare("
        SELECT e.*, c.client_name,
               ua.username AS auditor_username,
               ur.username AS reviewer_username
        FROM engagements e
        JOIN clients c ON e.client_id = c.client_id
        LEFT JOIN users ua ON e.assigned_auditor_id = ua.user_id
        LEFT JOIN users ur ON e.assigned_reviewer_id = ur.user_id
        WHERE e.assigned_reviewer_id = ?
        ORDER BY e.engagement_year DESC, c.client_name
    ");
  // Bind the user ID to the prepared statement.
  $stmt->bind_param("i", $user_id);
} elseif ($user_role === 'Admin') {
  // Admin can see all engagements
  /**
   * If the user is an Admin, fetch all engagements.
   */
  // Prepare a SQL statement to fetch all engagements.
  $stmt = $conn->prepare("
        SELECT e.*, c.client_name,
               ua.username AS auditor_username,
               ur.username AS reviewer_username
        FROM engagements e
        JOIN clients c ON e.client_id = c.client_id
        LEFT JOIN users ua ON e.assigned_auditor_id = ua.user_id
        LEFT JOIN users ur ON e.assigned_reviewer_id = ur.user_id
        ORDER BY e.engagement_year DESC, c.client_name
    ");
}

// Execute the prepared statement if it was successfully prepared.
if ($stmt) {
  // Execute the prepared statement.
  $stmt->execute();
  // Get the result set.
  $result = $stmt->get_result();
  // Fetch the engagements and store them in the $engagements array.
  while ($row = $result->fetch_assoc()) {
    $engagements[] = $row;
  }
  // Close the prepared statement.
  $stmt->close();
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
          <div class="row">
            <div class="col-12">
              <h1 class="mb-4">Engagements for Review</h1>
              <?php if ($user_role === 'Admin'): ?>
                <p class="alert alert-info">As an Admin, you see all engagements. Reviewers only see their assigned engagements.</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h4>Assigned Engagements for Review</h4>
            </div>
            <div class="card-body">
              <?php if (empty($engagements)): ?>
                <p>No engagements found or assigned to you for review.</p>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="basic-datatables">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Year</th>
                        <th>Period</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Auditor</th>
                        <th>Reviewer</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($engagements as $engagement): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($engagement['engagement_id']); ?></td>
                          <td><?php echo htmlspecialchars($engagement['client_name']); ?></td>
                          <td><?php echo htmlspecialchars($engagement['engagement_year']); ?></td>
                          <td><?php echo htmlspecialchars($engagement['period']); ?></td>
                          <td><?php echo htmlspecialchars($engagement['engagement_type']); ?></td>
                          <td><?php echo htmlspecialchars($engagement['status']); ?></td>
                          <td><?php echo htmlspecialchars($engagement['auditor_username'] ?? 'N/A'); ?></td>
                          <td><?php echo htmlspecialchars($engagement['reviewer_username'] ?? 'N/A'); ?></td>
                          <td>
                            <a href="review_engagement_details.php?engagement_id=<?php echo $engagement['engagement_id']; ?>" class="btn btn-sm btn-info btn-icon btn-round"><i class="fas fa-list"></i></a>
                          </td>
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