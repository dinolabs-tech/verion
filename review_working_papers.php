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

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review_paper') {
  $paper_id = $_POST['paper_id'] ?? 0;
  $review_status = $_POST['review_status'] ?? '';
  $review_comments = $_POST['review_comments'] ?? '';
  $reviewed_by_user_id = $_SESSION['user_id'];

  if ($paper_id > 0 && !empty($review_status)) {
    $stmt = $conn->prepare("UPDATE working_papers SET review_status = ?, review_comments = ?, reviewed_by_user_id = ?, reviewed_at = CURRENT_TIMESTAMP WHERE paper_id = ? AND engagement_id = ?");
    $stmt->bind_param("ssiii", $review_status, $review_comments, $reviewed_by_user_id, $paper_id, $engagement_id);
    if ($stmt->execute()) {
      $success_message = "Working paper reviewed successfully!";
    } else {
      $error_message = "Error updating review: " . $conn->error;
    }
    $stmt->close();
  } else {
    $error_message = "Invalid data provided for review.";
  }
}

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

// Fetch existing working papers for this engagement
$working_papers = [];
if ($engagement) {
  $result = $conn->query("SELECT wp.*, u.username, r.username AS reviewer_username FROM working_papers wp JOIN users u ON wp.uploaded_by_user_id = u.user_id LEFT JOIN users r ON wp.reviewed_by_user_id = r.user_id WHERE wp.engagement_id = $engagement_id ORDER BY wp.uploaded_at DESC");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $working_papers[] = $row;
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
                <h1 class="mb-4">Review Working Papers for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- List of Working Papers for Review -->
                <div class="card">
                  <div class="card-header">
                    <h4>Working Papers</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($working_papers)): ?>
                      <p>No working papers found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Title</th>
                              <th>Description</th>
                              <th>File Name</th>
                              <th>Uploaded By</th>
                              <th>Uploaded At</th>
                              <th>Status</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($working_papers as $paper): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($paper['paper_id']); ?></td>
                                <td><?php echo htmlspecialchars($paper['title']); ?></td>
                                <td><?php echo htmlspecialchars($paper['description']); ?></td>
                                <td><a href="<?php echo htmlspecialchars($paper['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($paper['file_name']); ?></a></td>
                                <td><?php echo htmlspecialchars($paper['username']); ?></td>
                                <td><?php echo htmlspecialchars($paper['uploaded_at']); ?></td>
                                <td>
                                  <?php
                                  $status_badge = 'secondary';
                                  if ($paper['review_status'] === 'Approved') {
                                    $status_badge = 'success';
                                  } elseif ($paper['review_status'] === 'Rejected') {
                                    $status_badge = 'danger';
                                  }
                                  ?>
                                  <span class="badge bg-<?php echo $status_badge; ?>"><?php echo htmlspecialchars($paper['review_status']); ?></span>
                                  <?php if ($paper['reviewed_by_user_id']): ?>
                                    <br><small>by <?php echo htmlspecialchars($paper['reviewer_username']); ?> at <?php echo htmlspecialchars($paper['reviewed_at']); ?></small>
                                  <?php endif; ?>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-primary btn-icon btn-round" data-bs-toggle="modal" data-bs-target="#reviewWorkingPaperModal"
                                    data-id="<?php echo $paper['paper_id']; ?>"
                                    data-title="<?php echo htmlspecialchars($paper['title']); ?>"
                                    data-description="<?php echo htmlspecialchars($paper['description']); ?>"
                                    data-file-path="<?php echo htmlspecialchars($paper['file_path']); ?>"
                                    data-file-name="<?php echo htmlspecialchars($paper['file_name']); ?>"
                                    data-review-status="<?php echo htmlspecialchars($paper['review_status']); ?>"
                                    data-review-comments="<?php echo htmlspecialchars($paper['review_comments']); ?>">
                                    <i class="fas fa-list"></i>
                                  </button>
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
                  <a href="review_engagement_details.php?engagement_id=<?php echo $engagement_id; ?>" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="reviewWorkingPaperModal" tabindex="-1" aria-labelledby="reviewWorkingPaperModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="reviewWorkingPaperModalLabel">Review Working Paper</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <h4 id="review_paper_title"></h4>
              <p><strong>Description:</strong> <span id="review_paper_description"></span></p>
              <p><strong>File:</strong> <a id="review_paper_file_link" href="#" target="_blank"></a></p>
              <hr>
              <form action="review_working_papers.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                <input type="hidden" name="action" value="review_paper">
                <input type="hidden" name="paper_id" id="review_paper_id">
                <div class="mb-3">
                  <label for="review_comments" class="form-label">Review Comments</label>
                  <textarea class="form-control" id="review_comments" name="review_comments" rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Review Status</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="review_status" id="review_status_approved" value="Approved" required>
                    <label class="form-check-label" for="review_status_approved">Approve</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="review_status" id="review_status_rejected" value="Rejected" required>
                    <label class="form-check-label" for="review_status_rejected">Reject</label>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                </div>
              </form>
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