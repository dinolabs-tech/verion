<?php
session_start();
require_once 'database/db_connection.php';

// Only Admin can access this page for now
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
  header("Location: login.php");
  exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $role = $_POST['role'] ?? '';
  $client_id = $_POST['client_id'] ?? NULL;

  // Enhanced validation
  if (empty($username)) {
    $error_message = "Username is required.";
  } elseif (empty($email)) {
    $error_message = "Email is required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Invalid email format.";
  } elseif (empty($password)) {
    $error_message = "Password is required.";
  } elseif (empty($confirm_password)) {
    $error_message = "Confirm password is required.";
  } elseif ($password !== $confirm_password) {
    $error_message = "Passwords do not match.";
  } elseif (strlen($password) < 6) {
    $error_message = "Password must be at least 6 characters long.";
  } elseif (empty($role)) {
    $error_message = "Role is required.";
  } else {
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error_message = "Username or email already exists.";
    } else {
      // Hash password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      // Insert new user
      $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, client_id) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $client_id);

      if ($stmt->execute()) {
        $success_message = "User registered successfully!";
      } else {
        $error_message = "Error registering user: " . $conn->error;
      }
    }
    $stmt->close();
  }
}

// Fetch clients for the dropdown
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
        <div class="container mt-5">
          <!-- <div class="container-scroller "> -->
          <div class="page-inner">
            <div class="row ">
              <div class="col-12">
                <div class="card">
                  <div class="card-header text-center">
                    <h3>Register New User</h3>
                  </div>
                  <div class="card-body">
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
                    <form action="register.php" method="POST">
                      <div class="row">
                        <div class="mb-3 col-md-6">
                          <input type="text" class="form-control" placeholder="Username" id="username" name="username">
                        </div>
                        <div class="mb-3 col-md-6">
                          <input type="email" class="form-control" placeholder="Email" id="email" name="email">
                        </div>
                        <div class="mb-3 col-md-6">
                          <input type="password" class="form-control" placeholder="Password" id="password" name="password">
                        </div>
                        <div class="mb-3 col-md-6">
                          <input type="password" class="form-control" placeholder="Confirm Password" id="confirm_password" name="confirm_password">
                        </div>
                        <div class="mb-3 col-md-6">
                          <select class="form-select form-control s" id="role" name="role">
                            <option value="" class="selected disabled" >Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Auditor">Auditor</option>
                            <option value="Reviewer">Reviewer</option>
                            <option value="Client">Client</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-6" id="client_id_field" style="display: none;">
                          <select class="form-select form-control" id="client_id" name="client_id">
                            <option value="">Select Client (Optional)</option>
                            <?php foreach ($clients as $client): ?>
                              <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="col-md-6">
                          <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- </div> -->
        </div>
      </div>
      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
  <script>
    document.getElementById('role').addEventListener('change', function() {
      var clientIdField = document.getElementById('client_id_field');
      if (this.value === 'Client') {
        clientIdField.style.display = 'block';
      } else {
        clientIdField.style.display = 'none';
      }
    });
  </script>
</body>

</html>