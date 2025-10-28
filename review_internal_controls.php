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

// Fetch existing internal controls for this engagement
$internal_controls = [];
if ($engagement) {
  $result = $conn->query("SELECT ic.*, u.username FROM internal_controls ic LEFT JOIN users u ON ic.tested_by_user_id = u.user_id WHERE ic.engagement_id = $engagement_id ORDER BY ic.control_name");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $internal_controls[] = $row;
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
                <h1 class="mb-4">Review Internal Controls for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- List of Internal Controls for Review -->
                <div class="card">
                  <div class="card-header">
                    <h4>Internal Controls</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($internal_controls)): ?>
                      <p>No internal controls found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Control Name</th>
                              <th>Description</th>
                              <th>Design Effectiveness</th>
                              <th>Operating Effectiveness</th>
                              <th>Tested By</th>
                              <th>Tested At</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($internal_controls as $control): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($control['control_id']); ?></td>
                                <td><?php echo htmlspecialchars($control['control_name']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($control['description'])); ?></td>
                                <td><?php echo htmlspecialchars($control['design_effectiveness'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($control['operating_effectiveness'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($control['username'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($control['tested_at'] ?? 'N/A'); ?></td>
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