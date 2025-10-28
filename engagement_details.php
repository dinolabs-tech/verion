<?php

/**
 * File: engagement_details.php
 * Purpose: This file displays detailed information about a specific audit engagement.
 * It retrieves engagement details from the database and provides links to various modules related to the engagement.
 */

session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
/**
 * Check if the user is logged in and has the necessary role (Auditor or Admin).
 * If not, redirect to the login page.
 */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser')) {
  header("Location: login.php");
  exit();
}

// Retrieve the engagement ID from the GET request.
/**
 * Get the engagement ID from the URL parameters.
 */
$engagement_id = $_GET['engagement_id'];
// Initialize variables
$engagement = null;
$error_message = '';

// Fetch engagement details from the database
/**
 * If an engagement ID is provided, fetch the engagement details from the database.
 */
if ($engagement_id > 0) {
  // Prepare a SQL statement to fetch engagement details, including client name, auditor username, and reviewer username.
  $stmt = $conn->prepare("
        SELECT e.*, c.client_name,
               ua.username AS auditor_username,
               ur.username AS reviewer_username
        FROM engagements e
        JOIN clients c ON e.client_id = c.client_id
        LEFT JOIN users ua ON e.assigned_auditor_id = ua.user_id
        LEFT JOIN users ur ON e.assigned_reviewer_id = ur.user_id
        WHERE e.engagement_id = ?
    ");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();

  // If the engagement is found, store the engagement details.
  if ($result->num_rows === 1) {
    $engagement = $result->fetch_assoc();
    // Ensure the logged-in auditor is assigned to this engagement, or if it's an Admin
    /**
     * If the user is an auditor, verify that they are assigned to the engagement.
     * If not, set an error message and clear the engagement data.
     */
    if ($_SESSION['role'] == 'Auditor' && $engagement['assigned_auditor_id'] != $_SESSION['user_id']) {
      $error_message = "You are not authorized to view this engagement.";
      $engagement = null; // Clear engagement data if not authorized
    }
  } else {
    // If the engagement is not found, set an error message.
    $error_message = "Engagement not found.";
  }
  // Close the prepared statement.
  $stmt->close();
} else {
  // If no engagement ID is provided, set an error message.
  $error_message = "No engagement ID provided.";
}

// Fetch clients for dropdowns
$clients = [];
$client_result = $conn->query("SELECT client_id, client_name FROM clients ORDER BY client_name");
if ($client_result) {
  while ($row = $client_result->fetch_assoc()) {
    $clients[] = $row;
  }
}

// Fetch auditors for dropdowns
$auditors = [];
$auditor_result = $conn->query("SELECT user_id, username FROM users WHERE role = 'Auditor' ORDER BY username");
if ($auditor_result) {
  while ($row = $auditor_result->fetch_assoc()) {
    $auditors[] = $row;
  }
}

// Fetch reviewers for dropdowns
$reviewers = [];
$reviewer_result = $conn->query("SELECT user_id, username FROM users WHERE role = 'Reviewer' ORDER BY username");
if ($reviewer_result) {
  while ($row = $reviewer_result->fetch_assoc()) {
    $reviewers[] = $row;
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
          <div class="row">
            <div class="col-12">
              <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo $error_message; ?>
                </div>
                <a href="my_engagements.php" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Engagement Details: <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?>)</h1>
                <p><strong>Period:</strong> <?php echo htmlspecialchars($engagement['period']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($engagement['engagement_type']); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($engagement['status']); ?></span></p>
                <p><strong>Assigned Auditor:</strong> <?php echo htmlspecialchars($engagement['auditor_username'] ?? 'N/A'); ?></p>
                <p><strong>Assigned Reviewer:</strong> <?php echo htmlspecialchars($engagement['reviewer_username'] ?? 'N/A'); ?></p>

                <button type="button" class="btn btn-primary mb-3 edit-engagement-btn btn-icon btn-round" data-bs-toggle="modal" data-bs-target="#editEngagementModal"
                  data-id="<?php echo $engagement['engagement_id']; ?>"
                  data-engagement-name="<?php echo htmlspecialchars($engagement['engagement_name']); ?>"
                  data-client-id="<?php echo htmlspecialchars($engagement['client_id']); ?>"
                  data-year="<?php echo htmlspecialchars($engagement['engagement_year']); ?>"
                  data-period="<?php echo htmlspecialchars($engagement['period']); ?>"
                  data-type="<?php echo htmlspecialchars($engagement['engagement_type']); ?>"
                  data-status="<?php echo htmlspecialchars($engagement['status']); ?>"
                  data-auditor-id="<?php echo htmlspecialchars($engagement['assigned_auditor_id']); ?>"
                  data-reviewer-id="<?php echo htmlspecialchars($engagement['assigned_reviewer_id']); ?>">
                  <i class="fas fa-edit"></i>
                </button>

                <hr>

                <h3 class="text-center">Engagement Modules</h3>
                <div class="row">
                  <div class="col-md-3">
                    <a href="view_trial_balance.php?engagement_id=<?= $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-warning">
                        <div class="card-body">
                          <i class="fas fa-solid fa-chart-bar me-2"></i> View Trial Balance
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="upload_trial_balance.php?engagement_id=<?= $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-primary text-white">
                        <div class="card-body">
                          <i class="fas fa-solid fa-file-upload me-2"></i> Upload Trial Balance
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="view_balance_sheet.php?engagement_id=<?= $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-secondary text-white">
                        <div class="card-body">
                          <i class="fas fa-solid fa-file-invoice-dollar me-2"></i> View Balance Sheet
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="risk_assessment.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-danger text-white">
                        <div class="card-body">
                          <i class="fas fa-exclamation-triangle me-2"></i> Risk Assessment
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="materiality_calculator.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-success text-white">
                        <div class="card-body">
                          <i class="fas fa-solid fa-calculator me-2"></i> Materiality Calculator
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="audit_sampling.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-warning">
                        <div class="card-body">
                          <i class="fas fa-solid fa-bullseye me-2"></i> Audit Sampling
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="queries.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-primary text-white">
                        <div class="card-body">
                          <i class="fas fa-solid fa-comments me-2"></i> Queries & Responses
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="working_papers.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-secondary text-white">
                        <div class="card-body">
                          <i class="fas fa-solid fa-paperclip me-2"></i> Working Paper Attachments
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="audit_adjustments.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-info text-white">
                        <div class="card-body">
                          <i class="fas fa-edit fa-pen-to-square me-2"></i> Audit Adjustments
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="reconciliations.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-success text-white">
                        <div class="card-body">
                          <i class="fas fa-sync-alt me-2"></i> Reconciliations & Testing
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="exception_reports.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-warning">
                        <div class="card-body">
                          <i class="fas fa-exclamation-circle me-2"></i> Exception Reports
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="anomaly_detection.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-primary text-white">
                        <div class="card-body">
                          <i class="fas fa-glasses me-2"></i> Anomaly Detection
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="generate_reports.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-dark text-white">
                        <div class="card-body">
                          <i class="far fa-file-alt me-2"></i> Generate Reports
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="compliance_checklists.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-info text-white">
                        <div class="card-body">
                          <i class="fas fa-balance-scale me-2"></i> Compliance & Standards
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="internal_controls.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-success text-white">
                        <div class="card-body">
                          <i class="fas fa-shield-alt me-2"></i> Internal Controls
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-3">
                    <a href="kpi_dashboard.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-warning">
                        <div class="card-body">
                          <i class="fas fa-chart-line me-2"></i> KPI Dashboard
                        </div>
                      </div>
                    </a>
                  </div>
                </div>

                <div class="mt-4 col-md-12 text-center">
                  <a href="my_engagements.php" class="btn btn-lg btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="editEngagementModal" tabindex="-1" aria-labelledby="editEngagementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editEngagementModalLabel">Edit Engagement</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="manage_engagements.php" method="POST"> <!-- Action points to manage_engagements.php for update logic -->
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="engagement_id" id="edit_engagement_id">
                <div class="mb-3">
                  <input type="text" class="form-control" placeholder="Engagement Name" id="edit_engagement_name" name="engagement_name" required>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_client_id" name="client_id" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $client): ?>
                      <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <input type="number" class="form-control" id="edit_engagement_year" placeholder="Engagement Year" name="engagement_year" required>
                </div>
                <div class="mb-3">
                  <label for="edit_period" class="form-label"></label>
                  <input type="text" class="form-control" placeholder="Period" id="edit_period" name="period" required>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_engagement_type" name="engagement_type" required>
                    <option value="Internal">Internal</option>
                    <option value="External">External</option>
                  </select>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_status" name="status" required>
                    <option value="Planning">Planning</option>
                    <option value="Fieldwork">Fieldwork</option>
                    <option value="Review">Review</option>
                    <option value="Reporting">Reporting</option>
                    <option value="Closed">Closed</option>
                  </select>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_assigned_auditor_id" name="assigned_auditor_id">
                    <option value="">Select Auditor (Optional)</option>
                    <?php foreach ($auditors as $auditor): ?>
                      <option value="<?php echo $auditor['user_id']; ?>"><?php echo htmlspecialchars($auditor['username']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_assigned_reviewer_id" name="assigned_reviewer_id">
                    <option value="">Select Reviewer (Optional)</option>
                    <?php foreach ($reviewers as $reviewer): ?>
                      <option value="<?php echo $reviewer['user_id']; ?>"><?php echo htmlspecialchars($reviewer['username']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
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
    $('#editEngagementModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var engagementName = button.data('engagement-name');
      var clientId = button.data('client-id');
      var year = button.data('year');
      var period = button.data('period');
      var type = button.data('type');
      var status = button.data('status');
      var auditorId = button.data('auditor-id');
      var reviewerId = button.data('reviewer-id');

      var modal = $(this);
      modal.find('.modal-title').text('Edit Engagement: ' + engagementName);
      modal.find('#edit_engagement_id').val(id);
      modal.find('#edit_engagement_name').val(engagementName);
      modal.find('#edit_client_id').val(clientId);
      modal.find('#edit_engagement_year').val(year);
      modal.find('#edit_period').val(period);
      modal.find('#edit_engagement_type').val(type);
      modal.find('#edit_status').val(status);
      modal.find('#edit_assigned_auditor_id').val(auditorId);
      modal.find('#edit_assigned_reviewer_id').val(reviewerId);
    });
  </script>
</body>

</html>