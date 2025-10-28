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
    $standard_type = $_POST['standard_type'] ?? '';
    $item_description = $_POST['item_description'] ?? '';
    $is_compliant = isset($_POST['is_compliant']) ? 1 : 0;
    $notes = $_POST['notes'] ?? '';
    $reviewed_by_user_id = $_SESSION['user_id'];

    if (empty($standard_type) || empty($item_description)) {
      $error_message = "Standard Type and Item Description are required.";
    } else {
      if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO compliance_checklists (engagement_id, standard_type, item_description, is_compliant, notes, reviewed_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issisi", $engagement_id, $standard_type, $item_description, $is_compliant, $notes, $reviewed_by_user_id);
        if ($stmt->execute()) {
          $success_message = "Checklist item added successfully!";
        } else {
          $error_message = "Error adding checklist item: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        $checklist_id = $_POST['checklist_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE compliance_checklists SET standard_type = ?, item_description = ?, is_compliant = ?, notes = ?, reviewed_by_user_id = ?, reviewed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE checklist_id = ? AND engagement_id = ?");
        $stmt->bind_param("ssisiii", $standard_type, $item_description, $is_compliant, $notes, $reviewed_by_user_id, $checklist_id, $engagement_id);
        if ($stmt->execute()) {
          $success_message = "Checklist item updated successfully!";
        } else {
          $error_message = "Error updating checklist item: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Checklist Item
if (isset($_GET['delete_id']) && $engagement) {
  $checklist_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM compliance_checklists WHERE checklist_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $checklist_id, $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Checklist item deleted successfully!";
  } else {
    $error_message = "Error deleting checklist item: " . $conn->error;
  }
  $stmt->close();
  header("Location: compliance_checklists.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing compliance checklists for this engagement
$checklists = [];
if ($engagement) {
  $result = $conn->query("SELECT cc.*, u.username FROM compliance_checklists cc LEFT JOIN users u ON cc.reviewed_by_user_id = u.user_id WHERE cc.engagement_id = $engagement_id ORDER BY cc.standard_type, cc.item_description");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $checklists[] = $row;
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
                <h1 class="mb-4">Compliance Checklists for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Add New Checklist Item Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Add New Compliance Checklist Item</h4>
                  </div>
                  <div class="card-body">
                    <form action="compliance_checklists.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class=" col-md-6">
                          <select class="form-select form-control mb-1" id="standard_type" name="standard_type" required>
                            <option value="" selected disabled>Select Standard Type</option>
                            <option value="IFRS">IFRS</option>
                            <option value="GAAP">GAAP</option>
                            <option value="Internal Controls">Internal Controls</option>
                            <option value="Custom">Custom</option>
                          </select>
                        </div>
                        <div class=" col-md-6">
                          <textarea class="form-control" placeholder="Item Description" id="item_description" name="item_description" rows="3" required></textarea>
                        </div>
                        <div class="   form-check">
                          <input type="checkbox" class="form-check-input" id="is_compliant" name="is_compliant" value="1">
                          <label class="form-check-label" for="is_compliant">Is Compliant?</label>
                        </div>
                        <div class=" col-md-6">
                          <textarea class="form-control" id="notes" placeholders="Notes" name="notes" rows="3"></textarea>
                        </div>


                        <div class="mt-5 col-md-6">
                          <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-plus"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Compliance Checklists -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Compliance Checklists</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($checklists)): ?>
                      <p>No compliance checklist items found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Standard Type</th>
                              <th>Description</th>
                              <th>Compliant?</th>
                              <th>Notes</th>
                              <th>Reviewed By</th>
                              <th>Reviewed At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($checklists as $item): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($item['checklist_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['standard_type']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($item['item_description'])); ?></td>
                                <td>
                                  <?php if ($item['is_compliant']): ?>
                                    <span class="badge bg-success">Yes</span>
                                  <?php else: ?>
                                    <span class="badge bg-danger">No</span>
                                  <?php endif; ?>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($item['notes'])); ?></td>
                                <td><?php echo htmlspecialchars($item['username'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['reviewed_at'] ?? 'N/A'); ?></td>
                                <td class="d-flex">
                                  

                                      <button type="button" class="btn btn-icon btn-round btn-primary text-white mb-1 me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editChecklistModal"
                                        data-id="<?php echo $item['checklist_id']; ?>"
                                        data-type="<?php echo htmlspecialchars($item['standard_type']); ?>"
                                        data-description="<?php echo htmlspecialchars($item['item_description']); ?>"
                                        data-compliant="<?php echo htmlspecialchars($item['is_compliant']); ?>"
                                        data-notes="<?php echo htmlspecialchars($item['notes']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                    
                                   
                                      <a href="compliance_checklists.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $item['checklist_id']; ?>" class="btn btn-icon btn-round btn-danger  text-white" onclick="return confirm('Are you sure you want to delete this checklist item?');"><i class="fas fa-trash"></i></a>
                                    
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
      <!-- Edit Checklist Modal -->
      <div class="modal fade" id="editChecklistModal" tabindex="-1" aria-labelledby="editChecklistModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editChecklistModalLabel">Edit Compliance Checklist Item</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action=" compliance_checklists.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="checklist_id" id="edit_checklist_id">
                <div class="mb-3">
                  <select class="form-select" id="edit_standard_type" name="standard_type" required>
                    <option value="" selected disabled>Select Standard Type</option>
                    <option value="IFRS">IFRS</option>
                    <option value="GAAP">GAAP</option>
                    <option value="Internal Controls">Internal Controls</option>
                    <option value="Custom">Custom</option>
                  </select>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_item_description" placeholder="Item Description" name="item_description" rows="3" required></textarea>
                </div>
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="edit_is_compliant" name="is_compliant" value="1">
                  <label class="form-check-label" for="edit_is_compliant">Is Compliant?</label>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_notes" placeholder="Notes" name="notes" rows="3"></textarea>
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
    var editChecklistModal = document.getElementById('editChecklistModal');
    editChecklistModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      var id = button.getAttribute('data-id');
      var type = button.getAttribute('data-type');
      var description = button.getAttribute('data-description');
      var compliant = button.getAttribute('data-compliant');
      var notes = button.getAttribute('data-notes');

      var modalTitle = editChecklistModal.querySelector('.modal-title');
      var checklistIdInput = editChecklistModal.querySelector('#edit_checklist_id');
      var standardTypeSelect = editChecklistModal.querySelector('#edit_standard_type');
      var itemDescriptionTextarea = editChecklistModal.querySelector('#edit_item_description');
      var isCompliantCheckbox = editChecklistModal.querySelector('#edit_is_compliant');
      var notesTextarea = editChecklistModal.querySelector('#edit_notes');

      modalTitle.textContent = 'Edit Checklist Item (ID: ' + id + ')';
      checklistIdInput.value = id;
      standardTypeSelect.value = type;
      itemDescriptionTextarea.value = description;
      isCompliantCheckbox.checked = (compliant == 1);
      notesTextarea.value = notes;
    });
  </script>
</body>

</html>