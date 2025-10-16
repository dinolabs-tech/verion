<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin')) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$engagements = [];
$stmt = null;

if ($user_role === 'Auditor') {
  $stmt = $conn->prepare("
        SELECT e.*, c.client_name,
               ua.username AS auditor_username,
               ur.username AS reviewer_username
        FROM engagements e
        JOIN clients c ON e.client_id = c.client_id
        LEFT JOIN users ua ON e.assigned_auditor_id = ua.user_id
        LEFT JOIN users ur ON e.assigned_reviewer_id = ur.user_id
        WHERE e.assigned_auditor_id = ?
        ORDER BY e.engagement_year DESC, c.client_name
    ");
  $stmt->bind_param("i", $user_id);
} elseif ($user_role === 'Admin') {
  // Admin can see all engagements
  $stmt = $conn->prepare("
        SELECT e.*, c.client_name,
               ua.username AS auditor_username,
               ur.username AS reviewer_username
        FROM engagements e
        JOIN clients c ON e.client_id = c.client_id
        LEFT JOIN users ua ON e.assigned_auditor_id = ua.user_id
        LEFT JOIN users ur ON e.assigned_reviewer_id = ur.user_id
        ORDER BY e.engagement_year DESC, c.client_name
    ");
}

if ($stmt) {
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $engagements[] = $row;
  }
  $stmt->close();
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
                <h1 class="mb-4">My Engagements</h1>
                <?php if ($user_role === 'Admin'): ?>
                  <p class="alert alert-info">As an Admin, you see all engagements. Auditors only see their assigned engagements.</p>
                <?php endif; ?>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h4>Assigned Engagements</h4>
              </div>
              <div class="card-body">
                <?php if (empty($engagements)): ?>
                  <p>No engagements found or assigned to you.</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover" id="basic-datatables" >
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Client</th>
                          <th>Year</th>
                          <th>Period</th>
                          <th>Type</th>
                          <th>Status</th>
                          <th>Auditor</th>
                          <th>Reviewer</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($engagements as $engagement): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($engagement['engagement_id']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['engagement_year']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['period']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['engagement_type']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['status']); ?></td>
                            <td><?php echo htmlspecialchars($engagement['auditor_username'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($engagement['reviewer_username'] ?? 'N/A'); ?></td>
                            <td>
                              <a href="engagement_details.php?engagement_id=<?= $engagement['engagement_id']; ?>" class="btn btn-sm btn-info btn-icon btn-round"><i class="fas fa-list"></i></a>
                              <!-- More actions for auditor within an engagement -->
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
