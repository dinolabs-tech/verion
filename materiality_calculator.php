<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin')) {
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
    $basis = $_POST['basis'] ?? '';
    $percentage = $_POST['percentage'] ?? 0;
    $calculated_amount = $_POST['calculated_amount'] ?? 0;
    $performance_materiality = $_POST['performance_materiality'] ?? 0;
    $trivial_amount = $_POST['trivial_amount'] ?? 0;
    $calculated_by_user_id = $_SESSION['user_id'];

    if (empty($basis) || empty($percentage) || empty($calculated_amount) || empty($performance_materiality) || empty($trivial_amount)) {
      $error_message = "All fields are required.";
    } else {
      if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO materiality_calculations (engagement_id, basis, percentage, calculated_amount, performance_materiality, trivial_amount, calculated_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddddi", $engagement_id, $basis, $percentage, $calculated_amount, $performance_materiality, $trivial_amount, $calculated_by_user_id);
        if ($stmt->execute()) {
          $success_message = "Materiality calculation added successfully!";
        } else {
          $error_message = "Error adding materiality calculation: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        $materiality_id = $_POST['materiality_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE materiality_calculations SET basis = ?, percentage = ?, calculated_amount = ?, performance_materiality = ?, trivial_amount = ?, calculated_by_user_id = ?, updated_at = CURRENT_TIMESTAMP WHERE materiality_id = ?");
        $stmt->bind_param("sddddii", $basis, $percentage, $calculated_amount, $performance_materiality, $trivial_amount, $calculated_by_user_id, $materiality_id);
        if ($stmt->execute()) {
          $success_message = "Materiality calculation updated successfully!";
        } else {
          $error_message = "Error updating materiality calculation: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Materiality Calculation
if (isset($_GET['delete_id']) && $engagement) {
  $materiality_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM materiality_calculations WHERE materiality_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $materiality_id, $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Materiality calculation deleted successfully!";
  } else {
    $error_message = "Error deleting materiality calculation: " . $conn->error;
  }
  $stmt->close();
  header("Location: materiality_calculator.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing materiality calculations for this engagement
$materiality_calculations = [];
if ($engagement) {
  $result = $conn->query("SELECT mc.*, u.username FROM materiality_calculations mc JOIN users u ON mc.calculated_by_user_id = u.user_id WHERE mc.engagement_id = $engagement_id ORDER BY mc.calculated_at DESC");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $materiality_calculations[] = $row;
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
                <h1 class="mb-4">Materiality Calculator for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Add New Materiality Calculation Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Add New Materiality Calculation</h4>
                  </div>
                  <div class="card-body">
                    <form action="materiality_calculator.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="mb-3 col-md-6">
                          <input type="text" class="form-control" id="basis" name="basis" placeholder="Basis e.g., Revenue, Total Assets">
                        </div>
                        <div class="mb-3 col-md-6">
                          <input type="number" step="0.01" class="form-control" placeholder="Percentage (%)" id="percentage" name="percentage">
                        </div>
                        <div class="mb-3 col-md-6">
                          <input type="number" step="0.01" class="form-control" placeholder="Calculated Amount" id="calculated_amount" name="calculated_amount">
                        </div>
                        <div class="mb-3 col-md-6">
                          <input type="number" step="0.01" placeholder="Performance Materiality" class="form-control" id="performance_materiality" name="performance_materiality">
                        </div>
                        <div class="mb-3 col-md-6">
                          <label for="trivial_amount" class="form-label"></label>
                          <input type="number" step="0.01" placeholder="Trivial Amount" class="form-control" id="trivial_amount" name="trivial_amount">
                        </div>
                        <div class="mb-3 col-md-6">
                          <button type="submit" class="btn btn-primary btn-icon btn-round mt-4"><i class="fas fa-plus"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Materiality Calculations -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Materiality Calculations</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($materiality_calculations)): ?>
                      <p>No materiality calculations found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Basis</th>
                              <th>Percentage</th>
                              <th>Calculated Amount</th>
                              <th>Performance Materiality</th>
                              <th>Trivial Amount</th>
                              <th>Calculated By</th>
                              <th>Calculated At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($materiality_calculations as $calc): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($calc['materiality_id']); ?></td>
                                <td><?php echo htmlspecialchars($calc['basis']); ?></td>
                                <td><?php echo htmlspecialchars($calc['percentage']); ?>%</td>
                                <td><?php echo htmlspecialchars(number_format($calc['calculated_amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(number_format($calc['performance_materiality'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(number_format($calc['trivial_amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($calc['username']); ?></td>
                                <td><?php echo htmlspecialchars($calc['calculated_at']); ?></td>
                                <td class="d-flex">
                                 
                                      <button type="button" class="btn btn-icon btn-round btn-primary text-white mb-1 me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editMaterialityModal"
                                        data-id="<?php echo $calc['materiality_id']; ?>"
                                        data-basis="<?php echo htmlspecialchars($calc['basis']); ?>"
                                        data-percentage="<?php echo htmlspecialchars($calc['percentage']); ?>"
                                        data-calculated-amount="<?php echo htmlspecialchars($calc['calculated_amount']); ?>"
                                        data-performance-materiality="<?php echo htmlspecialchars($calc['performance_materiality']); ?>"
                                        data-trivial-amount="<?php echo htmlspecialchars($calc['trivial_amount']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                    
                                      <a href="materiality_calculator.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $calc['materiality_id']; ?>" class=" text-white btn btn-icon btn-round btn-danger" onclick="return confirm('Are you sure you want to delete this calculation?');"><i class="fas fa-trash"></i></a>
                                    
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
      <!-- Edit Materiality Modal -->
      <div class="modal fade" id="editMaterialityModal" tabindex="-1" aria-labelledby="editMaterialityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editMaterialityModalLabel">Edit Materiality Calculation</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="materiality_calculator.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="materiality_id" id="edit_materiality_id">
                <div class="mb-3">
                  <input type="text" class="form-control" placeholder="Basis" id="edit_basis" name="basis" required>
                </div>
                <div class="mb-3">
                  <input type="number" step="0.01" class="form-control" placeholder="Percentage (%)" id="edit_percentage" name="percentage" required>
                </div>
                <div class="mb-3">
                  <label for="edit_calculated_amount" class="form-label">Calculated Amount</label>
                  <input type="number" step="0.01" class="form-control" id="edit_calculated_amount" name="calculated_amount" required>
                </div>
                <div class="mb-3">
                  <input type="number" step="0.01" class="form-control" placeholder="Performance Materiality" id="edit_performance_materiality" name="performance_materiality" required>
                </div>
                <div class="mb-3">
                  <input type="number" step="0.01" placeholder="Trivial Amount" class="form-control" id="edit_trivial_amount" name="trivial_amount" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i>
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
    $('#editMaterialityModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var basis = button.data('basis');
      var percentage = button.data('percentage');
      var calculatedAmount = button.data('calculated-amount');
      var performanceMateriality = button.data('performance-materiality');
      var trivialAmount = button.data('trivial-amount');

      var modal = $(this);
      modal.find('.modal-title').text('Edit Materiality Calculation: ' + basis);
      modal.find('#edit_materiality_id').val(id);
      modal.find('#edit_basis').val(basis);
      modal.find('#edit_percentage').val(percentage);
      modal.find('#edit_calculated_amount').val(calculatedAmount);
      modal.find('#edit_performance_materiality').val(performanceMateriality);
      modal.find('#edit_trivial_amount').val(trivialAmount);
    });
  </script>
</body>

</html>