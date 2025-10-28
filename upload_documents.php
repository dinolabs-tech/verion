<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser')) {
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
  if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_path = $_FILES['document_file']['tmp_name'];
    $file_name = $_FILES['document_file']['name'];
    $file_size = $_FILES['document_file']['size'];
    $file_type = $_FILES['document_file']['type'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validate file type and size (example)
    $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
    $max_file_size = 10 * 1024 * 1024; // 10MB

    if (in_array($file_ext, $allowed_extensions) && $file_size <= $max_file_size) {
      // Sanitize file name
      $new_file_name = uniqid('', true) . '.' . $file_ext;
      $upload_directory = 'uploads/documents/';

      // Create directory if it doesn't exist
      if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0777, true);
      }

      $file_path = $upload_directory . $new_file_name;

      if (move_uploaded_file($file_tmp_path, $file_path)) {
        // File uploaded successfully, now store metadata in the database
        $document_type = $_POST['document_type'] ?? 'Other'; // Get document type from form
        $uploaded_by_user_id = $_SESSION['user_id'];

        $insert_stmt = $conn->prepare("INSERT INTO documents (engagement_id, file_name, file_path, document_type, uploaded_by_user_id, upload_date, file_size, file_type) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
        $insert_stmt->bind_param("isssisi", $engagement_id, $file_name, $file_path, $document_type, $uploaded_by_user_id, $file_size, $file_type);

        if ($insert_stmt->execute()) {
          $success_message = "Document uploaded successfully!";
        } else {
          $error_message = "Error saving document metadata: " . $conn->error;
          // Optionally, delete the uploaded file if metadata saving fails
          unlink($file_path);
        }
        $insert_stmt->close();
      } else {
        $error_message = "Error moving uploaded file.";
      }
    } else {
      $error_message = "Invalid file type or size. Allowed types: " . implode(', ', $allowed_extensions) . ". Max size: 10MB.";
    }
  } else {
    $error_message = "Error uploading file: " . ($_FILES['document_file']['error'] ?? 'Unknown error');
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
                <h1 class="mb-4">Upload Documents for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Upload Document</h4>
                  </div>
                  <div class="card-body">
                    <form action="upload_documents.php?engagement_id=<?php echo $engagement_id; ?>" method="POST" enctype="multipart/form-data">
                      <div class="mb-3">
                        <label for="document_type" class="form-label">Document Type</label>
                        <select class="form-select" id="document_type" name="document_type">
                          <option value="Other">Other</option>
                          <option value="Working Paper">Working Paper</option>
                          <option value="Client Provided Document">Client Provided Document</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="document_file" class="form-label">Select File</label>
                        <input class="form-control" type="file" id="document_file" name="document_file">
                        <div class="form-text">Allowed file types: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG. Max size: 10MB.</div>
                      </div>
                      <button type="submit" class="btn btn-primary">Upload Document</button>
                    </form>
                  </div>
                </div>

                <div class="mt-4">
                  <a href="engagement_details.php?engagement_id=<?php echo $engagement_id; ?>" class="btn btn-secondary">Back to Engagement Details</a>
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