<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Client')) {
  header("Location: login.php");
  exit();
}

$success_message = '';
$error_message = '';
$reports = [];

// Determine if the user is a client and get their client_id
$is_client = ($_SESSION['role'] === 'Client');
$client_id = $is_client && isset($_SESSION['client_id']) ? $_SESSION['client_id'] : null;

// Build the WHERE clause for client-specific filtering
$client_filter_clause = '';
if ($is_client && $client_id) {
  $client_filter_clause = " WHERE e.client_id = " . intval($client_id);
}

// Fetch Audit Reports
$audit_reports_query = "SELECT ar.report_id AS report_identifier, ar.*, e.engagement_year, e.period, c.client_name FROM audit_reports ar JOIN engagements e ON ar.engagement_id = e.engagement_id JOIN clients c ON e.client_id = c.client_id" . $client_filter_clause . " ORDER BY ar.generated_at DESC";
$audit_reports_result = $conn->query($audit_reports_query);
if ($audit_reports_result) {
  while ($row = $audit_reports_result->fetch_assoc()) {
    $row['report_type'] = 'Audit Report';
    $reports[] = $row;
  }
}

// Fetch Management Letters
$management_letters_query = "SELECT ml.letter_id AS report_identifier, ml.*, e.engagement_year, e.period, c.client_name FROM management_letters ml JOIN engagements e ON ml.engagement_id = e.engagement_id JOIN clients c ON e.client_id = c.client_id" . $client_filter_clause . " ORDER BY ml.generated_at DESC";
$management_letters_result = $conn->query($management_letters_query);
if ($management_letters_result) {
  while ($row = $management_letters_result->fetch_assoc()) {
    $row['report_type'] = 'Management Letter';
    $reports[] = $row;
  }
}

// Sort reports by generated_at in descending order
usort($reports, function ($a, $b) {
  return strtotime($b['generated_at']) - strtotime($a['generated_at']);
});

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
              <h1 class="mb-4">View Reports</h1>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h4>Available Reports</h4>
            </div>
            <div class="card-body">
              <?php if (empty($reports)): ?>
                <p>No reports found.</p>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="basic-datatables">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Engagement</th>
                        <th>Generated At</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($reports as $report): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($report['report_type']); ?></td>
                          <td><?php echo htmlspecialchars($report['title']); ?></td>
                          <td><?php echo htmlspecialchars($report['client_name']) . ' (' . htmlspecialchars($report['engagement_year']) . ' - ' . htmlspecialchars($report['period']) . ')'; ?></td>
                          <td><?php echo htmlspecialchars($report['generated_at']); ?></td>
                          <td>
                            <!-- Link to view report details (e.g., in a modal or separate page) -->
                            <?php if (!empty($report['file_path'])): ?>
                              <a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                            <?php else: ?>
                              <a href="download_report_pdf.php?report_id=<?php echo htmlspecialchars($report['report_identifier']); ?>&report_type=<?php echo urlencode($report['report_type']); ?>" class="btn btn-sm btn-info" target="_blank">Download PDF</a>
                            <?php endif; ?>
                            <?php if ($_SESSION['role'] === 'Auditor' || $_SESSION['role'] === 'Admin'): ?>
                              <a href="edit_report.php?report_id=<?php echo htmlspecialchars($report['report_identifier']); ?>&report_type=<?php echo urlencode($report['report_type']); ?>" class="btn btn-sm btn-warning">Edit</a>
                              <a href="delete_report.php?report_id=<?php echo htmlspecialchars($report['report_identifier']); ?>&report_type=<?php echo urlencode($report['report_type']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
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
        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
</body>

</html>
