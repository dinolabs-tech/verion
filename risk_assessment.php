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
    $risk_area = $_POST['risk_area'] ?? '';
    $inherent_risk = $_POST['inherent_risk'] ?? '';
    $control_risk = $_POST['control_risk'] ?? '';
    $detection_risk = $_POST['detection_risk'] ?? '';
    $overall_risk = $_POST['overall_risk'] ?? '';
    $mitigation_strategy = $_POST['mitigation_strategy'] ?? '';
    $assessed_by_user_id = $_SESSION['user_id'];

    if (empty($risk_area) || empty($inherent_risk) || empty($control_risk) || empty($detection_risk) || empty($overall_risk)) {
      $error_message = "All risk fields are required.";
    } else {
      if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO risk_assessment (engagement_id, risk_area, inherent_risk, control_risk, detection_risk, overall_risk, mitigation_strategy, assessed_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $engagement_id, $risk_area, $inherent_risk, $control_risk, $detection_risk, $overall_risk, $mitigation_strategy, $assessed_by_user_id);
        if ($stmt->execute()) {
          $success_message = "Risk assessment added successfully!";
        } else {
          $error_message = "Error adding risk assessment: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        $risk_id = $_POST['risk_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE risk_assessment SET risk_area = ?, inherent_risk = ?, control_risk = ?, detection_risk = ?, overall_risk = ?, mitigation_strategy = ?, assessed_by_user_id = ?, updated_at = CURRENT_TIMESTAMP WHERE risk_id = ?");
        $stmt->bind_param("ssssssss", $risk_area, $inherent_risk, $control_risk, $detection_risk, $overall_risk, $mitigation_strategy, $assessed_by_user_id, $risk_id);
        if ($stmt->execute()) {
          $success_message = "Risk assessment updated successfully!";
        } else {
          $error_message = "Error updating risk assessment: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Risk
if (isset($_GET['delete_id']) && $engagement) {
  $risk_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM risk_assessment WHERE risk_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $risk_id, $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Risk assessment deleted successfully!";
  } else {
    $error_message = "Error deleting risk assessment: " . $conn->error;
  }
  $stmt->close();
  header("Location: risk_assessment.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing risk assessments for this engagement
$risks = [];
if ($engagement) {
  $result = $conn->query("SELECT ra.*, u.username FROM risk_assessment ra JOIN users u ON ra.assessed_by_user_id = u.user_id WHERE ra.engagement_id = $engagement_id ORDER BY ra.assessed_at DESC");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $risks[] = $row;
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
                <a href="my_engagements.php" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Risk Assessment for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Add New Risk Assessment Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Add New Risk Assessment</h4>
                  </div>
                  <div class="card-body">


                    <form action="risk_assessment.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="mb-3 col-md-6">
                          <input type="text" placeholder="Risk Area" class="form-control" id="risk_area" name="risk_area">
                        </div>
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control" id="inherent_risk" name="inherent_risk">
                            <option value="" selected disabled> Select Inherent Risk</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Significant">Significant</option>
                          </select>
                        </div>
                        <div class=" mb-3 col-md-6">
                          <select class="form-select form-control" id="control_risk" name="control_risk">
                            <option value="" selected disabled>Select Control risk</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control" id="detection_risk" name="detection_risk">
                            <option value="" selected disabled>Select Detection Risk</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control" id="overall_risk" name="overall_risk">
                            <option value="" selected disabled>Select Overall Risk</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Significant">Significant</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-6">
                          <textarea class="form-control" placeholder="Mitigation Strategy" id="mitigation_strategy" name="mitigation_strategy" rows="3"></textarea>
                        </div>
                        <div class="col-md-11 ms-2" style="text-align: center;">
                          <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-plus"></i></button>
                        </div>
                      </div>
                    </form>

                  </div>
                </div>

                <!-- List of Risk Assessments -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Risk Assessments</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($risks)): ?>
                      <p>No risk assessments found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Risk Area</th>
                              <th>Inherent</th>
                              <th>Control</th>
                              <th>Detection</th>
                              <th>Overall</th>
                              <th>Mitigation Strategy</th>
                              <th>Assessed By</th>
                              <th>Assessed At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($risks as $risk): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($risk['risk_id']); ?></td>
                                <td><?php echo htmlspecialchars($risk['risk_area']); ?></td>
                                <td><?php echo htmlspecialchars($risk['inherent_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['control_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['detection_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['overall_risk']); ?></td>
                                <td><?php echo htmlspecialchars($risk['mitigation_strategy']); ?></td>
                                <td><?php echo htmlspecialchars($risk['username']); ?></td>
                                <td><?php echo htmlspecialchars($risk['assessed_at']); ?></td>

                                <td class="d-flex">
                                  
                                      <button type="button" class="btn  btn-primary edit-risk-btn btn-icon btn-round text-white mb-1 me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editRiskModal"
                                        data-id="<?php echo $risk['risk_id']; ?>"
                                        data-area="<?php echo htmlspecialchars($risk['risk_area']); ?>"
                                        data-inherent="<?php echo htmlspecialchars($risk['inherent_risk']); ?>"
                                        data-control="<?php echo htmlspecialchars($risk['control_risk']); ?>"
                                        data-detection="<?php echo htmlspecialchars($risk['detection_risk']); ?>"
                                        data-overall="<?php echo htmlspecialchars($risk['overall_risk']); ?>"
                                        data-mitigation="<?php echo htmlspecialchars($risk['mitigation_strategy']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                    
                                      <a href="risk_assessment.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $risk['risk_id']; ?>" class="btn btn-icon btn-round btn-danger text-white" onclick="return confirm('Are you sure you want to delete this risk assessment?');"> <i class="fas fa-trash"></i></a>
                                    
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

      <!-- Edit Risk Modal -->
      <div class="modal fade" id="editRiskModal" tabindex="-1" aria-labelledby="editRiskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editRiskModalLabel">Edit Risk Assessment</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="risk_assessment.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="risk_id" id="edit_risk_id">
                <div class="mb-3">
                  <input type="text" placeholder="Risk Area" class="form-control" id="edit_risk_area" name="risk_area" required>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_inherent_risk" name="inherent_risk" required>
                    <option value="" >Inherent Risk</option>
                  <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Significant">Significant</option>
                  </select>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_control_risk" name="control_risk" required>
                    <option value="">Control Risk</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                  </select>
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_detection_risk" name="detection_risk" required>
                    <option value="">Detection Risk</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="edit_overall_risk" class="form-label"></label>
                  <select class="form-control" id="edit_overall_risk" name="overall_risk" required>
                    <option value="">Overall Risk</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Significant">Significant</option>
                  </select>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" placeholder="Mitigation Strategy" id="edit_mitigation_strategy" name="mitigation_strategy" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary  btn-icon btn-round"><i class="fas fa-save"></i></button>
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
    $('#editRiskModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var area = button.data('area');
      var inherent = button.data('inherent');
      var control = button.data('control');
      var detection = button.data('detection');
      var overall = button.data('overall');
      var mitigation = button.data('mitigation');

      var modal = $(this);
      modal.find('.modal-title').text('Edit Risk Assessment: ' + area);
      modal.find('#edit_risk_id').val(id);
      modal.find('#edit_risk_area').val(area);
      modal.find('#edit_inherent_risk').val(inherent);
      modal.find('#edit_control_risk').val(control);
      modal.find('#edit_detection_risk').val(detection);
      modal.find('#edit_overall_risk').val(overall);
      modal.find('#edit_mitigation_strategy').val(mitigation);
    });
  </script>
</body>

</html>