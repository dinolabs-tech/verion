<?php
session_start();
require_once 'database/db_connection.php';

// Only Reviewer or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Reviewer' && $_SESSION['role'] !== 'Admin')) {
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

// Fetch existing queries for this engagement
$queries = [];
if ($engagement) {
  $result = $conn->query("
        SELECT q.*, u_raised.username AS raised_by_username, u_to.username AS raised_to_username
        FROM queries q
        JOIN users u_raised ON q.raised_by_user_id = u_raised.user_id
        LEFT JOIN users u_to ON q.raised_to_user_id = u_to.user_id
        WHERE q.engagement_id = $engagement_id
        ORDER BY q.raised_at DESC
    ");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $queries[] = $row;
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
                <h1 class="mb-4">Review Queries & Responses for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

                <?php if ($success_message): ?>
                  <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                  </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                  <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                  </div>
                <?php endif; ?>

                <!-- List of Queries for Review -->
                <div class="card">
                  <div class="card-header">
                    <h4>Queries</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($queries)): ?>
                      <p>No queries found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables" >
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Query</th>
                              <th>Raised By</th>
                              <th>Raised To</th>
                              <th>Status</th>
                              <th>Raised At</th>
                              <th>Response</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($queries as $query): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($query['query_id']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($query['query_text'])); ?></td>
                                <td><?php echo htmlspecialchars($query['raised_by_username']); ?></td>
                                <td><?php echo htmlspecialchars($query['raised_to_username'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-<?php echo ($query['status'] === 'Open' ? 'warning' : ($query['status'] === 'Responded' ? 'info' : 'success')); ?>"><?php echo htmlspecialchars($query['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($query['raised_at']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($query['response_text'] ?? 'N/A')); ?></td>
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