<?php
session_start();
require_once 'database/db_connection.php';

// Only Reviewer or Admin can access this page
// if (!isset($_SESSION['user_id']) && ($_SESSION['role'] !== 'Reviewer' || $_SESSION['role'] !== 'Admin')) {
//     header("Location: login.php");
//     exit();
// }

$engagement_id = $_GET['engagement_id'] ?? 0;
$engagement = null;
$error_message = '';

if ($engagement_id > 0) {
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

  if ($result->num_rows === 1) {
    $engagement = $result->fetch_assoc();
    // Ensure the logged-in reviewer is assigned to this engagement, or if it's an Admin
    if ($_SESSION['role'] == 'Reviewer' && $engagement['assigned_reviewer_id'] != $_SESSION['user_id']) {
      $error_message = "You are not authorized to review this engagement.";
      $engagement = null; // Clear engagement data if not authorized
    }
  } else {
    $error_message = "Engagement not found.";
  }
  $stmt->close();
} else {
  $error_message = "No engagement ID provided.";
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
              <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo $error_message; ?>
                </div>
                <a href="engagements_for_review.php" class="btn btn-secondary">Back to Engagements for Review</a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Review Engagement: <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?>)</h1>
                <p><strong>Period:</strong> <?php echo htmlspecialchars($engagement['period']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($engagement['engagement_type']); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($engagement['status']); ?></span></p>
                <p><strong>Assigned Auditor:</strong> <?php echo htmlspecialchars($engagement['auditor_username'] ?? 'N/A'); ?></p>
                <p><strong>Assigned Reviewer:</strong> <?php echo htmlspecialchars($engagement['reviewer_username'] ?? 'N/A'); ?></p>

                <hr>

                <h3>Review Modules</h3>
                <div class="list-group">
                  <a href="review_working_papers.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-file-earmark-text me-2"></i> Review Working Papers
                  </a>
                  <a href="review_audit_adjustments.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-pencil-square me-2 "></i> Review Audit Adjustments
                  </a>
                  <!-- <a href="review_queries.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-question-circle me-2"></i> Review Queries & Responses
                        </a> -->
                  <a href="review_risk_assessment.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-shield-fill-exclamation me-2"></i> Review Risk Assessment
                  </a>
                  <a href="review_materiality_calculations.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-calculator me-2"></i> Review Materiality Calculations
                  </a>
                  <a href="review_reconciliations.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-repeat me-2"></i> Review Reconciliations
                  </a>
                  <a href="review_exception_reports.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-exclamation-triangle me-2"></i> Review Exception Reports
                  </a>
                  <a href="approve_reports.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-check-circle me-2"></i> Approve Reports
                  </a>
                  <a href="review_compliance_checklists.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-check-square me-2"></i> Review Compliance Checklists
                  </a>
                  <a href="review_internal_controls.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-lock me-2"></i> Review Internal Controls
                  </a>
                </div>

                <div class="mt-4">
                  <a href="engagements_for_review.php" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
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