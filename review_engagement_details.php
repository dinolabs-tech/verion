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

                <h3 class="text-center">Review Modules</h3>
           
                <div class="row">
                  <div class="col-md-4">
                    <a href="review_working_papers.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-secondary text-white card-hover">
                        <div class="card-body">
                          <i class="fas fa-solid fa-paperclip me-2"></i> Review Working Papers
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-4">
                    <a href="review_audit_adjustments.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-primary text-white card-hover">
                        <div class="card-body">
                          <i class="fas fa-solid fa-bullseye me-2"></i> Review Audit Adjustments
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-4">
                    <a href="review_risk_assessment.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-success text-white">
                        <div class="card-body">
                          <i class="fas fa-exclamation-triangle me-2"></i> Review Risk Assessment
                        </div>
                      </div>
                    </a>
                  </div>

                  <!-- <a href="review_queries.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-question-circle me-2"></i> Review Queries & Responses
                        </a> -->

                  <div class="col-md-4">
                    <a href="review_materiality_calculations.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-warning">
                        <div class="card-body">
                          <i class="fas fa-solid fa-calculator me-2"></i> Review Materiality Calculations
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-4">
                    <a href="review_reconciliations.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-secondary text-white">
                        <div class="card-body">
                          <i class="fas fa-sync-alt me-2"></i> Review Reconciliations
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-4">
                    <a href="review_exception_reports.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-primary text-white">
                        <div class="card-body">
                          <i class="fas fa-exclamation-circle me-2"></i> Review Exception Reports
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-4">
                    <a href="approve_reports.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-success text-white">
                        <div class="card-body">
                          <i class="fas fa-file-signature me-2"></i> Approve Reports
                        </div>
                      </div>
                    </a>
                  </div>

                  <div class="col-md-4">
                    <a href="review_compliance_checklists.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-warning">
                        <div class="card-body">
                          <i class="fas fa-list-ol me-2"></i> Review Compliance Checklists
                        </div>
                      </div>
                    </a>
                  </div>


                  <div class="col-md-4">
                    <a href="review_internal_controls.php?engagement_id=<?php echo $engagement_id; ?>" class="list-group-item list-group-item-action">
                      <div class="card shadow bg-secondary text-white">
                        <div class="card-body">
                          <i class="fas fa-shield-alt me-2"></i> Review Internal Controls
                        </div>
                      </div>
                    </a>
                  </div>

                </div>

                <div class="mt-4 col-md-12 text-center">
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