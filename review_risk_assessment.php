<?php
session_start();
require_once 'database/db_connection.php';

// Only Reviewer or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Reviewer' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser')) {
  header("Location: login.php");
  exit();
}

$engagement_id = $_GET['engagement_id'] ?? 0;
$engagement = null;
$success_message = '';
$error_message = '';

if ($engagement_id > 0) {
  $stmt = $conn->prepare("SELECT e.engagement_id, e.engagement_year, e.period, c.client_name FROM engagements e JOIN clients c ON e.client_id = c.client_id WHERE e.engagement_id = ?");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 1) {
    $engagement = $result->fetch_assoc();
  } else {
    $error_message = "Engagement not found.";
  }
  $stmt->close();
} else {
  $error_message = "No engagement ID provided.";
}

// Fetch existing risk assessments for this engagement
$risks = [];
if ($engagement) {
  $result = $conn->query("SELECT ra.*, u.username FROM risk_assessment ra JOIN users u ON ra.assessed_by_user_id = u.user_id WHERE ra.engagement_id = $engagement_id ORDER BY ra.assessed_at DESC");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $risks[] = $row;
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
                <a href="engagements_for_review.php" class="btn btn-secondary">Back to Engagements for Review</a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Review Risk Assessment for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- List of Risk Assessments for Review -->
                <div class="card">
                  <div class="card-header">
                    <h4>Risk Assessments</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($risks)): ?>
                      <p>No risk assessments found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Risk Area</th>
                              <th>Inherent</th>
                              <th>Control</th>
                              <th>Detection</th>
                              <th>Overall</th>
                              <th>Mitigation Strategy</th>
                              <th>Assessed By</th>
                              <th>Assessed At</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($risks as $risk): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($risk['risk_id']); ?></td>
                                <td><?php echo htmlspecialchars($risk['risk_area']); ?></td>
                                <td><?php echo htmlspecialchars($risk['inherent_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['control_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['detection_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['overall_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['mitigation_strategy']); ?></td>
                                <td><?php echo htmlspecialchars($risk['username']); ?></td>
                                <td><?php echo htmlspecialchars($risk['assessed_at']); ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="mt-4">
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