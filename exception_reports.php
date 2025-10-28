<?php

/**
 * File: exception_reports.php
 * Purpose: This file allows Auditors and Admins to manage exception reports for a specific engagement.
 * It retrieves engagement details, handles adding, editing, and deleting exception reports, and displays a list of reports.
 */

session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
/**
 * Check if the user is logged in and has the necessary role (Auditor or Admin).
 * If not, redirect to the login page.
 */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Client' && $_SESSION['role'] !== 'Reviewer' && $_SESSION['role'] !== 'Superuser')) {
  header("Location: login.php");
  exit();
}

// Retrieve the engagement ID from the GET request.
/**
 * Get the engagement ID from the URL parameters.
 */
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

// Handle Adding, Editing, and Deleting Exception Reports
// Check if the request method is POST and if an engagement is selected.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $engagement) {
  // Check if the 'action' parameter is set.
  if (isset($_POST['action'])) {
    // Retrieve the action to be performed (add or edit).
    $action = $_POST['action'];
    // Retrieve the form data.
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $severity = $_POST['severity'] ?? 'Low';
    $status = $_POST['status'] ?? 'Open';
    // Get the ID of the user raising the report.
    $raised_by_user_id = $_SESSION['user_id'];

    // Validate the input data.
    if (empty($_POST['title'])) {
      $error_message = "Title is required.";
    }
    if (empty($_POST['description'])) {
      $error_message = "Description is required.";
    }
    if (empty($_POST['severity'])) {
      $error_message = "Severity is required.";
    }
    if (empty($_POST['status'])) {
      $error_message = "Status is required.";
    }

    if (empty($error_message)) {
      // Sanitize input data to prevent SQL injection
      $title = htmlspecialchars($_POST['title']);
      $description = htmlspecialchars($_POST['description']);
      $severity = htmlspecialchars($_POST['severity']);
      $status = htmlspecialchars($_POST['status']);

      // Perform the appropriate action based on the 'action' parameter.
      if ($action === 'add') {
        // Prepare a SQL statement to insert a new exception report.
        $stmt = $conn->prepare("INSERT INTO exception_reports (engagement_id, title, description, severity, status, raised_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $engagement_id, $title, $description, $severity, $status, $raised_by_user_id);
        if ($stmt->execute()) {
          $success_message = "Exception report added successfully!";
        } else {
          $error_message = "Error adding exception report: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        // Retrieve the report ID.
        $report_id = $_POST['report_id'] ?? 0;
        $resolved_at = ($status === 'Resolved') ? date('Y-m-d H:i:s') : NULL;

        $stmt = $conn->prepare("UPDATE exception_reports SET title = ?, description = ?, severity = ?, status = ?, resolved_at = ? WHERE report_id = ? AND engagement_id = ?");
        $stmt->bind_param("sssssii", $title, $description, $severity, $status, $resolved_at, $report_id, $engagement_id);
        if ($stmt->execute()) {
          $success_message = "Exception report updated successfully!";
        } else {
          $error_message = "Error updating exception report: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Exception Report
// Check if a delete request is made.
if (isset($_GET['delete_id']) && $engagement) {
  // Retrieve the report ID to be deleted.
  $report_id = $_GET['delete_id'];
  // Prepare a SQL statement to delete the exception report.
  $stmt = $conn->prepare("DELETE FROM exception_reports WHERE report_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $report_id, $engagement_id);
  // Execute the statement and display a success or error message.
  if ($stmt->execute()) {
    $success_message = "Exception report deleted successfully!";
  } else {
    $error_message = "Error deleting exception report: " . $conn->error;
  }
  $stmt->close();
  // Redirect to the same page with success or error messages.
  header("Location: exception_reports.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing exception reports for this engagement
$exception_reports = [];
// If engagement details were successfully retrieved, fetch the exception reports.
if ($engagement) {
  // Execute a query to fetch exception reports.
  $result = $conn->query("SELECT er.*, u.username FROM exception_reports er JOIN users u ON er.raised_by_user_id = u.user_id WHERE er.engagement_id = $engagement_id ORDER BY er.raised_at DESC");
  // Store the exception reports in an array.
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $exception_reports[] = $row;
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

          <div class="row">
            <div class="col-12">
              <?php if ($error_message && !$engagement): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo $error_message; ?>
                </div>
                <a href="my_engagements.php" class="btn btn-secondary">Back to My Engagements</a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Exception Reports for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

                <?php if (isset($_GET['message'])): ?>
                  <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                  </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                  <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                  </div>
                <?php endif; ?>
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

                <!-- Add New Exception Report Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Add New Exception Report</h4>
                  </div>
                  <div class="card-body">
                    <form action="exception_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="mb-3 col-md-6">
                          <input type="text" class="form-control" placeholder="Title" id="title" name="title" required>
                        </div>
                        <div class="mb-3 col-md-6">
                          <textarea class="form-control" id="description" placeholder="Description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control" id="severity" name="severity" required>
                            <option value="" selected disabled>Select Severity</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control" id="status" name="status" required>
                            <option value="" selected disabled>Status</option>
                            <option value="Open">Open</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Acknowledged">Acknowledged</option>
                          </select>
                        </div>

                        <div class="mb-3 col-md-12" style="text-align: center;">
                          <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-plus"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Exception Reports -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Exception Reports</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($exception_reports)): ?>
                      <p>No exception reports found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Title</th>
                              <th>Description</th>
                              <th>Severity</th>
                              <th>Status</th>
                              <th>Raised By</th>
                              <th>Raised At</th>
                              <th>Resolved At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($exception_reports as $report): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($report['report_id']); ?></td>
                                <td><?php echo htmlspecialchars($report['title']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($report['description'])); ?></td>
                                <td><?php echo htmlspecialchars($report['severity']); ?></td>
                                <td><span class="badge bg-<?php echo ($report['status'] === 'Open' ? 'warning' : ($report['status'] === 'Resolved' ? 'success' : 'info')); ?>"><?php echo htmlspecialchars($report['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($report['username']); ?></td>
                                <td><?php echo htmlspecialchars($report['raised_at']); ?></td>
                                <td><?php echo htmlspecialchars($report['resolved_at'] ?? 'N/A'); ?></td>
                                <td class="d-flex">
                                  
                                      <button type="button" class="btn btn-icon btn-round btn-primary text-white me-1 mb-1 ps-1" data-bs-toggle="modal" data-bs-target="#editExceptionReportModal"
                                        data-id="<?php echo $report['report_id']; ?>"
                                        data-title="<?php echo htmlspecialchars($report['title']); ?>"
                                        data-description="<?php echo htmlspecialchars($report['description']); ?>"
                                        data-severity="<?php echo htmlspecialchars($report['severity']); ?>"
                                        data-status="<?php echo htmlspecialchars($report['status']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                   
                                      <a href="exception_reports.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $report['report_id']; ?>" class="btn btn-icon btn-round btn-danger tetx-white" onclick="return confirm('Are you sure you want to delete this exception report?');"><i class="fas fa-trash"></i></a>
                                    
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="mt-4">
                  <a href="engagement_details.php?engagement_id=<?php echo $engagement_id; ?>" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <!-- Edit Exception Report Modal -->
      <div class="modal fade" id="editExceptionReportModal" tabindex="-1" aria-labelledby="editExceptionReportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editExceptionReportModalLabel">Edit Exception Report</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="exception_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="report_id" id="edit_report_id">
                <div class="mb-3">
                  <label for="edit_title" class="form-label"></label>
                  <input type="text" class="form-control" placeholder="Title" id="edit_title" name="title" required>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_description" placeholder="Description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label for="edit_severity" class="form-label"></label>
                  <select class="form-select" id="edit_severity" name="severity" required>
                    <option value="">Severity</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                  </select>
                </div>
                <div class="mb-3">
                  <select class="form-select" id="edit_status" name="status" required>
                    <option value="">Status</option>
                    <option value="Open">Open</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Acknowledged">Acknowledged</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
  <script>
    var editExceptionReportModal = document.getElementById('editExceptionReportModal');
    editExceptionReportModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      var id = button.getAttribute('data-id');
      var title = button.getAttribute('data-title');
      var description = button.getAttribute('data-description');
      var severity = button.getAttribute('data-severity');
      var status = button.getAttribute('data-status');

      var modalTitle = editExceptionReportModal.querySelector('.modal-title');
      var reportIdInput = editExceptionReportModal.querySelector('#edit_report_id');
      var titleInput = editExceptionReportModal.querySelector('#edit_title');
      var descriptionTextarea = editExceptionReportModal.querySelector('#edit_description');
      var severitySelect = editExceptionReportModal.querySelector('#edit_severity');
      var statusSelect = editExceptionReportModal.querySelector('#edit_status');

      modalTitle.textContent = 'Edit Exception Report (ID: ' + id + ')';
      reportIdInput.value = id;
      titleInput.value = title;
      descriptionTextarea.value = description;
      severitySelect.value = severity;
      statusSelect.value = status;
    });
  </script>
</body>

</html>