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
$stmt = $conn->prepare("SELECT username, email, password, role, status, created_at, updated_at, last_login, first_name, last_name, date_of_birth, gender, nationality, marital_status, phone_number, address_street, address_city, address_state, address_zip_code, address_country, occupation, company, education_level, time_zone, preferred_language FROM users WHERE user_id = ?");
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
      // Collect new demographic data
      $first_name = $_POST['first_name'] ?? NULL;
      $last_name = $_POST['last_name'] ?? NULL;
      $date_of_birth = $_POST['date_of_birth'] ?? NULL;
      $gender = $_POST['gender'] ?? NULL;
      $nationality = $_POST['nationality'] ?? NULL;
      $marital_status = $_POST['marital_status'] ?? NULL;
      $phone_number = $_POST['phone_number'] ?? NULL;
      $address_street = $_POST['address_street'] ?? NULL;
      $address_city = $_POST['address_city'] ?? NULL;
      $address_state = $_POST['address_state'] ?? NULL;
      $address_zip_code = $_POST['address_zip_code'] ?? NULL;
      $address_country = $_POST['address_country'] ?? NULL;
      $occupation = $_POST['occupation'] ?? NULL;
      $company = $_POST['company'] ?? NULL;
      $education_level = $_POST['education_level'] ?? NULL;
      $time_zone = $_POST['time_zone'] ?? NULL;
      $preferred_language = $_POST['preferred_language'] ?? NULL;


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

      // Add demographic fields to update
      if ($first_name !== ($user_data['first_name'] ?? NULL)) {
        $update_fields[] = "first_name = ?";
        $update_params[] = $first_name;
        $param_types .= "s";
      }
      if ($last_name !== ($user_data['last_name'] ?? NULL)) {
        $update_fields[] = "last_name = ?";
        $update_params[] = $last_name;
        $param_types .= "s";
      }
      if ($date_of_birth !== ($user_data['date_of_birth'] ?? NULL)) {
        $update_fields[] = "date_of_birth = ?";
        $update_params[] = $date_of_birth;
        $param_types .= "s";
      }
      if ($gender !== ($user_data['gender'] ?? NULL)) {
        $update_fields[] = "gender = ?";
        $update_params[] = $gender;
        $param_types .= "s";
      }
      if ($nationality !== ($user_data['nationality'] ?? NULL)) {
        $update_fields[] = "nationality = ?";
        $update_params[] = $nationality;
        $param_types .= "s";
      }
      if ($marital_status !== ($user_data['marital_status'] ?? NULL)) {
        $update_fields[] = "marital_status = ?";
        $update_params[] = $marital_status;
        $param_types .= "s";
      }
      if ($phone_number !== ($user_data['phone_number'] ?? NULL)) {
        $update_fields[] = "phone_number = ?";
        $update_params[] = $phone_number;
        $param_types .= "s";
      }
      if ($address_street !== ($user_data['address_street'] ?? NULL)) {
        $update_fields[] = "address_street = ?";
        $update_params[] = $address_street;
        $param_types .= "s";
      }
      if ($address_city !== ($user_data['address_city'] ?? NULL)) {
        $update_fields[] = "address_city = ?";
        $update_params[] = $address_city;
        $param_types .= "s";
      }
      if ($address_state !== ($user_data['address_state'] ?? NULL)) {
        $update_fields[] = "address_state = ?";
        $update_params[] = $address_state;
        $param_types .= "s";
      }
      if ($address_zip_code !== ($user_data['address_zip_code'] ?? NULL)) {
        $update_fields[] = "address_zip_code = ?";
        $update_params[] = $address_zip_code;
        $param_types .= "s";
      }
      if ($address_country !== ($user_data['address_country'] ?? NULL)) {
        $update_fields[] = "address_country = ?";
        $update_params[] = $address_country;
        $param_types .= "s";
      }
      if ($occupation !== ($user_data['occupation'] ?? NULL)) {
        $update_fields[] = "occupation = ?";
        $update_params[] = $occupation;
        $param_types .= "s";
      }
      if ($company !== ($user_data['company'] ?? NULL)) {
        $update_fields[] = "company = ?";
        $update_params[] = $company;
        $param_types .= "s";
      }
      if ($education_level !== ($user_data['education_level'] ?? NULL)) {
        $update_fields[] = "education_level = ?";
        $update_params[] = $education_level;
        $param_types .= "s";
      }
      if ($time_zone !== ($user_data['time_zone'] ?? NULL)) {
        $update_fields[] = "time_zone = ?";
        $update_params[] = $time_zone;
        $param_types .= "s";
      }
      if ($preferred_language !== ($user_data['preferred_language'] ?? NULL)) {
        $update_fields[] = "preferred_language = ?";
        $update_params[] = $preferred_language;
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
          $stmt = $conn->prepare("SELECT username, email, password, role, status, created_at, updated_at, last_login, first_name, last_name, date_of_birth, gender, nationality, marital_status, phone_number, address_street, address_city, address_state, address_zip_code, address_country, occupation, company, education_level, time_zone, preferred_language FROM users WHERE user_id = ?");
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
                <div class="card-body row">
                  <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                      <?php echo $error_message; ?>
                    </div>
                  <?php else: ?>
                    <div class="row">
                      <div class="mb-3 col-md-3">
                        <label class="form-label"><strong>Username:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['username']); ?></p>
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label"><strong>Email:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label"><strong>Role:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['role']); ?></p>
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label"><strong>Account Status:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['status']); ?></p>
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label"><strong>Member Since:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['created_at']); ?></p>
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label"><strong>Last Updated:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['updated_at']); ?></p>
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label"><strong>Last Login:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['last_login'] ?? 'N/A'); ?></p>
                      </div>
                    </div>

                    <hr class="my-4">
                    <h4>Additional Demographic Information</h4>

                    <div class="row">
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>First Name:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['first_name'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Last Name:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['last_name'] ?? 'N/A'); ?></p>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Date of Birth:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['date_of_birth'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Gender:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['gender'] ?? 'N/A'); ?></p>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Nationality:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['nationality'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Marital Status:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['marital_status'] ?? 'N/A'); ?></p>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Phone Number:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['phone_number'] ?? 'N/A'); ?></p>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Street Address:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['address_street'] ?? 'N/A'); ?></p>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>City:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['address_city'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>State/Province:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['address_state'] ?? 'N/A'); ?></p>
                      </div>


                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Zip/Postal Code:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['address_zip_code'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Country:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['address_country'] ?? 'N/A'); ?></p>
                      </div>


                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Occupation/Job Title:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['occupation'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Company/Organization:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['company'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Education Level:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['education_level'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Time Zone:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['time_zone'] ?? 'N/A'); ?></p>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Preferred Language:</strong></label>
                        <p><?php echo htmlspecialchars($user_data['preferred_language'] ?? 'N/A'); ?></p>
                      </div>

                      <div class="col-md-12 text-center">
                        <a href="dashboard.php" class="btn btn-secondary btn-lg btn-icon btn-round me-2"><i class="fas fa-arrow-left"></i></a>
                        <?php if (!$edit_mode): ?>
                          <a href="profile.php?edit=true" class="btn btn-primary btn-lg btn-icon btn-round ps-1"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                      </div>

                    <?php endif; ?>
                    </div>



                </div>
              </div>

              <?php if ($edit_mode): ?>
                <div class="card mt-5 shadow">
                  <div class="card-header text-center">
                    <h3>Edit Profile</h3>
                  </div>
                  <div class="card-body">


                    <form action="profile.php" method="POST" class="row">
                      <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Leave blank to keep current password.</div>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                      </div>

                      <hr class="my-4">
                      <h4>Additional Demographic Information</h4>


                      <div class="col-md-3 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>">
                      </div>



                      <div class="col-md-3 mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user_data['date_of_birth'] ?? ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                          <option value="">Select Gender</option>
                          <option value="Male" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                          <option value="Female" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                      </div>



                      <div class="col-md-3 mb-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo htmlspecialchars($user_data['nationality'] ?? ''); ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="marital_status" class="form-label">Marital Status</label>
                        <select class="form-select" id="marital_status" name="marital_status">
                          <option value="">Select Marital Status</option>
                          <option value="Single" <?php echo (isset($user_data['marital_status']) && $user_data['marital_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                          <option value="Married" <?php echo (isset($user_data['marital_status']) && $user_data['marital_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                          <option value="Divorced" <?php echo (isset($user_data['marital_status']) && $user_data['marital_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                          <option value="Widowed" <?php echo (isset($user_data['marital_status']) && $user_data['marital_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                      </div>


                      <div class="col-md-3 mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>">
                      </div>

                      <div class="col-md-3 mb-3">
                        <label for="address_street" class="form-label">Street Address</label>
                        <input type="text" class="form-control" id="address_street" name="address_street" value="<?php echo htmlspecialchars($user_data['address_street'] ?? ''); ?>">
                      </div>

                      <div class="col-md-3 mb-3">
                        <label for="address_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="address_city" name="address_city" value="<?php echo htmlspecialchars($user_data['address_city'] ?? ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="address_state" class="form-label">State/Province</label>
                        <input type="text" class="form-control" id="address_state" name="address_state" value="<?php echo htmlspecialchars($user_data['address_state'] ?? ''); ?>">
                      </div>


                      <div class="col-md-3 mb-3">
                        <label for="address_zip_code" class="form-label">Zip/Postal Code</label>
                        <input type="text" class="form-control" id="address_zip_code" name="address_zip_code" value="<?php echo htmlspecialchars($user_data['address_zip_code'] ?? ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="address_country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="address_country" name="address_country" value="<?php echo htmlspecialchars($user_data['address_country'] ?? ''); ?>">
                      </div>


                      <div class="col-md-3 mb-3">
                        <label for="occupation" class="form-label">Occupation/Job Title</label>
                        <input type="text" class="form-control" id="occupation" name="occupation" value="<?php echo htmlspecialchars($user_data['occupation'] ?? ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="company" class="form-label">Company/Organization</label>
                        <input type="text" class="form-control" id="company" name="company" value="<?php echo htmlspecialchars($user_data['company'] ?? ''); ?>">
                      </div>
                      <div class="col-md-3 md-3">
                        <label for="education_level" class="form-label">Education Level</label>
                        <select class="form-select" id="education_level" name="education_level">
                          <option value="">Select Education Level</option>
                          <option value="High School" <?php echo (isset($user_data['education_level']) && $user_data['education_level'] == 'High School') ? 'selected' : ''; ?>>High School</option>
                          <option value="Associate Degree" <?php echo (isset($user_data['education_level']) && $user_data['education_level'] == 'Associate Degree') ? 'selected' : ''; ?>>Associate Degree</option>
                          <option value="Bachelor's Degree" <?php echo (isset($user_data['education_level']) && $user_data['education_level'] == 'Bachelor\'s Degree') ? 'selected' : ''; ?>>Bachelor's Degree</option>
                          <option value="Master's Degree" <?php echo (isset($user_data['education_level']) && $user_data['education_level'] == 'Master\'s Degree') ? 'selected' : ''; ?>>Master's Degree</option>
                          <option value="Doctorate" <?php echo (isset($user_data['education_level']) && $user_data['education_level'] == 'Doctorate') ? 'selected' : ''; ?>>Doctorate</option>
                          <option value="Other" <?php echo (isset($user_data['education_level']) && $user_data['education_level'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="time_zone" class="form-label">Time Zone</label>
                        <input type="text" class="form-control" id="time_zone" name="time_zone" value="<?php echo htmlspecialchars($user_data['time_zone'] ?? ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="preferred_language" class="form-label">Preferred Language</label>
                        <input type="text" class="form-control" id="preferred_language" name="preferred_language" value="<?php echo htmlspecialchars($user_data['preferred_language'] ?? ''); ?>">
                      </div>

                      <div class="col-md-12 text-center">
                        <button type="submit" name="update_profile" class="btn btn-primary btn-lg btn-icon btn-round"><i class="fas fa-save"></i></button>
                        <a href="profile.php" class="btn btn-secondary btn-lg btn-icon btn-round"><i class="fas fa-times"></i></a>
                      </div>
                    </form>

                  </div>
                </div>
              <?php endif; ?>
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