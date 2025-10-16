<?php

/**
 * File: approve_reports.php
 * Purpose: This file allows Reviewers and Admins to approve audit reports and management letters.
 * It retrieves engagement details, handles report approval, and displays a list of reports for approval.
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

// Retrieve the engagement ID from the GET request.  Default to 0 if not provided.
$engagement_id = $_GET['engagement_id'] ?? 0;
// Initialize variables
$engagement = null;
$success_message = '';
$error_message = '';

// If an engagement ID is provided, fetch the engagement details.
if ($engagement_id > 0) {
  // Prepare a SQL statement to fetch engagement details.
  $stmt = $conn->prepare("SELECT e.engagement_id, e.engagement_year, e.period, c.client_name FROM engagements e JOIN clients c ON e.client_id = c.client_id WHERE e.engagement_id = ?");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  // If the engagement is found, store the engagement details.
  if ($result->num_rows === 1) {
    $engagement = $result->fetch_assoc();
  } else {
    $error_message = "Engagement not found.";
  }
  $stmt->close();
} else {
  $error_message = "No engagement ID provided.";
}

// Handle Report Approval
// Check if the request method is POST and if an engagement is selected.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $engagement) {
  // Check if the action is to approve an audit report or a management letter.
  if (isset($_POST['action']) && ($_POST['action'] === 'approve_audit_report' || $_POST['action'] === 'approve_management_letter')) {
    // Retrieve the report ID and determine the table name based on the action.
    $report_id = $_POST['report_id'] ?? 0;
    $table_name = ($_POST['action'] === 'approve_audit_report') ? 'audit_reports' : 'management_letters';
    // Get the ID of the user approving the report.
    $approved_by_user_id = $_SESSION['user_id'];

    // Prepare a SQL statement to update the report with the approval details.
    $stmt = $conn->prepare("UPDATE $table_name SET approved_by_user_id = ?, approved_at = CURRENT_TIMESTAMP WHERE report_id = ? AND engagement_id = ?");
    $stmt->bind_param("iii", $approved_by_user_id, $report_id, $engagement_id);
    // Execute the statement and display a success or error message.
    if ($stmt->execute()) {
      $success_message = ucfirst(str_replace('approve_', '', $_POST['action'])) . " approved successfully!";
    } else {
      $error_message = "Error approving report: " . $conn->error;
    }
    $stmt->close();
  }
}

// Fetch existing audit reports for this engagement
$audit_reports = [];
// If engagement details were successfully retrieved, fetch the audit reports.
if ($engagement) {
  // Execute a query to fetch audit reports.
  $result = $conn->query("SELECT ar.*, u.username FROM audit_reports ar JOIN users u ON ar.generated_by_user_id = u.user_id WHERE ar.engagement_id = $engagement_id ORDER BY ar.generated_at DESC");
  // Store the audit reports in an array.
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $audit_reports[] = $row;
    }
  }
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

          <div class="row g-2">
            <div class="col-12 md-8">
              <div class="card-title px-3 pd-3">
                <?php if ($error_message && !$engagement): ?>
                  <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                  </div>
                  <a href="engagements_for_review.php" class="btn btn-secondary">Back to Engagements for Review</a>
                <?php elseif ($engagement): ?>
                  <h1 class="mb-4">Approve Reports for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

                  <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert">
                      <?php echo $success_message; ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                      <?php echo $error_message; ?>
                    </div>
                  <?php endif; ?>
              </div>
              <div class="card-body mt-4 mb-4">
                <div class="card-header">
                  <h4>Audit Reports</h4>
                </div>
                <div class="card-body">
                  <?php if (empty($audit_reports)): ?>
                    <p>No audit reports found for this engagement.</p>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="basic-datatables">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Generated By</th>
                            <th>Generated At</th>
                            <th>Approved At</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($audit_reports as $report): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($report['report_id']); ?></td>
                              <td><?php echo htmlspecialchars($report['title']); ?></td>
                              <td><?php echo htmlspecialchars($report['username']); ?></td>
                              <td><?php echo htmlspecialchars($report['generated_at']); ?></td>
                              <td>
                                <?php
                                if (!empty($report['approved_at'])) {
                                  echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($report['approved_at'])));
                                } else {
                                  echo 'N/A';
                                }
                                ?>
                              </td>
                              <td>
                                <?php if (!$report['approved_by_user_id']): ?>
                                  <form action="approve_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                                    <input type="hidden" name="action" value="approve_audit_report">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                  </form>
                                <?php else: ?>
                                  <span class="text-success">Approved</span>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="card-footer mt-4">
                <a href="review_engagement_details.php?engagement_id=<?php echo $engagement_id; ?>" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
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