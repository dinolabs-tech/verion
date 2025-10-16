<?php
session_start();
require_once 'database/db_connection.php';

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


<!-- Mirrored from www.urbanui.com/melody/template/pages/samples/blank-page.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 15 Sep 2018 06:08:54 GMT -->
 <?php include('components/head.php'); ?>

<body>
  <div class="container-scroller">
    <!-- partial:../../partials/_navbar.html -->
     <?php include('components/navbar.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:../../partials/_settings-panel.html -->
      <div class="theme-setting-wrapper">
        <div id="settings-trigger"><i class="fas fa-fill-drip"></i></div>
        <div id="theme-settings" class="settings-panel">
          <i class="settings-close fa fa-times"></i>
          <p class="settings-heading">SIDEBAR SKINS</p>
          <div class="sidebar-bg-options selected" id="sidebar-light-theme"><div class="img-ss rounded-circle bg-light border mr-3"></div>Light</div>
          <div class="sidebar-bg-options" id="sidebar-dark-theme"><div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark</div>
          <p class="settings-heading mt-2">HEADER SKINS</p>
          <div class="color-tiles mx-0 px-4">
            <div class="tiles primary"></div>
            <div class="tiles success"></div>
            <div class="tiles warning"></div>
            <div class="tiles danger"></div>
            <div class="tiles info"></div>
            <div class="tiles dark"></div>
            <div class="tiles default"></div>
          </div>
        </div>
      </div>
      <!-- partial -->
      <!-- partial:../../partials/_sidebar.html -->
      <?php include('components/sidebar.php'); ?>
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="card">
            <div class="card-header">
              Edit Management
            </div>
            <div class="card-body">
              <form action="">
                <!-- <input type="text"> -->
                <div>
                  <input type="text" class="form-control" id="engagement_name" name="engagement_name" placeholder="Enter Engagement Name">
                </div>
                <div class="mt-3">
                  <select class="form-select form-control" id="edit_client_id" name="client_id">
                      <option value="">Select Client</option>
                      <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
                <div class="mt-3">
                      <label for="edit_engagement_year" class="form-label">Engagement Year</label>
                      <input type="number" class="form-control" id="edit_engagement_year" name="engagement_year" required>
                    </div>
                    <div class="mt-3">
                      <label for="edit_period" class="form-label">Period</label>
                      <input type="text" class="form-control" id="edit_period" name="period" required>
                    </div>
                    <div class="mt-3">
                      <label for="edit_engagement_type" class="form-label">Engagement Type</label>
                      <select class="form-select form-control" id="edit_engagement_type" name="engagement_type" required>
                        <option value="Internal">Internal</option>
                        <option value="External">External</option>
                      </select>
                    </div>
                    <div class="mt-3">
                      <label for="edit_status" class="form-label">Status</label>
                      <select class="form-select form-control" id="edit_status" name="status" required>
                        <option value="Planning">Planning</option>
                        <option value="Fieldwork">Fieldwork</option>
                        <option value="Review">Review</option>
                        <option value="Reporting">Reporting</option>
                        <option value="Closed">Closed</option>
                      </select>
                    </div>
                     <div class="mt-3">
                      <label for="edit_assigned_auditor_id" class="form-label">Assign Auditor</label>
                      <select class="form-select form-control" id="edit_assigned_auditor_id" name="assigned_auditor_id">
                        <option value="">Select Auditor (Optional)</option>
                        <?php foreach ($auditors as $auditor): ?>
                          <option value="<?php echo $auditor['user_id']; ?>"><?php echo htmlspecialchars($auditor['username']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                     <div class="mt-3">
                        <label for="edit_assigned_reviewer_id" class="form-label">Assign Reviewer</label>
                        <select class="form-select form-control" id="edit_assigned_reviewer_id" name="assigned_reviewer_id">
                          <option value="">Select Reviewer (Optional)</option>
                          <?php foreach ($reviewers as $reviewer): ?>
                            <option value="<?php echo $reviewer['user_id']; ?>"><?php echo htmlspecialchars($reviewer['username']); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                     <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
              </form>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
         <?php include('components/footer.php'); ?>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
   <?php include('components/script.php'); ?>
   
  <!-- endinject -->
  <!-- Custom js for this page-->
  <!-- End custom js for this page-->
</body>


<!-- Mirrored from www.urbanui.com/melody/template/pages/samples/blank-page.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 15 Sep 2018 06:08:54 GMT -->
</html>
