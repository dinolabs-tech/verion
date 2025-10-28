<?php
session_start();
require_once 'database/db_connection.php';

// Only Admin can access this page for now
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser') {
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

  // New fields
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
      $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, client_id, first_name, last_name, date_of_birth, gender, nationality, marital_status, phone_number, address_street, address_city, address_state, address_zip_code, address_country, occupation, company, education_level, time_zone, preferred_language) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("ssssisssssssssssssssss", $username, $email, $hashed_password, $role, $client_id, $first_name, $last_name, $date_of_birth, $gender, $nationality, $marital_status, $phone_number, $address_street, $address_city, $address_state, $address_zip_code, $address_country, $occupation, $company, $education_level, $time_zone, $preferred_language);

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
                    <span><small>Fields marked read are compulsory</small></span>
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
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control border-danger" placeholder="Username" id="username" name="username" >
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="email" class="form-control border-danger" placeholder="Email" id="email" name="email">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="password" class="form-control border-danger" placeholder="Password" id="password" name="password">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="password" class="form-control border-danger" placeholder="Confirm Password" id="confirm_password" name="confirm_password">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="First Name" id="first_name" name="first_name">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Last Name" id="last_name" name="last_name">
                        </div>
                        <div class="mb-3 col-md-3">
                          <!-- <label for="date_of_birth" class="form-label">Date of Birth</label> -->
                          <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                        <div class="mb-3 col-md-3">
                          <select class="form-select form-control" id="gender" name="gender">
                            <option value="" selected disabled>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Nationality" id="nationality" name="nationality">
                        </div>
                        <div class="mb-3 col-md-3">
                          <select class="form-select form-control" id="marital_status" name="marital_status">
                            <option value="" selected disabled>Select Marital Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Phone Number" id="phone_number" name="phone_number">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Street Address" id="address_street" name="address_street">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="City" id="address_city" name="address_city">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="State/Province" id="address_state" name="address_state">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Zip Code" id="address_zip_code" name="address_zip_code">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Country" id="address_country" name="address_country">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Occupation" id="occupation" name="occupation">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Company" id="company" name="company">
                        </div>
                        <div class="mb-3 col-md-3">
                          <select class="form-select form-control" id="education_level" name="education_level">
                            <option value="" selected disabled>Select Education Level</option>
                            <option value="High School">High School</option>
                            <option value="Associate Degree">Associate Degree</option>
                            <option value="Bachelor's Degree">Bachelor's Degree</option>
                            <option value="Master's Degree">Master's Degree</option>
                            <option value="Doctorate">Doctorate</option>
                            <option value="Other">Other</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Time Zone" id="time_zone" name="time_zone">
                        </div>
                        <div class="mb-3 col-md-3">
                          <input type="text" class="form-control" placeholder="Preferred Language" id="preferred_language" name="preferred_language">
                        </div>
                        <div class="mb-3 col-md-3">
                          <select class="form-select form-control border-danger" id="role" name="role">
                            <option value="" selected disabled>Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Auditor">Auditor</option>
                            <option value="Reviewer">Reviewer</option>
                            <option value="Client">Client</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-3" id="client_id_field" style="display: none;">
                          <select class="form-select form-control" id="client_id" name="client_id">
                            <option value="" selected disabled>Select Client (Optional)</option>
                            <?php foreach ($clients as $client): ?>
                              <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="col-md-12 text-center">
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
