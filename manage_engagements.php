<?php
session_start();
require_once 'database/db_connection.php';

// Only Admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser') {
  header("Location: login.php");
  exit();
}

$success_message = '';
$error_message = '';

// Handle Add/Edit Engagement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $engagement_name = $_POST['engagement_name'] ?? '';
    $client_id = $_POST['client_id'] ?? 0;
    $engagement_year = $_POST['engagement_year'] ?? date('Y');
    $period = $_POST['period'] ?? '';
    $engagement_type = $_POST['engagement_type'] ?? '';
    $status = $_POST['status'] ?? 'Planning';
    $assigned_auditor_id = $_POST['assigned_auditor_id'] ?? NULL;
    $assigned_reviewer_id = $_POST['assigned_reviewer_id'] ?? NULL;
    $created_by_user_id = $_SESSION['user_id'];

    if (empty($client_id) || empty($engagement_year) || empty($period) || empty($engagement_type)) {
      $error_message = "Client, Year, Period, and Type are required fields.";
    } else {
      if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO engagements (engagement_name, client_id, engagement_year, period, engagement_type, status, assigned_auditor_id, assigned_reviewer_id, created_by_user_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siissssii", $engagement_name, $client_id, $engagement_year, $period, $engagement_type, $status, $assigned_auditor_id, $assigned_reviewer_id, $created_by_user_id);
        if ($stmt->execute()) {
          $success_message = "Engagement added successfully!";
        } else {
          $error_message = "Error adding engagement: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        $engagement_id = $_POST['engagement_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE engagements SET engagement_name = ?, client_id = ?, engagement_year = ?, period = ?, engagement_type = ?, status = ?, assigned_auditor_id = ?, assigned_reviewer_id = ? WHERE engagement_id = ?");
        $stmt->bind_param("siissssii", $engagement_name, $client_id, $engagement_year, $period, $engagement_type, $status, $assigned_auditor_id, $assigned_reviewer_id, $engagement_id);
        if ($stmt->execute()) {
          $success_message = "Engagement updated successfully!";
        } else {
          $error_message = "Error updating engagement: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Engagement
if (isset($_GET['delete_id'])) {
  $engagement_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM engagements WHERE engagement_id = ?");
  $stmt->bind_param("i", $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Engagement deleted successfully!";
  } else {
    $error_message = "Error deleting engagement: " . $conn->error;
  }
  $stmt->close();
  header("Location: manage_engagements.php?message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch all engagements with client, auditor, and reviewer names
$engagements = [];
$result = $conn->query("
    SELECT e.*, c.client_name,
           ua.username AS auditor_username,
           ur.username AS reviewer_username
    FROM engagements e
    JOIN clients c ON e.client_id = c.client_id
    LEFT JOIN users ua ON e.assigned_auditor_id = ua.user_id
    LEFT JOIN users ur ON e.assigned_reviewer_id = ur.user_id
    ORDER BY e.engagement_year DESC, c.client_name
");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $engagements[] = $row;
  }
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
              <h1 class="mb-4">Manage Engagements</h1>
              <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success" role="alert">
                  <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
              <?php endif; ?>
              <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
              <?php endif; ?>
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
            </div>
          </div>
          <!-- Add New Engagement Form -->
          <div class="card mb-4">
            <div class="card-header">
              <h4>Add New Engagement</h4>
            </div>
            <div class="card-body">
              <form action="manage_engagements.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="row">
                  <div class="mb-3 col-md-6">
                    <input type="text" class="form-control" id="engagement_name" name="engagement_name" placeholder="Enter Engagement Name">
                  </div>
                  <div class="mb-3 col-md-6">

                    <select class="form-select form-control" id="client_id" name="client_id">
                      <option value="" selected disabled>Select Client</option>
                      <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3 col-md-6">
                    <input type="number" class="form-control" id="engagement_year" placeholder="Engagement Year" name="engagement_year" value="<?php echo date('Y'); ?>">
                  </div>
                  <div class="mb-3 col-md-6">
                    <input type="text" class="form-control" id="period" name="period" placeholder=" Period e.g., FY2023, Q4 2023">
                  </div>
                  <div class="mb-3 col-md-6">
                    <select class="form-select form-control" id="engagement_type" name="engagement_type">
                      <option value="" selected disabled>Select Type</option>
                      <option value="Internal">Internal</option>
                      <option value="External">External</option>
                    </select>
                  </div>
                  <div class="mb-3 col-md-6">
                    <select class="form-select form-control" id="assigned_auditor_id" name="assigned_auditor_id">
                      <option value="" selected disabled>Select Auditor (Optional)</option>
                      <?php foreach ($auditors as $auditor): ?>
                        <option value="<?php echo $auditor['user_id']; ?>"><?php echo htmlspecialchars($auditor['username']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3 col-md-6">
                    <select class="form-select form-control" id="assigned_reviewer_id" name="assigned_reviewer_id">
                      <option value="" selected disabled>Select Reviewer (Optional)</option>
                      <?php foreach ($reviewers as $reviewer): ?>
                        <option value="<?php echo $reviewer['user_id']; ?>"><?php echo htmlspecialchars($reviewer['username']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3 col-md-6">
                    <button type="submit" class="btn btn-primary btn-icon btn-round "><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!-- List of Engagements -->
          <div class="card">
            <div class="card-header">
              <h4>Existing Engagements</h4>
            </div>
            <div class="card-body">
              <?php if (empty($engagements)): ?>
                <p>No engagements found. Add a new engagement above.</p>
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
                          <td class="d-flex">
                            <button type="button" class=" btn-primary edit-engagement-btn btn-icon btn-round text-white me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editEngagementModal"
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
                            <a href="manage_engagements.php?delete_id=<?php echo $engagement['engagement_id']; ?>" class=" text-white btn-danger btn-icon btn-round" onclick="return confirm('Are you sure you want to delete this engagement?');"><i class="fas fa-trash"></i></a>
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

      <!-- Edit Engagement Modal -->
      <div class="modal fade" id="editEngagementModal" tabindex="-1" aria-labelledby="editEngagementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editEngagementModalLabel">Edit Engagement</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="manage_engagements.php" method="POST">
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
                  <input type="number" class="form-control" placeholder="Engagement Year"  id="edit_engagement_year" name="engagement_year" required>
                </div>
                <div class="mb-3">
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