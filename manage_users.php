<?php
session_start();
require_once 'database/db_connection.php';

// Only Admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
  header("Location: login.php");
  exit();
}

$success_message = '';
$error_message = '';

// Handle Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $user_id = $_POST['user_id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';
    $client_id = $_POST['client_id'] ?? NULL;

    if (empty($username) || empty($email) || empty($role) || empty($status)) {
      $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error_message = "Invalid email format.";
    } else {
      // Check if username or email already exists for another user
      $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
      $stmt->bind_param("ssi", $username, $email, $user_id);
      $stmt->execute();
      $stmt->store_result();

      if ($stmt->num_rows > 0) {
        $error_message = "Username or email already exists for another user.";
      } else {
        $stmt->close();
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ?, client_id = ? WHERE user_id = ?");
        $stmt->bind_param("ssssii", $username, $email, $role, $status, $client_id, $user_id);

        if ($stmt->execute()) {
          $success_message = "User updated successfully!";
        } else {
          $error_message = "Error updating user: " . $conn->error;
        }
      }
      $stmt->close();
    }
  }
}

// Handle Delete User
if (isset($_GET['delete_id'])) {
  $user_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  if ($stmt->execute()) {
    $success_message = "User deleted successfully!";
  } else {
    $error_message = "Error deleting user: " . $conn->error;
  }
  $stmt->close();
  header("Location: manage_users.php?message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch all users with their assigned client names
$users = [];
$result = $conn->query("SELECT u.*, c.client_name FROM users u LEFT JOIN clients c ON u.client_id = c.client_id ORDER BY u.username");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}

// Fetch clients for the dropdown in edit modal
$clients = [];
$client_result = $conn->query("SELECT client_id, client_name FROM clients ORDER BY client_name");
if ($client_result) {
  while ($row = $client_result->fetch_assoc()) {
    $clients[] = $row;
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
          <!-- <div class="page-header"> -->
          <!-- <h4 class="page-title">Dashboard</h4> -->
          <!-- <ul class="breadcrumbs">
                <li class="nav-home">
                  <a href="#">
                    <i class="icon-home"></i>
                  </a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                  <a href="#">Pages</a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                  <a href="#">Starter Page</a>
                </li>
              </ul> -->
          <!-- </div> -->
           <div
              class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-2"
            >
              <div>
                <h3 class="fw-bold mb-3">Manage Users</h3>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <a href="register.php" class="btn btn-primary btn-round">Add User</a>
              </div>
            </div>
          <div class="row">
            <div class="col-6">
              <!-- <a href="" class="btn btn-primary ms-3 mt-2 mb-3 btn-icon btn-round"><i class="fas fa-plus"></i></a> -->

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

          <!-- List of Users -->
          <div class="card">
            <div class="card-header">
              <h4>Existing Users</h4>
            </div>
            <div class="card-body">
              <?php if (empty($users)): ?>
                <p>No users found. Register a new user.</p>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="basic-datatables">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Client</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($users as $user): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                          <td><?php echo htmlspecialchars($user['username']); ?></td>
                          <td><?php echo htmlspecialchars($user['email']); ?></td>
                          <td><?php echo htmlspecialchars($user['role']); ?></td>
                          <td><?php echo htmlspecialchars($user['status']); ?></td>
                          <td><?php echo htmlspecialchars($user['client_name'] ?? 'N/A'); ?></td>
                          <td class="d-flex">
                            <button type="button" class=" btn-primary edit-user-btn btn-icon btn-round text-white me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editUserModal"
                              data-id="<?php echo $user['user_id']; ?>"
                              data-username="<?php echo htmlspecialchars($user['username']); ?>"
                              data-email="<?php echo htmlspecialchars($user['email']); ?>"
                              data-role="<?php echo htmlspecialchars($user['role']); ?>"
                              data-status="<?php echo htmlspecialchars($user['status']); ?>"
                              data-client-id="<?php echo htmlspecialchars($user['client_id']); ?>">
                              <i class="fa fa-edit"></i>
                            </button>
                            <a href="manage_users.php?delete_id=<?php echo $user['user_id']; ?>" class=" text-white btn-danger btn-icon btn-round" onclick="return confirm('Are you sure you want to delete this user?');"><i class="icon fas fa-trash"></i></a>
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
      <!-- Edit User Modal -->
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="manage_users.php" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="mb-3">

                  <input type="text" class="form-control" id="edit_username" placeholder="Username" name="username">
                </div>
                <div class="mb-3">
                  <input type="email" placeholder="Email" class="form-control" id="edit_email" name="email">
                </div>
                <div class="mb-3">
                  <select class="form-control" id="edit_role" name="role">
                    <option value="Admin">Admin</option>
                    <option value="Auditor">Auditor</option>
                    <option value="Reviewer">Reviewer</option>
                    <option value="Client">Client</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="edit_status" class="form-label">Status</label>
                  <select class="form-control" id="edit_status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="mb-3" id="edit_client_id_field">
                  <label for="edit_client_id" class="form-label">Assign Client</label>
                  <select class="form-control" id="edit_client_id" name="client_id">
                    <option value="">Select Client (Optional)</option>
                    <?php foreach ($clients as $client): ?>
                      <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
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
    $('#editUserModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var username = button.data('username');
      var email = button.data('email');
      var role = button.data('role');
      var status = button.data('status');
      var clientId = button.data('client-id');

      var modal = $(this);
      modal.find('.modal-title').text('Edit User: ' + username);
      modal.find('#edit_user_id').val(id);
      modal.find('#edit_username').val(username);
      modal.find('#edit_email').val(email);
      modal.find('#edit_role').val(role);
      modal.find('#edit_status').val(status);

      var clientIdField = modal.find('#edit_client_id_field');
      var clientIdSelect = modal.find('#edit_client_id');

      // Show/hide client_id field based on role
      if (role === 'Client') {
        clientIdField.show();
        clientIdSelect.val(clientId);
      } else {
        clientIdField.hide();
        clientIdSelect.val(''); // Clear selection if not a client
      }

      // Event listener for role change within the modal
      modal.find('#edit_role').on('change', function() {
        if ($(this).val() === 'Client') {
          clientIdField.show();
        } else {
          clientIdField.hide();
          clientIdSelect.val('');
        }
      });
    });
  </script>
</body>

</html>