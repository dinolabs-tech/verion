<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser')) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $engagement) {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $control_name = $_POST['control_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $design_effectiveness = $_POST['design_effectiveness'] ?? NULL;
    $operating_effectiveness = $_POST['operating_effectiveness'] ?? NULL;
    $tested_by_user_id = $_SESSION['user_id'];

    if (empty($control_name) || empty($description)) {
      $error_message = "Control Name and Description are required.";
    } else {
      if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO internal_controls (engagement_id, control_name, description, design_effectiveness, operating_effectiveness, tested_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $engagement_id, $control_name, $description, $design_effectiveness, $operating_effectiveness, $tested_by_user_id);
        if ($stmt->execute()) {
          $success_message = "Internal control added successfully!";
        } else {
          $error_message = "Error adding internal control: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        $control_id = $_POST['control_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE internal_controls SET control_name = ?, description = ?, design_effectiveness = ?, operating_effectiveness = ?, tested_by_user_id = ?, tested_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE control_id = ? AND engagement_id = ?");
        $stmt->bind_param("ssssiii", $control_name, $description, $design_effectiveness, $operating_effectiveness, $tested_by_user_id, $control_id, $engagement_id);
        if ($stmt->execute()) {
          $success_message = "Internal control updated successfully!";
        } else {
          $error_message = "Error updating internal control: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Internal Control
if (isset($_GET['delete_id']) && $engagement) {
  $control_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM internal_controls WHERE control_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $control_id, $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Internal control deleted successfully!";
  } else {
    $error_message = "Error deleting internal control: " . $conn->error;
  }
  $stmt->close();
  header("Location: internal_controls.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
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
                <a href="my_engagements.php" class="btn btn-secondary">Back to My Engagements</a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Internal Controls for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Add New Internal Control Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Add New Internal Control</h4>
                  </div>
                  <div class="card-body">
                    <form action="internal_controls.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="col-md-6">
                          <input type="text" class="form-control" id="control_name" placeholder="Control Name" name="control_name" required>
                        </div>
                        <div class="mb-3 col-md-6">
                          <textarea class="form-control" id="description" placeholder="Description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6 mb-1">
                          <select class="form-select form-control" id="design_effectiveness" name="design_effectiveness">
                            <option value="" selected disabled>Select Design Effectiveness</option>
                            <option value="Effective">Effective</option>
                            <option value="Ineffective">Ineffective</option>
                          </select>
                        </div>
                        <div class="col-md-6 mb-1">
                          <select class="form-select form-control" id="operating_effectiveness" name="operating_effectiveness">
                            <option value="" selected disabled>Select Operating Effectiveness</option>
                            <option value="Effective">Effective</option>
                            <option value="Ineffective">Ineffective</option>
                          </select>
                        </div>

                        <div class="col-md-12 mb-1"style="text-align: center;">
                          <button type="submit" class="btn btn-primary btn-round btn-icon"><i class="fas fa-plus"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Internal Controls -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Internal Controls</h4>
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
                              <th>Actions</th>
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
                                <td class="d-flex">
                                  
                                   
                                      <button type="button" class="btn btn-icon btn-round btn-primary me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editControlModal"
                                        data-id="<?php echo $control['control_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($control['control_name']); ?>"
                                        data-description="<?php echo htmlspecialchars($control['description']); ?>"
                                        data-design="<?php echo htmlspecialchars($control['design_effectiveness']); ?>"
                                        data-operating="<?php echo htmlspecialchars($control['operating_effectiveness']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                    
                                      <a href="internal_controls.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $control['control_id']; ?>" class="btn btn-icon btn-round btn-danger" onclick="return confirm('Are you sure you want to delete this internal control?');"><i class="fas fa-trash"></i></a>
                                    
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
                  <a href="engagement_details.php?engagement_id=<?php echo $engagement_id; ?>" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <!-- Edit Internal Control Modal -->
      <div class="modal fade" id="editControlModal" tabindex="-1" aria-labelledby="editControlModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editControlModalLabel">Edit Internal Control</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="internal_controls.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="control_id" id="edit_control_id">
                <div class="mb-3">
                  <input type="text" class="form-control" id="edit_control_name" placeholder="Control Name" name="control_name" required>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_description" placeholder="Description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <select class="form-select" id="edit_design_effectiveness" name="design_effectiveness">
                    <option value="">Select Design Effectiveness </option>
                    <option value="Effective">Effective</option>
                    <option value="Ineffective">Ineffective</option>
                  </select>
                </div>
                <div class="mb-3">
                  <select class="form-select" id="edit_operating_effectiveness" name="operating_effectiveness">
                    <option value="">Select Operating Effectiveness</option>
                    <option value="Effective">Effective</option>
                    <option value="Ineffective">Ineffective</option>
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
    var editControlModal = document.getElementById('editControlModal');
    editControlModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      var id = button.getAttribute('data-id');
      var name = button.getAttribute('data-name');
      var description = button.getAttribute('data-description');
      var design = button.getAttribute('data-design');
      var operating = button.getAttribute('data-operating');

      var modalTitle = editControlModal.querySelector('.modal-title');
      var controlIdInput = editControlModal.querySelector('#edit_control_id');
      var controlNameInput = editControlModal.querySelector('#edit_control_name');
      var descriptionTextarea = editControlModal.querySelector('#edit_description');
      var designEffectivenessSelect = editControlModal.querySelector('#edit_design_effectiveness');
      var operatingEffectivenessSelect = editControlModal.querySelector('#edit_operating_effectiveness');

      modalTitle.textContent = 'Edit Internal Control: ' + name;
      controlIdInput.value = id;
      controlNameInput.value = name;
      descriptionTextarea.value = description;
      designEffectivenessSelect.value = design;
      operatingEffectivenessSelect.value = operating;
    });
  </script>
</body>

</html>