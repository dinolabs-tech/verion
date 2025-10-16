<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Auditor', 'Admin', 'Client', 'Reviewer'])) {
  header("Location: login.php");
  exit();
}

$engagement_id = $_GET['engagement_id'] ?? 0;
$engagement = null;
$success_message = '';
$error_message = '';


if ($raised_by_user_id = $_SESSION['user_id']) {
  $stmt = $conn->prepare("UPDATE queries SET response_text = ?, status = ?, responded_at = CASE WHEN ? = 'Responded' AND response_text IS NOT NULL THEN CURRENT_TIMESTAMP ELSE responded_at END, closed_at = CASE WHEN ? = 'Closed' THEN CURRENT_TIMESTAMP ELSE closed_at END WHERE query_id = ? AND engagement_id = ? AND raised_to_user_id = ?");
  $stmt->bind_param("ssssiii", $response_text, $status, $status, $status, $query_id, $engagement_id, $current_user_id);
}

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
    $query_text = $_POST['query_text'] ?? '';
    $raised_to_user_id = $_POST['raised_to_user_id'] ?? NULL;
    $raised_by_user_id = $_SESSION['user_id'];

    if (empty($query_text)) {
      $error_message = "Query text cannot be empty.";
    } else {
      if ($action === 'add') {
        $status = 'sent'; // Default status
        $stmt = $conn->prepare("INSERT INTO queries (engagement_id, raised_by_user_id, raised_to_user_id, query_text, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $engagement_id, $raised_by_user_id, $raised_to_user_id, $query_text, $status);
        if ($stmt->execute()) {
          $success_message = "Query raised successfully!";
        } else {
          $error_message = "Error raising query: " . $conn->error;
        }
        $stmt->close();
      } elseif ($action === 'edit') {
        $query_id = $_POST['query_id'] ?? 0;
        $response_text = $_POST['response_text'] ?? '';
        $status = $_POST['status'] ?? 'Open';

        $current_user_role = $_SESSION['role'];
        $current_user_id = $_SESSION['user_id'];

        // Fetch the existing query to check permissions
        $stmt_check = $conn->prepare("SELECT raised_to_user_id FROM queries WHERE query_id = ? AND engagement_id = ?");
        $stmt_check->bind_param("ii", $query_id, $engagement_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $existing_query = $result_check->fetch_assoc();
        $stmt_check->close();

        if (!$existing_query) {
          $error_message = "Query not found or you do not have permission to edit it.";
        } else {
          $can_edit_full = in_array($current_user_role, ['Auditor', 'Admin']);
          $can_respond = (in_array($current_user_role, ['Client', 'Reviewer']) && $current_user_id == $existing_query['raised_to_user_id']);

          if ($can_edit_full) {
            // Auditors and Admins can edit all fields
            $stmt = $conn->prepare("UPDATE queries SET query_text = ?, raised_to_user_id = ?, response_text = ?, status = ?, responded_at = CASE WHEN ? = 'Responded' AND response_text IS NOT NULL THEN CURRENT_TIMESTAMP ELSE responded_at END, closed_at = CASE WHEN ? = 'Closed' THEN CURRENT_TIMESTAMP ELSE closed_at END WHERE query_id = ? AND engagement_id = ?");
            $stmt->bind_param("sisssiis", $query_text, $raised_to_user_id, $response_text, $status, $status, $status, $query_id, $engagement_id);
          } elseif ($can_respond) {
            // Clients and Reviewers can only update response_text and status for queries raised to them
            $status = 'Responded';
            $stmt = $conn->prepare("UPDATE queries SET response_text = ?, status = ?, responded_at = NOW() WHERE query_id = ? AND engagement_id = ? AND raised_to_user_id = ?");
            $stmt->bind_param("ssiii", $response_text, $status, $query_id, $engagement_id, $current_user_id);
          } else {
            $error_message = "You do not have permission to edit this query.";
            $stmt = null; // Prevent execution if no permission
          }

          if ($stmt) {
            if ($stmt->execute()) {
              $success_message = "Query updated successfully!";
            } else {
              $error_message = "Error updating query: " . $conn->error;
            }
            $stmt->close();
          }
        }
      }
    }
  }
}

// Handle Delete Query
if (isset($_GET['delete_id']) && $engagement) {
  $query_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM queries WHERE query_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $query_id, $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Query deleted successfully!";
  } else {
    $error_message = "Error deleting query: " . $conn->error;
  }
  $stmt->close();
  header("Location: queries.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing queries for this engagement
$queries = [];
if ($engagement) {
  $user_role = $_SESSION['role'];
  $user_id = $_SESSION['user_id'];

  $sql = "SELECT q.*, u_raised.username AS raised_by_username, u_to.username AS raised_to_username
            FROM queries q
            JOIN users u_raised ON q.raised_by_user_id = u_raised.user_id
            LEFT JOIN users u_to ON q.raised_to_user_id = u_to.user_id
            WHERE q.engagement_id = ?";

  $params = [$engagement_id];
  $types = "i";

  if ($user_role === 'Client' || $user_role === 'Reviewer') {
    $sql .= " AND q.raised_to_user_id = ?";
    $params[] = $user_id;
    $types .= "i";
  }

  $sql .= " ORDER BY q.raised_at DESC";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $queries[] = $row;
    }
  }
  $stmt->close();
}

// Fetch users (Auditors, Reviewers, Clients) for the "Raised To" dropdown
$users_for_dropdown = [];
$users_result = $conn->query("SELECT user_id, username, role FROM users WHERE role IN ('Auditor', 'Reviewer', 'Client') ORDER BY username");
if ($users_result) {
  while ($row = $users_result->fetch_assoc()) {
    $users_for_dropdown[] = $row;
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
                <h1 class="mb-4">Queries & Responses for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <?php if (in_array($_SESSION['role'], ['Auditor', 'Admin'])): ?>
                  <!-- Raise New Query Form -->
                  <div class="card mb-4">
                    <div class="card-header">
                      <h4>Raise New Query</h4>
                    </div>
                    <div class="card-body">
                      <form action="queries.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                          <textarea class="form-control" placeholder="Query Text" id="query_text" name="query_text" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                          <select class="form-select form-control" id="raised_to_user_id" name="raised_to_user_id">
                            <option value=""> Raise To Select User (Optional)</option>
                            <?php foreach ($users_for_dropdown as $user): ?>
                              <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <button type="submit" class="btn btn-primary  btn-icon btn-round"><i class="fas fa-plus"></i></button>
                      </form>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- List of Queries -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Queries</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($queries)): ?>
                      <p>No queries found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Raised By</th>
                              <th>Raised To</th>
                              <th>Status</th>
                              <th>Raised At</th>
                              <th>Response</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($queries as $query): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($query['query_id']); ?></td>
                                <td><?php echo htmlspecialchars($query['raised_by_username']); ?></td>
                                <td><?php echo htmlspecialchars($query['raised_to_username'] ?? 'N/A'); ?></td>
                                <td >
                                  <span id="status-<?php echo $query['query_id']; ?>" class="badge 
                                                     <?php
                                                      switch ($query['status']) {
                                                        case 'sent':
                                                          echo 'bg-warning';
                                                          break;
                                                        case 'opened':
                                                          echo 'bg-primary';
                                                          break;
                                                        case 'responded':
                                                          echo 'bg-info';
                                                          break;
                                                        case 'Closed':
                                                          echo 'bg-success';
                                                          break;
                                                        default:
                                                          echo 'bg-secondary'; // fallback if status is unexpected
                                                      }
                                                      ?>">
                                    <?php echo htmlspecialchars($query['status']); ?>
                                  </span>
                                </td>



                                <td><?php echo htmlspecialchars($query['raised_at']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($query['response_text'] ?? 'N/A')); ?></td>
                                <td class="d-flex">
                                  <?php
                                  $can_edit_respond = false;
                                  if (in_array($_SESSION['role'], ['Auditor', 'Admin'])) {
                                    $can_edit_respond = true;
                                  } elseif (in_array($_SESSION['role'], ['Client', 'Reviewer']) && $_SESSION['user_id'] == $query['raised_to_user_id']) {
                                    $can_edit_respond = true;
                                  }
                                  ?>
                                  <?php if ($can_edit_respond): ?>
                                    <?php if (htmlspecialchars($query['status']) != 'Responded') { ?>
                                      <button type="button" class="btn btn-icon btn-round btn-primary text-white mb-1 me-1" data-bs-toggle="modal" data-bs-target="#editQueryModal"
                                        data-id="<?php echo $query['query_id']; ?>"
                                        data-query-text="<?php echo htmlspecialchars($query['query_text']); ?>"
                                        data-raised-to-id="<?php echo htmlspecialchars($query['raised_to_user_id']); ?>"
                                        data-response-text="<?php echo htmlspecialchars($query['response_text']); ?>"
                                        data-status="<?php echo htmlspecialchars($query['status']); ?>">
                                       <i class="fas fa-list"></i>
                                      </button>
                                    <?php } ?>
                                  <?php endif; ?>
                                  <?php if (in_array($_SESSION['role'], ['Auditor', 'Admin'])): ?>
                                    <a href="queries.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $query['query_id']; ?>" class=" text-white btn btn-icon btn-round btn-danger" onclick="return confirm('Are you sure you want to delete this query?');"><i class="fas fa-trash"></i></a>
                                  <?php endif; ?>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if ($_SESSION['role'] == 'Client' || $_SESSION['role'] == 'Reviewer') { ?>
                  <div class="mt-4">
                    <a href="open_queries.php" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
                  </div>
                <?php } elseif ($_SESSION['role'] == 'Auditor' || $_SESSION['role'] == 'Admin') { ?>
                  <div class="mt-4">
                    <a href="engagement_details.php?engagement_id=<?php echo $engagement_id; ?>" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
                  </div>
                <?php } ?>

              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="editQueryModal" tabindex="-1" aria-labelledby="editQueryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header"> 
              <h5 class="modal-title" id="editQueryModalLabel">Edit Query / Respond</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="queries.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="query_id" id="edit_query_id">
                <div class="mb-3">
                  <label for="edit_query_text" class="form-label">Query Text</label>
                  <textarea class="form-control" id="edit_query_text" name="query_text" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label for="edit_raised_to_user_id" class="form-label">Raised To</label>
                  <select class="form-select form-control" id="edit_raised_to_user_id" name="raised_to_user_id">
                    <option value="">Select User (Optional)</option>
                    <?php foreach ($users_for_dropdown as $user): ?>
                      <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if ($raised_by_user_id == $_SESSION['user_id']) { ?>
                  <div class="mb-3">
                    <label for="edit_response_text" class="form-label">Response</label>
                    <textarea class="form-control" id="edit_response_text" name="response_text" rows="3"></textarea>
                  </div>
                <?php } ?>

                <?php if ($raised_by_user_id == $_SESSION['user_id']) { ?>

                <?php } ?>

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-icon btn-round"><i  class="fas fa-save"></i></button>
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
    document.addEventListener('DOMContentLoaded', function() {
      var viewButtons = document.querySelectorAll('button[data-toggle="modal"]');
      viewButtons.forEach(function(button) {
        button.addEventListener('click', function() {
          var queryId = this.getAttribute('data-id');
          var raisedToId = this.getAttribute('data-raised-to-id');
          var currentUserId = "<?php echo $_SESSION['user_id']; ?>";

          if (currentUserId == raisedToId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_query_status.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
              if (xhr.status === 200) {
                // Optionally, update the UI to reflect the status change
                var statusBadge = document.querySelector('#status-' + queryId);
                if (statusBadge) {
                  statusBadge.textContent = 'opened';
                  statusBadge.className = 'badge bg-primary';
                }
              }
            };
            xhr.send('query_id=' + queryId + '&status=opened');
          }
        });
      });
    });

    $('#editQueryModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var queryText = button.data('query-text');
      var raisedToId = button.data('raised-to-id');
      var responseText = button.data('response-text');
      var status = button.data('status');

      var modal = $(this);
      modal.find('.modal-title').text('Edit Query / Respond (ID: ' + id + ')');
      modal.find('#edit_query_id').val(id);
      modal.find('#edit_query_text').val(queryText);
      modal.find('#edit_raised_to_user_id').val(raisedToId);
      modal.find('#edit_response_text').val(responseText);
      modal.find('#edit_status').val(status);

      // Get current user role and ID from PHP session (passed via a data attribute or global JS var)
      var currentUserRole = "<?php echo $_SESSION['role']; ?>";
      var currentUserId = "<?php echo $_SESSION['user_id']; ?>";

      if (['Client', 'Reviewer'].includes(currentUserRole) && currentUserId == raisedToId) {
        // Client/Reviewer can only respond to queries raised to them
        modal.find('#edit_query_text').attr('readonly', 'readonly');
        modal.find('#edit_raised_to_user_id').attr('disabled', 'disabled'); // Use disabled for select to prevent submission
        modal.find('#edit_response_text').removeAttr('readonly');
        modal.find('#edit_status').removeAttr('disabled');
      } else if (['Client', 'Reviewer'].includes(currentUserRole) && currentUserId != raisedToId) {
        // Client/Reviewer cannot edit queries not raised to them
        modal.find('#edit_query_text').attr('readonly', 'readonly');
        modal.find('#edit_raised_to_user_id').attr('disabled', 'disabled');
        modal.find('#edit_response_text').attr('readonly', 'readonly');
        modal.find('#edit_status').attr('disabled', 'disabled');
      } else {
        // Auditor/Admin has full control
        modal.find('#edit_query_text').removeAttr('readonly');
        modal.find('#edit_raised_to_user_id').removeAttr('disabled');
        modal.find('#edit_response_text').removeAttr('readonly');
        modal.find('#edit_status').removeAttr('disabled');
      }
    });
  </script>
</body>

</html>