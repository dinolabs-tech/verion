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
          <div class="sidebar-bg-options selected" id="sidebar-light-theme">
            <div class="img-ss rounded-circle bg-light border mr-3"></div>Light
          </div>
          <div class="sidebar-bg-options" id="sidebar-dark-theme">
            <div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark
          </div>
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
              Edit User
            </div>
            <div class="card-body">
              <form action="">
                <!-- <input type="text"> -->
                <div class="mb-2">
                  <label for="edit_username" class="form-label">Username</label>
                  <input type="text" class="form-control" id="edit_username" name="username">
                </div>
                <div class="mt-3">
                  <label for="edit_email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="edit_email" name="email">
                </div>
                <div class="mb-3">
                  <label for="role" class="form-label">Role</label>
                  <select class="form-select form-control" id="role" name="role">
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Auditor">Auditor</option>
                    <option value="Reviewer">Reviewer</option>
                    <option value="Client">Client</option>
                  </select>
                </div>
                <div class="mb-3" id="client_id_field" style="display: none;">
                  <label for="client_id" class="form-label">Assign Client</label>
                  <select class="form-select form-control" id="client_id" name="client_id">
                    <option value="">Select Client (Optional)</option>
                    <?php foreach ($clients as $client): ?>
                      <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
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
  <script>
    var editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      var id = button.getAttribute('data-id');
      var username = button.getAttribute('data-username');
      var email = button.getAttribute('data-email');
      var role = button.getAttribute('data-role');
      var status = button.getAttribute('data-status');
      var clientId = button.getAttribute('data-client-id');

      var modalTitle = editUserModal.querySelector('.modal-title');
      var userIdInput = editUserModal.querySelector('#edit_user_id');
      var usernameInput = editUserModal.querySelector('#edit_username');
      var emailInput = editUserModal.querySelector('#edit_email');
      var roleSelect = editUserModal.querySelector('#edit_role');
      var statusSelect = editUserModal.querySelector('#edit_status');
      var clientIdSelect = editUserModal.querySelector('#edit_client_id');
      var clientIdField = editUserModal.querySelector('#edit_client_id_field');

      modalTitle.textContent = 'Edit User: ' + username;
      userIdInput.value = id;
      usernameInput.value = username;
      emailInput.value = email;
      roleSelect.value = role;
      statusSelect.value = status;

      // Show/hide client_id field based on role
      if (role === 'Client') {
        clientIdField.style.display = 'block';
        clientIdSelect.value = clientId;
      } else {
        clientIdField.style.display = 'none';
        clientIdSelect.value = ''; // Clear selection if not a client
      }

      // Event listener for role change within the modal
      roleSelect.addEventListener('change', function() {
        if (this.value === 'Client') {
          clientIdField.style.display = 'block';
        } else {
          clientIdField.style.display = 'none';
          clientIdSelect.value = '';
        }
      });
    });
  </script>

  <!-- endinject -->
  <!-- Custom js for this page-->
  <!-- End custom js for this page-->
</body>


<!-- Mirrored from www.urbanui.com/melody/template/pages/samples/blank-page.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 15 Sep 2018 06:08:54 GMT -->

</html>