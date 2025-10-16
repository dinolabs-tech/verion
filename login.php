<?php
session_start();
require_once 'database/db_connection.php';
// require_once 'database/database_schema.php';

// Check if the default admin user exists, and create it if it doesn't
$admin_username = 'dinolabs';
$admin_password = 'dinolabs';
$admin_role = 'admin';

$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
  $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, 'active')");
  $insert_stmt->bind_param("sss", $admin_username, $hashed_password, $admin_role);
  $insert_stmt->execute();
  $insert_stmt->close();
}
$stmt->close();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $error_message = "Please enter both username and password.";
  } else {
    $stmt = $conn->prepare("SELECT u.user_id, u.username, u.password, u.role, u.status, c.client_id FROM users u LEFT JOIN clients c ON u.user_id = c.client_id WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();
      if (password_verify($password, $user['password'])) {
        if ($user['status'] === 'active') {
          $_SESSION['user_id'] = $user['user_id'];
          $_SESSION['username'] = $user['username'];
          $_SESSION['role'] = $user['role'];
          if ($user['role'] === 'Client' && isset($user['client_id'])) {
            $_SESSION['client_id'] = $user['client_id'];
          }

          // Update last_login timestamp
          $update_stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
          $update_stmt->bind_param("i", $user['user_id']);
          $update_stmt->execute();

          // Log the login event to audit_logs
          $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent) VALUES (?, 'login_success', 'Successful login', ?, ?)");
          $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
          $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
          $log_stmt->bind_param("iss", $user['user_id'], $ip_address, $user_agent);
          $log_stmt->execute();
          $log_stmt->close();

          // Log the login event to session_logs
          $log_stmt = $conn->prepare("INSERT INTO session_logs (user_id, event_type, ip_address, user_agent) VALUES (?, 'login', ?, ?)");
          $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
          $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
          $log_stmt->bind_param("iss", $user['user_id'], $ip_address, $user_agent);
          $log_stmt->execute();


          header("Location: dashboard.php");
          exit();
        } else {
          $error_message = "Your account is inactive. Please contact an administrator.";
        }
      } else {
        // Log failed login attempt
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent) VALUES (NULL, 'login_failed', 'Invalid password', ?, ?)");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $log_stmt->bind_param("ss", $ip_address, $user_agent);
        $log_stmt->execute();
        $log_stmt->close();

        $error_message = "Invalid username or password.";
      }
    } else {
      $error_message = "Invalid username or password.";
    }
    $stmt->close();
  }
}
// $conn->close(); // Connection should not be closed here to be available for the HTML part if needed.
?>


<!DOCTYPE html>
<html lang="en">
<?php include('component/head.php'); ?>

<body>
  <div class="container-fluid wrapper">


    <div class="content-wrapper d-flex align-items-center auth">
      <div class="container mt-5">
        <div class="page-inner">
          <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
              <div class="card">
                <div class="card-header text-center">
                  <h3>Login</h3>
                </div>
                <div class="card-body">
                  <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                      <?php echo $error_message; ?>
                    </div>
                  <?php endif; ?>
                  <form action="login.php" method="POST">
                    <div class="mb-3">
                      <label for="username" class="form-label">Username</label>
                      <input type="text" class="form-control" id="username" name="username">
                    </div>
                    <div class="mb-3">
                      <label for="password" class="form-label">Password</label>
                      <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="d-grid">
                      <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <?php include('component/script.php'); ?>
</body>

</html>