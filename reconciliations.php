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
    $reconciliation_type = $_POST['reconciliation_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $reconciled_amount = $_POST['reconciled_amount'] ?? 0;
    $difference = $_POST['difference'] ?? 0;
    $status = $_POST['status'] ?? 'Pending';
    $reconciled_by_user_id = $_SESSION['user_id'];

    if (empty($reconciliation_type) || empty($description) || empty($reconciled_amount) || empty($status)) {
      $error_message = "Reconciliation Type, Description, Reconciled Amount, and Status are required.";
    } else {
      if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO reconciliations (engagement_id, reconciliation_type, description, reconciled_amount, difference, status, reconciled_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddsi", $engagement_id, $reconciliation_type, $description, $reconciled_amount, $difference, $status, $reconciled_by_user_id);
        if ($stmt->execute()) {
          $success_message = "Reconciliation added successfully!";
        } else {
          $error_message = "Error adding reconciliation: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        $reconciliation_id = $_POST['reconciliation_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE reconciliations SET reconciliation_type = ?, description = ?, reconciled_amount = ?, difference = ?, status = ?, reconciled_by_user_id = ?, updated_at = CURRENT_TIMESTAMP WHERE reconciliation_id = ? AND engagement_id = ?");
        $stmt->bind_param("ssddsiii", $reconciliation_type, $description, $reconciled_amount, $difference, $status, $reconciled_by_user_id, $reconciliation_id, $engagement_id);
        if ($stmt->execute()) {
          $success_message = "Reconciliation updated successfully!";
        } else {
          $error_message = "Error updating reconciliation: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Reconciliation
if (isset($_GET['delete_id']) && $engagement) {
  $reconciliation_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM reconciliations WHERE reconciliation_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $reconciliation_id, $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Reconciliation deleted successfully!";
  } else {
    $error_message = "Error deleting reconciliation: " . $conn->error;
  }
  $stmt->close();
  header("Location: reconciliations.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing reconciliations for this engagement
$reconciliations = [];
if ($engagement) {
  $result = $conn->query("SELECT r.*, u.username FROM reconciliations r JOIN users u ON r.reconciled_by_user_id = u.user_id WHERE r.engagement_id = $engagement_id ORDER BY r.reconciled_at DESC");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $reconciliations[] = $row;
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
                <h1 class="mb-4">Reconciliations for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Add New Reconciliation Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Add New Reconciliation</h4>
                  </div>
                  <div class="card-body">
                    <form action="reconciliations.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control" id="reconciliation_type" name="reconciliation_type">
                            <option value="" selected disabled>Select Reconciliation Type</option>
                            <option value="Bank">Bank</option>
                            <option value="Debtor">Debtor</option>
                            <option value="Creditor">Creditor</option>
                            <option value="Inventory">Inventory</option>
                            <option value="Other">Other</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-6">
                          <textarea class="form-control" id="description" placeholder="Description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3 col-md-6">
                          <input type="number" step="0.01" class="form-control" placeholder="Reconciled Amount" id="reconciled_amount" name="reconciled_amount" required>
                        </div>
                        <div class="mb-3 col-md-6">

                          <input type="number" placeholder="Difference (0.00)" class="form-control" step="0.01" id="difference" name="difference">
                        </div>
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control" id="status" name="status" required>
                            <option value="" selected disabled>Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Reconciled">Reconciled</option>
                            <option value="Discrepancy">Discrepancy</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-plus"></i></button>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Reconciliations -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Reconciliations</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($reconciliations)): ?>
                      <p>No reconciliations found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Type</th>
                              <th>Description</th>
                              <th>Reconciled Amount</th>
                              <th>Difference</th>
                              <th>Status</th>
                              <th>Reconciled By</th>
                              <th>Reconciled At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($reconciliations as $reconciliation): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($reconciliation['reconciliation_id']); ?></td>
                                <td><?php echo htmlspecialchars($reconciliation['reconciliation_type']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($reconciliation['description'])); ?></td>
                                <td><?php echo htmlspecialchars(number_format($reconciliation['reconciled_amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(number_format($reconciliation['difference'], 2)); ?></td>
                                <td><span class="badge bg-<?php echo ($reconciliation['status'] === 'Pending' ? 'warning' : ($reconciliation['status'] === 'Reconciled' ? 'success' : 'danger')); ?>"><?php echo htmlspecialchars($reconciliation['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($reconciliation['username']); ?></td>
                                <td><?php echo htmlspecialchars($reconciliation['reconciled_at']); ?></td>
                                <td class="d-flex">
                                  
                                      <button type="button" class="btn btn-icon btn-round btn-primary text-white me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editReconciliationModal"
                                        data-id="<?php echo $reconciliation['reconciliation_id']; ?>"
                                        data-type="<?php echo htmlspecialchars($reconciliation['reconciliation_type']); ?>"
                                        data-description="<?php echo htmlspecialchars($reconciliation['description']); ?>"
                                        data-amount="<?php echo htmlspecialchars($reconciliation['reconciled_amount']); ?>"
                                        data-difference="<?php echo htmlspecialchars($reconciliation['difference']); ?>"
                                        data-status="<?php echo htmlspecialchars($reconciliation['status']); ?>">
                                        <i class="fas fa-edit"></i>
                                   
                                      <a href="reconciliations.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $reconciliation['reconciliation_id']; ?>" class="btn btn-icon btn-round btn-danger text-white" onclick="return confirm('Are you sure you want to delete this reconciliation?');"><i class="fas fa-trash"></i></a>
                                    
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
      <div class="modal fade" id="editReconciliationModal" tabindex="-1" aria-labelledby="editReconciliationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editReconciliationModalLabel">Edit Reconciliation</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="reconciliations.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="reconciliation_id" id="edit_reconciliation_id">
                <div class="mb-3">
                  <select class="form-select" id="edit_reconciliation_type" name="reconciliation_type" required>
                    <option value="">Reconciliation Type</option>
                    <option value="Bank">Bank</option>
                    <option value="Debtor">Debtor</option>
                    <option value="Creditor">Creditor</option>
                    <option value="Inventory">Inventory</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_description" placeholder="Description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <input type="number" step="0.01" class="form-control" placeholder="Reconciled Amount" id="edit_reconciled_amount" name="reconciled_amount" required>
                </div>
                <div class="mb-3">
                  <input type="number" step="0.01" class="form-control" placeholder="Difference" id="edit_difference" name="difference">
                </div>
                <div class="mb-3">
                  <select class="form-select" id="edit_status" name="status" required >
                    <option value="">Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Reconciled">Reconciled</option>
                    <option value="Discrepancy">Discrepancy</option>
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
    var editReconciliationModal = document.getElementById('editReconciliationModal');
    editReconciliationModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      var id = button.getAttribute('data-id');
      var type = button.getAttribute('data-type');
      var description = button.getAttribute('data-description');
      var amount = button.getAttribute('data-amount');
      var difference = button.getAttribute('data-difference');
      var status = button.getAttribute('data-status');

      var modalTitle = editReconciliationModal.querySelector('.modal-title');
      var reconciliationIdInput = editReconciliationModal.querySelector('#edit_reconciliation_id');
      var reconciliationTypeSelect = editReconciliationModal.querySelector('#edit_reconciliation_type');
      var descriptionTextarea = editReconciliationModal.querySelector('#edit_description');
      var reconciledAmountInput = editReconciliationModal.querySelector('#edit_reconciled_amount');
      var differenceInput = editReconciliationModal.querySelector('#edit_difference');
      var statusSelect = editReconciliationModal.querySelector('#edit_status');

      modalTitle.textContent = 'Edit Reconciliation (ID: ' + id + ')';
      reconciliationIdInput.value = id;
      reconciliationTypeSelect.value = type;
      descriptionTextarea.value = description;
      reconciledAmountInput.value = amount;
      differenceInput.value = difference;
      statusSelect.value = status;
    });
  </script>
</body>

</html>