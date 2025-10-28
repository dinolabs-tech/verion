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
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Reviewer' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser')) {
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $engagement) {
  $report_id = $_POST['report_id'] ?? 0;
  $action = $_POST['action'] ?? '';
  $table_name = '';
  $id_column = '';

  if ($action === 'approve_audit_report' || $action === 'reviewer_approve_audit_report') {
    $table_name = 'audit_reports';
    $id_column = 'report_id';
  } elseif ($action === 'approve_management_letter' || $action === 'reviewer_approve_management_letter') {
    $table_name = 'management_letters';
    $id_column = 'letter_id';
  }

  if ($report_id > 0 && !empty($table_name)) {
    $current_user_id = $_SESSION['user_id'];
    $current_user_role = $_SESSION['role'];

    // Fetch engagement's reviewer_id to check if current user is the assigned reviewer
    $engagement_reviewer_id = null;
    $stmt_reviewer = $conn->prepare("SELECT assigned_reviewer_id FROM engagements WHERE engagement_id = ?");
    $stmt_reviewer->bind_param("i", $engagement_id);
    $stmt_reviewer->execute();
    $result_reviewer = $stmt_reviewer->get_result();
    if ($result_reviewer->num_rows === 1) {
      $engagement_reviewer_id = $result_reviewer->fetch_assoc()['assigned_reviewer_id'];
    }
    $stmt_reviewer->close();

    if ($action === 'reviewer_approve_audit_report' || $action === 'reviewer_approve_management_letter') {
      // Only the assigned reviewer can perform this action
      if ($current_user_role === 'Reviewer' && $current_user_id == $engagement_reviewer_id) {
        $stmt = $conn->prepare("UPDATE $table_name SET reviewer_approved = TRUE WHERE $id_column = ? AND engagement_id = ?");
        $stmt->bind_param("ii", $report_id, $engagement_id);
        if ($stmt->execute()) {
          $success_message = "Report reviewer approved successfully!";
        } else {
          $error_message = "Error reviewer approving report: " . $conn->error;
        }
        $stmt->close();
      } else {
        $error_message = "You are not authorized to reviewer approve this report.";
      }
    } elseif ($action === 'approve_audit_report' || $action === 'approve_management_letter') {
      // Only Admin or Reviewer (if they are the final approver) can perform this action
      if ($current_user_role === 'Admin' || ($current_user_role === 'Superuser' && $current_user_id == $engagement_reviewer_id) || ($current_user_role === 'Reviewer' && $current_user_id == $engagement_reviewer_id)) {
        $stmt = $conn->prepare("UPDATE $table_name SET approved_by_user_id = ?, approved_at = CURRENT_TIMESTAMP WHERE $id_column = ? AND engagement_id = ?");
        $stmt->bind_param("iii", $current_user_id, $report_id, $engagement_id);
        if ($stmt->execute()) {
          $success_message = ucfirst(str_replace('approve_', '', $action)) . " approved successfully!";
        } else {
          $error_message = "Error approving report: " . $conn->error;
        }
        $stmt->close();
      } else {
        $error_message = "You are not authorized to approve this report.";
      }
    }
  }
}

// Fetch existing audit reports for this engagement
$audit_reports = [];
if ($engagement) {
  $stmt = $conn->prepare("SELECT ar.*, u.username AS generated_by_username,
                                 app_u.username AS approved_by_username,
                                 e.assigned_reviewer_id
                          FROM audit_reports ar
                          JOIN users u ON ar.generated_by_user_id = u.user_id
                          LEFT JOIN users app_u ON ar.approved_by_user_id = app_u.user_id
                          JOIN engagements e ON ar.engagement_id = e.engagement_id
                          WHERE ar.engagement_id = ? ORDER BY ar.generated_at DESC");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $audit_reports[] = $row;
    }
  }
  $stmt->close();
}

// Fetch existing management letters for this engagement
$management_letters = [];
if ($engagement) {
  $stmt = $conn->prepare("SELECT ml.*, u.username AS generated_by_username,
                                 app_u.username AS approved_by_username,
                                 e.assigned_reviewer_id
                          FROM management_letters ml
                          JOIN users u ON ml.generated_by_user_id = u.user_id
                          LEFT JOIN users app_u ON ml.approved_by_user_id = app_u.user_id
                          JOIN engagements e ON ml.engagement_id = e.engagement_id
                          WHERE ml.engagement_id = ? ORDER BY ml.generated_at DESC");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $management_letters[] = $row;
    }
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
                            <th>Reviewer Approved</th>
                            <th>Approved By</th>
                            <th>Approved At</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($audit_reports as $report): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($report['report_id']); ?></td>
                              <td><?php echo htmlspecialchars($report['title']); ?></td>
                              <td><?php echo htmlspecialchars($report['generated_by_username']); ?></td>
                              <td><?php echo htmlspecialchars($report['generated_at']); ?></td>
                              <td>
                                <?php if ($report['reviewer_approved']): ?>
                                  <span class="text-success">Yes</span>
                                <?php else: ?>
                                  <span class="text-danger">No</span>
                                <?php endif; ?>
                              </td>
                              <td><?php echo htmlspecialchars($report['approved_by_username'] ?? 'N/A'); ?></td>
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
                                <?php if ($_SESSION['role'] === 'Reviewer' && $_SESSION['user_id'] == $report['assigned_reviewer_id'] && !$report['reviewer_approved']): ?>
                                  <form action="approve_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="reviewer_approve_audit_report">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-info">Reviewer Approve</button>
                                  </form>
                                <?php endif; ?>
                                <?php if (!$report['approved_by_user_id'] && ($report['reviewer_approved'] || $_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Superuser')): ?>
                                  <form action="approve_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="approve_audit_report">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                  </form>
                                <?php elseif ($report['approved_by_user_id']): ?>
                                  <span class="text-success">Approved</span>
                                <?php endif; ?>
                                <a href="download_report_pdf.php?report_id=<?php echo $report['report_id']; ?>&report_type=Audit Report" class="btn btn-sm btn-primary" target="_blank">View PDF</a>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-body mt-4 mb-4">
                <div class="card-header">
                  <h4>Management Letters</h4>
                </div>
                <div class="card-body">
                  <?php if (empty($management_letters)): ?>
                    <p>No management letters found for this engagement.</p>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="management-letters-datatables">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Generated By</th>
                            <th>Generated At</th>
                            <th>Reviewer Approved</th>
                            <th>Approved By</th>
                            <th>Approved At</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($management_letters as $letter): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($letter['letter_id']); ?></td>
                              <td><?php echo htmlspecialchars($letter['title']); ?></td>
                              <td><?php echo htmlspecialchars($letter['generated_by_username']); ?></td>
                              <td><?php echo htmlspecialchars($letter['generated_at']); ?></td>
                              <td>
                                <?php if ($letter['reviewer_approved']): ?>
                                  <span class="text-success">Yes</span>
                                <?php else: ?>
                                  <span class="text-danger">No</span>
                                <?php endif; ?>
                              </td>
                              <td><?php echo htmlspecialchars($letter['approved_by_username'] ?? 'N/A'); ?></td>
                              <td>
                                <?php
                                if (!empty($letter['approved_at'])) {
                                  echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($letter['approved_at'])));
                                } else {
                                  echo 'N/A';
                                }
                                ?>
                              </td>
                              <td>
                                <?php if ($_SESSION['role'] === 'Reviewer' && $_SESSION['user_id'] == $letter['assigned_reviewer_id'] && !$letter['reviewer_approved']): ?>
                                  <form action="approve_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="reviewer_approve_management_letter">
                                    <input type="hidden" name="report_id" value="<?php echo $letter['letter_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-info">Reviewer Approve</button>
                                  </form>
                                <?php endif; ?>
                                <?php if (!$letter['approved_by_user_id'] && ($letter['reviewer_approved'] || $_SESSION['role'] === 'Admin')): ?>
                                  <form action="approve_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="approve_management_letter">
                                    <input type="hidden" name="report_id" value="<?php echo $letter['letter_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                  </form>
                                <?php elseif ($letter['approved_by_user_id']): ?>
                                  <span class="text-success">Approved</span>
                                <?php endif; ?>
                                <a href="download_report_pdf.php?report_id=<?php echo $letter['letter_id']; ?>&report_type=Management Letter" class="btn btn-sm btn-primary" target="_blank">View PDF</a>
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
