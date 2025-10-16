<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin')) {
  header("Location: login.php");
  exit();
}

$engagement_id = $_GET['engagement_id'] ?? 0;
$engagement = null;
$success_message = '';
$error_message = '';

// Define upload directory
$upload_dir = '../uploads/working_papers/';
if (!is_dir($upload_dir)) {
  mkdir($upload_dir, 0777, true);
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
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $uploaded_by_user_id = $_SESSION['user_id'];

    if ($action === 'add') {
      if (isset($_FILES['working_paper_file']) && $_FILES['working_paper_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['working_paper_file']['tmp_name'];
        $file_name = basename($_FILES['working_paper_file']['name']);
        $unique_file_name = uniqid() . '_' . $file_name;
        $dest_path = $upload_dir . $unique_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
          $stmt = $conn->prepare("INSERT INTO working_papers (engagement_id, title, description, file_path, file_name, uploaded_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
          $stmt->bind_param("issssi", $engagement_id, $title, $description, $dest_path, $file_name, $uploaded_by_user_id);
          if ($stmt->execute()) {
            $success_message = "Working paper uploaded successfully!";
          } else {
            $error_message = "Error saving working paper details to database: " . $conn->error;
            unlink($dest_path); // Delete uploaded file if DB insert fails
          }
          $stmt->close();
        } else {
          $error_message = "Error moving uploaded file.";
        }
      } else {
        $error_message = "Error uploading file: " . ($_FILES['working_paper_file']['error'] ?? 'Unknown error');
      }
    } elseif ($action === 'edit') {
      $paper_id = $_POST['paper_id'] ?? 0;
      $stmt = $conn->prepare("UPDATE working_papers SET title = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE paper_id = ? AND engagement_id = ?");
      $stmt->bind_param("ssii", $title, $description, $paper_id, $engagement_id);
      if ($stmt->execute()) {
        $success_message = "Working paper updated successfully!";
      } else {
        $error_message = "Error updating working paper: " . $conn->error;
      }
      $stmt->close();
    }
  }
}

// Handle Delete Working Paper
if (isset($_GET['delete_id']) && $engagement) {
  $paper_id = $_GET['delete_id'];

  // First, get the file path to delete the physical file
  $file_path_stmt = $conn->prepare("SELECT file_path FROM working_papers WHERE paper_id = ? AND engagement_id = ?");
  $file_path_stmt->bind_param("ii", $paper_id, $engagement_id);
  $file_path_stmt->execute();
  $file_path_result = $file_path_stmt->get_result();
  if ($file_path_result->num_rows === 1) {
    $file_to_delete = $file_path_result->fetch_assoc()['file_path'];
    if (file_exists($file_to_delete)) {
      unlink($file_to_delete); // Delete physical file
    }
  }
  $file_path_stmt->close();

  // Then delete the database record
  $stmt = $conn->prepare("DELETE FROM working_papers WHERE paper_id = ? AND engagement_id = ?");
  $stmt->bind_param("ii", $paper_id, $engagement_id);
  if ($stmt->execute()) {
    $success_message = "Working paper deleted successfully!";
  } else {
    $error_message = "Error deleting working paper: " . $conn->error;
  }
  $stmt->close();
  header("Location: working_papers.php?engagement_id=" . $engagement_id . "&message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch existing working papers for this engagement
$working_papers = [];
if ($engagement) {
  $result = $conn->query("SELECT wp.*, u.username FROM working_papers wp JOIN users u ON wp.uploaded_by_user_id = u.user_id WHERE wp.engagement_id = $engagement_id ORDER BY wp.uploaded_at DESC");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $working_papers[] = $row;
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
                <h1 class="mb-4">Working Papers for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Upload New Working Paper Form -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Upload New Working Paper</h4>
                  </div>
                  <div class="card-body">


                    <form action="working_papers.php?engagement_id=<?php echo $engagement_id; ?>" method="POST" enctype="multipart/form-data">
                      <input type="hidden" name="action" value="add">
                      <div class="row">
                        <div class="mt-3 col-md-6">
                          <input type="text" class="form-control" placeholder="Title" id="title" name="title" required>
                        </div>
                        <div class="mt-3 col-md-6">
                          <textarea class="form-control" id="description" placeholder="Description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3 col-md-6">
                          <input class="form-control" type="file" placeholder="Select File" id="working_paper_file" name="working_paper_file" required>
                          <div class="form-text">Accepted formats: PDF, Excel (xlsx, xls), Word (docx, doc).</div>
                        </div>


                        <div class="mt-3 col-md-6">
                          <button type="submit" class="btn btn-primary btn-icon btn-round "><i class="fas fa-cloud-upload-alt"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- List of Working Papers -->
                <div class="card">
                  <div class="card-header">
                    <h4>Existing Working Papers</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($working_papers)): ?>
                      <p>No working papers found for this engagement.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped table-hover" id="basic-datatables">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Title</th>
                              <th>Description</th>
                              <th>File Name</th>
                              <th>Uploaded By</th>
                              <th>Uploaded At</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($working_papers as $paper): ?>
                              <tr>

                                <td><?php echo htmlspecialchars($paper['paper_id']); ?></td>
                                <td><?php echo htmlspecialchars($paper['title']); ?></td>
                                <td><?php echo htmlspecialchars($paper['description']); ?></td>
                                <td><a href="<?php echo htmlspecialchars($paper['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($paper['file_name']); ?></a></td>
                                <td><?php echo htmlspecialchars($paper['username']); ?></td>
                                <td><?php echo htmlspecialchars($paper['uploaded_at']); ?></td>
                                <td class="d-flex">
                                  
                                      <button type="button" class="btn btn-icon btn-round btn-primary text-white me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editWorkingPaperModal"
                                        data-id="<?php echo $paper['paper_id']; ?>"
                                        data-title="<?php echo htmlspecialchars($paper['title']); ?>"
                                        data-description="<?php echo htmlspecialchars($paper['description']); ?>">
                                        <i class="fas fa-edit"></i>
                                      </button>
                                    
                                      <a href="working_papers.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $paper['paper_id']; ?>" class="btn btn-icon btn-round btn-danger text-white" onclick="return confirm('Are you sure you want to delete this working paper?');"><i class="fas fa-trash"></i></a>
                                    
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
      <!-- Edit Working Paper Modal -->
      <div class="modal fade" id="editWorkingPaperModal" tabindex="-1" aria-labelledby="editWorkingPaperModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editWorkingPaperModalLabel">Edit Working Paper</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="working_papers.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="paper_id" id="edit_paper_id">
                <div class="mb-3">
                  <input type="text" class="form-control" placeholder="Title" id="edit_title" name="title" required>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="edit_description" placeholder="Description" name="description" rows="3"></textarea>
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
    $('#editWorkingPaperModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var title = button.data('title');
      var description = button.data('description');

      var modal = $(this);
      modal.find('.modal-title').text('Edit Working Paper: ' + title);
      modal.find('#edit_paper_id').val(id);
      modal.find('#edit_title').val(title);
      modal.find('#edit_description').val(description);
    });
  </script>
</body>

</html>