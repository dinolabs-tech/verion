<?php
session_start();
require_once 'database/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$user_data = [];
$error_message = '';
$success_message = '';

$edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, password, role, status, created_at, updated_at, last_login FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user_data = $result->fetch_assoc();
} else {
  $error_message = "User not found.";
  // If user not found, redirect to login or show an error and exit
  header("Location: login.php");
  exit();
}
$stmt->close();


// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $new_email = $_POST['email'] ?? '';
  $new_password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Validate email
  if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Invalid email format.";
  } elseif ($new_password !== $confirm_password) {
    $error_message = "Passwords do not match.";
  } elseif (strlen($new_password) > 0 && strlen($new_password) < 8) {
    $error_message = "Password must be at least 8 characters long.";
  } elseif (strlen($new_password) > 0 && !preg_match('/[A-Z]/', $new_password)) {
    $error_message = "Password must contain at least one uppercase letter.";
  } elseif (strlen($new_password) > 0 && !preg_match('/[a-z]/', $new_password)) {
    $error_message = "Password must contain at least one lowercase letter.";
  } elseif (strlen($new_password) > 0 && !preg_match('/[0-9]/', $new_password)) {
    $error_message = "Password must contain at least one number.";
  } elseif (strlen($new_password) > 0 && !preg_match('/[^a-zA-Z0-9]/', $new_password)) {
    $error_message = "Password must contain at least one special character.";
  } else {
    // Check if the email is already in use
    $check_email_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $check_email_stmt->bind_param("si", $new_email, $user_id);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    if ($check_email_result->num_rows > 0) {
      $error_message = "This email is already in use.";
    } else {
      // Update email and password (if provided)
      $update_fields = [];
      $update_params = [];
      $param_types = "";

      if (!empty($new_email) && $new_email !== $user_data['email']) {
        $update_fields[] = "email = ?";
        $update_params[] = $new_email;
        $param_types .= "s";
      }

      if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields[] = "password = ?";
        $update_params[] = $hashed_password;
        $param_types .= "s";
      }
    }
    $check_email_stmt->close();

    if (!empty($update_fields)) {
      $sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE user_id = ?";
      $stmt = $conn->prepare($sql);

      if ($stmt) {
        $update_params[] = $user_id;
        $param_types .= "i";

        $stmt->bind_param($param_types, ...$update_params);

        if ($stmt->execute()) {
          $success_message = "Profile updated successfully!";
          // Refresh user data
          $stmt = $conn->prepare("SELECT username, email, password, role, status, created_at, updated_at, last_login FROM users WHERE user_id = ?");
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
          }
          $stmt->close();
        } else {
          $error_message = "Error updating profile: " . $stmt->error;
        }
      } else {
        $error_message = "Error preparing update statement.";
      }
    } else {
      $success_message = "No changes to update.";
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
          <div class="row justify-content-center">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header text-center">
                  <h3>User Profile</h3>
                </div>
                <div class="card-body">
                  <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                      <?php echo $error_message; ?>
                    </div>
                  <?php else: ?>
                    <div class="mb-3">
                      <label class="form-label"><strong>Username:</strong></label>
                      <p><?php echo htmlspecialchars($user_data['username']); ?></p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label"><strong>Email:</strong></label>
                      <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label"><strong>Role:</strong></label>
                      <p><?php echo htmlspecialchars($user_data['role']); ?></p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label"><strong>Account Status:</strong></label>
                      <p><?php echo htmlspecialchars($user_data['status']); ?></p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label"><strong>Member Since:</strong></label>
                      <p><?php echo htmlspecialchars($user_data['created_at']); ?></p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label"><strong>Last Updated:</strong></label>
                      <p><?php echo htmlspecialchars($user_data['updated_at']); ?></p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label"><strong>Last Login:</strong></label>
                      <p><?php echo htmlspecialchars($user_data['last_login'] ?? 'N/A'); ?></p>
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary btn-icon btn-round me-2"><i class="fas fa-arrow-left"></i></a>
                    <?php if (!$edit_mode): ?>
                      <a href="profile.php?edit=true" class="btn btn-primary btn-icon btn-round ps-1"><i class="fas fa-edit"></i></a>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if ($edit_mode): ?>
                    <form action="profile.php" method="POST">
                      <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>">
                      </div>
                      <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Leave blank to keep current password.</div>
                      </div>
                      <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                      </div>
                      <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                      <a href="profile.php" class="btn btn-secondary">Cancel</a>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
</body>

</html>