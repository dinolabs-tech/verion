<?php

/**
 * File: audit_adjustments.php
 * Purpose: This file allows Auditors and Admins to post and manage audit adjustments for a specific engagement.
 * It retrieves engagement details, handles adding, editing, and deleting adjustments, and displays a list of adjustments.
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

// Handle Adding, Editing, and Deleting Audit Adjustments
// Check if the request method is POST and if an engagement is selected.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $engagement) {
  // Check if the 'action' parameter is set.
  if (isset($_POST['action'])) {
    // Retrieve the action to be performed (add or edit).
    $action = $_POST['action'];
    // Retrieve the form data.
    $account_code = $_POST['account_code'] ?? '';
    $description = $_POST['description'] ?? '';
    $debit = $_POST['debit'] ?? 0;
    $credit = $_POST['credit'] ?? 0;
    // Get the ID of the user posting the adjustment.
    $posted_by_user_id = $_SESSION['user_id'];

    // Validate the input data.
    if (empty($account_code) || empty($description) || (empty($debit) && empty($credit))) {
      $error_message = "Account Code, Description, and either Debit or Credit are required.";
    } elseif ($debit > 0 && $credit > 0) {
      $error_message = "An adjustment cannot have both a debit and a credit amount simultaneously.";
    } else {
      // Perform the appropriate action based on the 'action' parameter.
      if ($action === 'add') {
        // Prepare a SQL statement to insert a new audit adjustment.
        $stmt = $conn->prepare("INSERT INTO audit_adjustments (engagement_id, account_code, description, debit, credit, posted_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddi", $engagement_id, $account_code, $description, $debit, $credit, $posted_by_user_id);
        // Execute the statement and display a success or error message.
        if ($stmt->execute()) {
          $success_message = "Audit adjustment posted successfully!";
        } else {
          $error_message = "Error posting audit adjustment: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        // Retrieve the adjustment ID.
        $adjustment_id = $_POST['adjustment_id'] ?? 0;
        // Prepare a SQL statement to update an existing audit adjustment.
        $stmt = $conn->prepare("UPDATE audit_adjustments SET account_code = ?, description = ?, debit = ?, credit = ?, posted_by_user_id = ? WHERE adjustment_id = ? AND engagement_id = ?");
        $stmt->bind_param("ssddiii", $account_code, $description, $debit, $credit, $posted_by_user_id, $adjustment_id, $engagement_id);
        // Execute the statement and display a success or error message.
        if ($stmt->execute()) {
          $success_message = "Audit adjustment updated successfully!";
        } else {
          $error_message = "Error updating audit adjustment: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}

// Handle Delete Audit Adjustment
// Check if a delete request is made.
if (isset($_GET['delete_id']) && $engagement) {
  // Retrieve the adjustment ID to be deleted.
  $adjustment_id = $_GET['delete_id'];
  // Prepare a SQL statement to delete the audit adjustment.
  $stmt = $conn->prepare("DELETE FROM audit_adjustments WHERE adjustment_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $adjustment_id, $engagement_id);
  // Execute the statement and display a success or error message.
  if ($stmt->execute()) {
    $success_message = "Audit adjustment deleted successfully!";
  } else {
    $error_message = "Error deleting audit adjustment: " . $conn->error;
  }
  $stmt->close();
  // Redirect to the same page with success or error messages.
  header("Location: audit_adjustments.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing audit adjustments for this engagement
$adjustments = [];
// If engagement details were successfully retrieved, fetch the audit adjustments.
if ($engagement) {
  // Execute a query to fetch audit adjustments.
  $result = $conn->query("SELECT aa.*, u.username FROM audit_adjustments aa JOIN users u ON aa.posted_by_user_id = u.user_id WHERE aa.engagement_id = $engagement_id ORDER BY aa.posted_at DESC");
  // Store the audit adjustments in an array.
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $adjustments[] = $row;
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
                <h1 class="mb-4">Audit Adjustments for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Post New Adjustment Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Post New Adjustment</h4>
                  </div>
                  <div class="card-body">
                    <form action="audit_adjustments.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="col-md-6  mt-4">
                          <input type="text" class="form-control" id="account_code" placeholder="Account Code" name="account_code" required>
                        </div>
                        <div class=" col-md-6  mb-3">
                          <textarea class="form-control" id="description" placeholder="Description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="col-md-6 mt-1">
                          <input type="number" step="0.01" class="form-control" id="debit" placeholder="Debit Amount (0.00)" name="debit" >
                        </div>
                        <div class="col-md-6 mt-1">
                          <input type="number" class="form-control" id="credit" name="credit" placeholder="Credit Amount (0.00) ">
                        </div>
                        <div class="col-md-12  mb-3" style="text-align: center;">
                          <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-plus"></i></button>
                        </div>

                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Audit Adjustments -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Audit Adjustments</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($adjustments)): ?>
                      <p>No audit adjustments found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Account Code</th>
                              <th>Description</th>
                              <th>Debit</th>
                              <th>Credit</th>
                              <th>Posted By</th>
                              <th>Posted At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($adjustments as $adjustment): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($adjustment['adjustment_id']); ?></td>
                                <td><?php echo htmlspecialchars($adjustment['account_code']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($adjustment['description'])); ?></td>
                                <td><?php echo htmlspecialchars(number_format($adjustment['debit'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(number_format($adjustment['credit'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($adjustment['username']); ?></td>
                                <td><?php echo htmlspecialchars($adjustment['posted_at']); ?></td>
                                <td class="d-flex">
                                  
                                
                                      <button type="button" class="btn btn-icon btn-round btn-primary edit-adjustment-btn text-white mb-1 ps-1" data-bs-toggle="modal" data-bs-target="#editAdjustmentModal"
                                        data-id="<?php echo $adjustment['adjustment_id']; ?>"
                                        data-account-code="<?php echo htmlspecialchars($adjustment['account_code']); ?>"
                                        data-description="<?php echo htmlspecialchars($adjustment['description']); ?>"
                                        data-debit="<?php echo htmlspecialchars($adjustment['debit']); ?>"
                                        data-credit="<?php echo htmlspecialchars($adjustment['credit']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                    
                                    
                                      <a href="audit_adjustments.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $adjustment['adjustment_id']; ?>" class="btn btn-icon btn-round btn-danger text-white" onclick="return confirm('Are you sure you want to delete this adjustment?');"><i class="fas fa-trash"></i></a>
                                   
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
      <!-- Edit Adjustment Modal -->
      <div class="modal fade" id="editAdjustmentModal" tabindex="-1" aria-labelledby="editAdjustmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editAdjustmentModalLabel">Edit Audit Adjustment</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="audit_adjustments.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="adjustment_id" id="edit_adjustment_id">
                <div class="mb-3">
                  <label for="edit_account_code" class="form-label"></label>
                  <input type="text" class="form-control" id="edit_account_code" placeholder="Account Code" name="account_code" required>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_description" placeholder="Description" name="description" rows="3" required></textarea>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <input type="number" step="0.01" class="form-control" placeholder="Debit Amount" id="edit_debit" name="debit">
                  </div>
                  <div class="col-md-6 mb-3">
                    <input type="number" step="0.01" class="form-control" placeholder="Credit Amount" id="edit_credit" name="credit">
                  </div>
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
    $('#editAdjustmentModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var accountCode = button.data('account-code');
      var description = button.data('description');
      var debit = button.data('debit');
      var credit = button.data('credit');

      var modal = $(this);
      modal.find('.modal-title').text('Edit Adjustment (ID: ' + id + ')');
      modal.find('#edit_adjustment_id').val(id);
      modal.find('#edit_account_code').val(accountCode);
      modal.find('#edit_description').val(description);
      modal.find('#edit_debit').val(debit);
      modal.find('#edit_credit').val(credit);
    });
  </script>
</body>

</html>