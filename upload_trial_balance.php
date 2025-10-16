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
  if (isset($_FILES['trial_balance_file']) && $_FILES['trial_balance_file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_path = $_FILES['trial_balance_file']['tmp_name'];
    $file_name = $_FILES['trial_balance_file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if ($file_ext === 'csv' || $file_ext === 'xlsx') {
      // For simplicity, we'll handle CSV here. XLSX would require a library like PhpSpreadsheet.
      if ($file_ext === 'csv') {
        $handle = fopen($file_tmp_path, "r");
        if ($handle !== FALSE) {
          // Skip header row
          fgetcsv($handle);

          // Clear existing trial balance entries for this engagement before import
          $conn->begin_transaction();
          try {
            $delete_stmt = $conn->prepare("DELETE FROM trial_balance WHERE engagement_id = ?");
            $delete_stmt->bind_param("i", $engagement_id);
            $delete_stmt->execute();
            $delete_stmt->close();

            $insert_stmt = $conn->prepare("INSERT INTO trial_balance (engagement_id, account_code, account_name, debit, credit) VALUES (?, ?, ?, ?, ?)");
            $imported_rows = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
              // Assuming CSV format: account_code, account_name, debit, credit
              if (count($data) >= 4) {
                $account_code = trim($data[0]);
                $account_name = trim($data[1]);
                $debit = (float)trim($data[2]);
                $credit = (float)trim($data[3]);

                $insert_stmt->bind_param("issdd", $engagement_id, $account_code, $account_name, $debit, $credit);
                $insert_stmt->execute();
                $imported_rows++;
              }
            }
            $insert_stmt->close();
            $conn->commit();
            $success_message = "Trial Balance CSV imported successfully! " . $imported_rows . " rows processed.";
          } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error importing CSV: " . $e->getMessage();
          }
          fclose($handle);
        } else {
          $error_message = "Could not open the uploaded CSV file.";
        }
      } else {
        $error_message = "XLSX import not yet supported. Please upload a CSV file.";
      }
    } else {
      $error_message = "Invalid file type. Please upload a CSV or XLSX file.";
    }
  } else {
    $error_message = "Error uploading file: " . ($_FILES['trial_balance_file']['error'] ?? 'Unknown error');
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
                <a href="my_engagements.php" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Upload Trial Balance for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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
                    <h4>Upload Trial Balance File (CSV)</h4>
                  </div>
                  <div class="card-body">
                    <form action="upload_trial_balance.php?engagement_id=<?php echo $engagement_id; ?>" method="POST" enctype="multipart/form-data">
                      <div class="mb-3">
                        <label for="trial_balance_file" class="form-label">Select CSV File</label>
                        <input class="form-control" type="file" id="trial_balance_file" name="trial_balance_file" accept=".csv" required>
                        <div class="form-text">Please upload a CSV file with columns: `account_code`, `account_name`, `debit`, `credit`.</div>
                      </div>
                      <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-cloud-upload-alt"></i></button>
                    </form>
                  </div>
                </div>

                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Download Trial Balance Template</h4>
                  </div>
                  <div class="card-body">
                    <p>If you need a template to get started, you can download our standard CSV trial balance template:</p>
                    <a href="templates/trial_balance_template.csv" class="btn btn-info btn-icon btn-round" download="trial_balance_template.csv">
                      <i class="fas fa-cloud-download-alt"></i>
                    </a>
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

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
</body>

</html>