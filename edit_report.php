<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin')) {
  header("Location: login.php");
  exit();
}

$report_id = $_GET['report_id'] ?? 0;
$report_type = $_GET['report_type'] ?? ''; // 'Audit Report' or 'Management Letter'
$engagement_id = 0; // Initialize engagement_id

$report_data = null;
$success_message = '';
$error_message = '';

if ($report_id > 0 && !empty($report_type)) {
  $table_name = ($report_type === 'Audit Report') ? 'audit_reports' : 'management_letters';

  // Fetch report data
  $id_column = ($report_type === 'Audit Report') ? 'report_id' : 'letter_id';
  $stmt = $conn->prepare("SELECT r.*, e.engagement_year, e.period, c.client_name FROM $table_name r JOIN engagements e ON r.engagement_id = e.engagement_id JOIN clients c ON e.client_id = c.client_id WHERE r.$id_column = ?");
  $stmt->bind_param("i", $report_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $report_data = $result->fetch_assoc();
    $engagement_id = $report_data['engagement_id'];
  } else {
    $error_message = "Report not found.";
  }
  $stmt->close();
} else {
  $error_message = "Invalid report ID or type provided.";
}

// Handle form submission for updating the report
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $report_data) {
  $new_title = $_POST['report_title'] ?? '';
  $new_content = $_POST['report_content'] ?? '';
  $new_version_type = $_POST['report_version_type'] ?? '';

  if (empty($new_title) || empty($new_content) || empty($new_version_type)) {
    $error_message = "All fields are required.";
  } else {
    $table_name = ($report_type === 'Audit Report') ? 'audit_reports' : 'management_letters';
    $id_column = ($report_type === 'Audit Report') ? 'report_id' : 'letter_id';
    $stmt = $conn->prepare("UPDATE $table_name SET title = ?, content = ?, report_type = ? WHERE $id_column = ?");
    $stmt->bind_param("sssi", $new_title, $new_content, $new_version_type, $report_id);

    if ($stmt->execute()) {
      $success_message = "Report updated successfully!";
      // Update report_data to reflect changes
      $report_data['title'] = $new_title;
      $report_data['content'] = $new_content;
      $report_data['report_type'] = $new_version_type;
    } else {
      $error_message = "Error updating report: " . $conn->error;
    }
    $stmt->close();
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
              <?php if ($error_message && !$report_data): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo $error_message; ?>
                </div>
                <a href="view_reports.php" class="btn btn-secondary">Back to Reports</a>
              <?php elseif ($report_data): ?>
                <h1 class="mb-4">Edit <?php echo htmlspecialchars($report_data['report_type']); ?></h1>

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

                <div class="card">
                  <div class="card-header">
                    <h4>Report Details</h4>
                  </div>
                  <div class="card-body">
                    <form action="edit_report.php?report_id=<?php echo $report_id; ?>&report_type=<?php echo urlencode($report_type); ?>" method="POST">
                      <div class="mb-3">
                        <label for="report_title" class="form-label">Report Title</label>
                        <input type="text" class="form-control" id="report_title" name="report_title" value="<?php echo htmlspecialchars($report_data['title']); ?>" required>
                      </div>
                      <div class="mb-3">
                        <label for="report_version_type" class="form-label">Version Type</label>
                        <select class="form-select" id="report_version_type" name="report_version_type" required>
                          <option value="Draft" <?php echo ($report_data['report_type'] === 'Draft') ? 'selected' : ''; ?>>Draft</option>
                          <option value="Final" <?php echo ($report_data['report_type'] === 'Final') ? 'selected' : ''; ?>>Final</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="report_content" class="form-label">Report Content</label>
                        <textarea class="form-control" id="report_content" name="report_content" rows="10" required><?php echo htmlspecialchars($report_data['content']); ?></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary">Update Report</button>
                      <a href="view_reports.php" class="btn btn-secondary">Cancel</a>
                    </form>
                  </div>
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
