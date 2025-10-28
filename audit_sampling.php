<?php

/**
 * File: audit_sampling.php
 * Purpose: This file allows Auditors and Admins to manage audit sampling for a specific engagement.
 * It retrieves engagement details, handles adding, editing, and deleting audit samples, and displays a list of samples.
 */

session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
/**
 * Check if the user is logged in and has the necessary role (Auditor or Admin).
 * If not, redirect to the login page.
 */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser')) {
  header("Location: login.php");
  exit();
}

// Retrieve the engagement ID from the GET request.  Default to 0 if not provided.
$engagement_id = $_GET['engagement_id'] ?? 0;
// Initialize variables
$engagement = null;
$success_message = '';
$error_message = '';

// If an engagement ID is provided, fetch the engagement details.
if ($engagement_id > 0) {
  // Prepare a SQL statement to fetch engagement details.
  $stmt = $conn->prepare("SELECT e.engagement_id, e.engagement_year, e.period, c.client_name FROM engagements e JOIN clients c ON e.client_id = c.client_id WHERE e.engagement_id = ?");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  // If the engagement is found, store the engagement details.
  if ($result->num_rows === 1) {
    $engagement = $result->fetch_assoc();
  } else {
    $error_message = "Engagement not found.";
  }
  $stmt->close();
} else {
  $error_message = "No engagement ID provided.";
}

// Handle Adding, Editing, and Deleting Audit Samples
// Check if the request method is POST and if an engagement is selected.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $engagement) {
  // Check if the 'action' parameter is set.
  if (isset($_POST['action'])) {
    // Retrieve the action to be performed (add or edit).
    $action = $_POST['action'];
    // Retrieve the form data.
    $sample_type = $_POST['sample_type'] ?? '';
    $population_size = $_POST['population_size'] ?? 0;
    $sample_size = $_POST['sample_size'] ?? 0;
    $sampling_method_details = $_POST['sampling_method_details'] ?? '';
    // Get the ID of the user creating the sample.
    $created_by_user_id = $_SESSION['user_id'];

    // Validate the input data.
    if (empty($sample_type) || empty($population_size) || empty($sample_size)) {
      $error_message = "Sample Type, Population Size, and Sample Size are required.";
    } elseif ($sample_size > $population_size) {
      $error_message = "Sample size cannot be greater than population size.";
    } else {
      // Perform the appropriate action based on the 'action' parameter.
      if ($action === 'add') {
        // Prepare a SQL statement to insert a new audit sample.
        $stmt = $conn->prepare("INSERT INTO audit_samples (engagement_id, sample_type, population_size, sample_size, sampling_method_details, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiis", $engagement_id, $sample_type, $population_size, $sample_size, $sampling_method_details, $created_by_user_id);
        // Execute the statement and display a success or error message.
        if ($stmt->execute()) {
          $success_message = "Audit sample added successfully!";
        } else {
          $error_message = "Error adding audit sample: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        // Retrieve the sample ID.
        $sample_id = $_POST['sample_id'] ?? 0;
        // Prepare a SQL statement to update an existing audit sample.
        $stmt = $conn->prepare("UPDATE audit_samples SET sample_type = ?, population_size = ?, sample_size = ?, sampling_method_details = ?, created_by_user_id = ? WHERE sample_id = ?");
        $stmt->bind_param("siiiii", $sample_type, $population_size, $sample_size, $sampling_method_details, $created_by_user_id, $sample_id);
        // Execute the statement and display a success or error message.
        if ($stmt->execute()) {
          $success_message = "Audit sample updated successfully!";
        } else {
          $error_message = "Error updating audit sample: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Audit Sample
// Check if a delete request is made.
if (isset($_GET['delete_id']) && $engagement) {
  // Retrieve the sample ID to be deleted.
  $sample_id = $_GET['delete_id'];
  // Prepare a SQL statement to delete the audit sample.
  $stmt = $conn->prepare("DELETE FROM audit_samples WHERE sample_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $sample_id, $engagement_id);
  // Execute the statement and display a success or error message.
  if ($stmt->execute()) {
    $success_message = "Audit sample deleted successfully!";
  } else {
    $error_message = "Error deleting audit sample: " . $conn->error;
  }
  $stmt->close();
  // Redirect to the same page with success or error messages.
  header("Location: audit_sampling.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing audit samples for this engagement
$audit_samples = [];
// If engagement details were successfully retrieved, fetch the audit samples.
if ($engagement) {
  // Execute a query to fetch audit samples.
  $result = $conn->query("SELECT als.*, u.username FROM audit_samples als JOIN users u ON als.created_by_user_id = u.user_id WHERE als.engagement_id = $engagement_id ORDER BY als.created_at DESC");
  // Store the audit samples in an array.
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $audit_samples[] = $row;
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
                <a href="my_engagements.php" class="btn btn-secondary">Back to My Engagements</a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Audit Sampling for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Add New Audit Sample Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Add New Audit Sample</h4>
                  </div>
                  <div class="card-body">
                    <form action="audit_sampling.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="mt-4 col-md-6">
                          <select class="form-select form-control" id="sample_type" name="sample_type" required>
                            <option value="" selected disabled>Select Sample Type</option>
                            <option value="Random">Random</option>
                            <option value="Systematic">Systematic</option>
                            <option value="Stratified">Stratified</option>
                            <option value="Monetary Unit">Monetary Unit</option>
                          </select>
                        </div>
                        <div class="mb-5 col-md-6">
                          <label for="population_size" class="form-label"></label>
                          <input type="number" class="form-control" id="population_size" placeholder="Population Size" pla name="population_size" required>
                        </div>
                        <div class="mb-3 col-md-6">
                          <label for="sample_size" class="form-label"></label>
                          <input type="number" class="form-control" id="sample_size" placeholder="Sample Size" name="sample_size" required>
                        </div>
                        <div class="mb-3 col-md-6">
                          <textarea class="form-control" id="sampling_method_details" placeholder="Sampling Method Details" name="sampling_method_details" rows="3"></textarea>
                        </div>
                        <div class="mb-3 col-md-12" style="text-align: center;">
                          <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-plus"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Audit Samples -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Audit Samples</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($audit_samples)): ?>
                      <p>No audit samples found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Sample Type</th>
                              <th>Population Size</th>
                              <th>Sample Size</th>
                              <th>Details</th>
                              <th>Created By</th>
                              <th>Created At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($audit_samples as $sample): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($sample['sample_id']); ?></td>
                                <td><?php echo htmlspecialchars($sample['sample_type']); ?></td>
                                <td><?php echo htmlspecialchars($sample['population_size']); ?></td>
                                <td><?php echo htmlspecialchars($sample['sample_size']); ?></td>
                                <td><?php echo htmlspecialchars($sample['sampling_method_details']); ?></td>
                                <td><?php echo htmlspecialchars($sample['username']); ?></td>
                                <td><?php echo htmlspecialchars($sample['created_at']); ?></td>
                                <td class="d-flex">
                                  
                                      <button type="button" class="btn btn-icon btn-round btn-primary edit-sample-btn text-white mb-1 ps-1" data-toggle="modal" data-target="#editSampleModal"
                                        data-id="<?php echo $sample['sample_id']; ?>"
                                        data-type="<?php echo htmlspecialchars($sample['sample_type']); ?>"
                                        data-population="<?php echo htmlspecialchars($sample['population_size']); ?>"
                                        data-sample="<?php echo htmlspecialchars($sample['sample_size']); ?>"
                                        data-details="<?php echo htmlspecialchars($sample['sampling_method_details']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                    
                                      <a href="audit_sampling.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $sample['sample_id']; ?>" class="btn btn-icon btn-round btn-danger text-white" onclick="return confirm('Are you sure you want to delete this audit sample?');"><i class="fas fa-trash"></i></a>
                                    
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
                  <a href="engagement_details.php?engagement_id=<?php echo $engagement_id; ?>" class="btn btn-icon btn-round btn-secondary"><i class="fas fa-arrow-left"></i></a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <!-- Edit Audit Sample Modal -->
      <div class="modal fade" id="editSampleModal" tabindex="-1" aria-labelledby="editSampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editSampleModalLabel">Edit Audit Sample</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="audit_sampling.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="sample_id" id="edit_sample_id">
                <div class="mb-3">
                  <select class="form-control" id="edit_sample_type" name="sample_type" required>
                    <option value="Random">Sample Type</option>
                    <option value="Random">Random</option>
                    <option value="Systematic">Systematic</option>
                    <option value="Stratified">Stratified</option>
                    <option value="Monetary Unit">Monetary Unit</option>
                  </select>
                </div>
                <div class="mb-3">
                  <input type="number" class="form-control" id="edit_population_size" placeholder="Population Size" name="population_size" required>
                </div>
                <div class="mb-3">
                  <input type="number" class="form-control" id="edit_sample_size" placeholder="Sample Size" name="sample_size" required>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_sampling_method_details" placeholder="Sampling Method Details" name="sampling_method_details" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
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
    $('#editSampleModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var type = button.data('type');
      var population = button.data('population');
      var sample = button.data('sample');
      var details = button.data('details');

      var modal = $(this);
      modal.find('.modal-title').text('Edit Audit Sample: ' + id);
      modal.find('#edit_sample_id').val(id);
      modal.find('#edit_sample_type').val(type);
      modal.find('#edit_population_size').val(population);
      modal.find('#edit_sample_size').val(sample);
      modal.find('#edit_sampling_method_details').val(details);
    });
  </script>
</body>

</html>