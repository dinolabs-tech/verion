<?php

/**
 * File: anomaly_detection.php
 * Purpose: This file implements the anomaly detection functionality for a specific engagement.
 * It retrieves trial balance data, performs anomaly detection, and displays the results.
 */

session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
/**
 * Check if the user is logged in and has the necessary role (Auditor or Admin).
 * If not, redirect to the login page.
 */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin')) {
  header("Location: login.php");
  exit();
}

// Retrieve the engagement ID from the GET request.  Default to 0 if not provided.
$engagement_id = $_GET['engagement_id'] ?? 0;
// Initialize variables
$engagement = null;
$success_message = '';
$error_message = '';

// If an engagement ID is provided, fetch the engagement details.
if ($engagement_id > 0) {
  // Prepare a SQL statement to fetch engagement details.
  $stmt = $conn->prepare("SELECT e.engagement_id, e.engagement_year, e.period, c.client_name FROM engagements e JOIN clients c ON e.client_id = c.client_id WHERE e.engagement_id = ?");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  // If the engagement is found, store the engagement details.
  if ($result->num_rows === 1) {
    $engagement = $result->fetch_assoc();
  } else {
    $error_message = "Engagement not found.";
  }
  $stmt->close();
} else {
  $error_message = "No engagement ID provided.";
}

// If engagement details were successfully retrieved, proceed with anomaly detection.
if ($engagement) {
  // Fetch trial balance data for the engagement
  // Prepare a SQL statement to fetch trial balance data.
  $stmt = $conn->prepare("SELECT account_code, debit, credit FROM trial_balance WHERE engagement_id = ?");
  $stmt->bind_param("i", $engagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $trial_balance_data = [];
  // Store the trial balance data in an associative array.
  while ($row = $result->fetch_assoc()) {
    $trial_balance_data[$row['account_code']] = [
      'debit' => $row['debit'],
      'credit' => $row['credit']
    ];
  }
  $stmt->close();

  // Anomaly Detection Logic (Replace with actual implementation)
  $anomaly_results = [];
  // Iterate through the trial balance data to detect anomalies.
  foreach ($trial_balance_data as $account_code => $data) {
    $debit = $data['debit'];
    $credit = $data['credit'];

    // Example: Flag accounts with unusually high debit or credit amounts
    $threshold = 1000000; // Example threshold
    // Check if the debit or credit amount exceeds the threshold.
    if ($debit > $threshold || $credit > $threshold) {
      // Calculate an anomaly score based on the debit or credit amount.
      $anomaly_score = ($debit > $credit) ? ($debit / $threshold) : ($credit / $threshold);
      $description = "Unusually high " . (($debit > $credit) ? "debit" : "credit") . " amount.";
      $anomaly_results[] = [
        'account_code' => $account_code,
        'anomaly_score' => $anomaly_score,
        'description' => $description
      ];
    }
  }

  // Store Anomaly Detection Results in the database
  // Start a database transaction to ensure atomicity.
  $conn->begin_transaction();
  try {
    // Clear existing anomaly detection results for this engagement
    $delete_stmt = $conn->prepare("DELETE FROM anomaly_detection_results WHERE engagement_id = ?");
    $delete_stmt->bind_param("i", $engagement_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Insert new anomaly detection results
    $insert_stmt = $conn->prepare("INSERT INTO anomaly_detection_results (engagement_id, account_code, anomaly_score, description) VALUES (?, ?, ?, ?)");
    foreach ($anomaly_results as $result) {
      $account_code = $result['account_code'];
      $anomaly_score = $result['anomaly_score'];
      $description = $result['description'];
      $insert_stmt->bind_param("isds", $engagement_id, $account_code, $anomaly_score, $description);
      $insert_stmt->execute();
    }
    $insert_stmt->close();

    $conn->commit();
    $success_message = "Anomaly detection completed successfully! " . count($anomaly_results) . " anomalies found.";
  } catch (Exception $e) {
    $conn->rollback();
    $error_message = "Error during anomaly detection: " . $e->getMessage();
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
                <h1 class="mb-4">Anomaly Detection for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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
                    <h4>Anomaly Detection Results</h4>
                  </div>
                  <div class="card-body">
                    <?php if (empty($anomaly_results)): ?>
                      <p>No anomalies found.</p>
                    <?php else: ?>
                      <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>Account Code</th>
                            <th>Anomaly Score</th>
                            <th>Description</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($anomaly_results as $result): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($result['account_code']); ?></td>
                              <td><?php echo htmlspecialchars(number_format($result['anomaly_score'], 2)); ?></td>
                              <td><?php echo htmlspecialchars($result['description']); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
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

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
</body>

</html>