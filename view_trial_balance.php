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
$trial_balance_data = [];
$error_message = '';

if ($engagement_id > 0) {
  // Fetch engagement details
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

  // Fetch trial balance data if engagement found
  if ($engagement) {
    $stmt = $conn->prepare("SELECT account_code, account_name, debit, credit FROM trial_balance WHERE engagement_id = ? ORDER BY account_code ASC");
    $stmt->bind_param("i", $engagement_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $trial_balance_data[] = $row;
    }
    $stmt->close();
  }
} else {
  $error_message = "No engagement ID provided.";
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
              <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo $error_message; ?>
                </div>
                <a href="my_engagements.php" class="btn btn-secondary">Back to My Engagements</a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Trial Balance for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

                <?php if (empty($trial_balance_data)): ?>
                  <div class="alert alert-info" role="alert">
                    No trial balance data found for this engagement. Please upload it via the "Data Import & Trial Balance" section.
                  </div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>Account Code</th>
                          <th>Account Name</th>
                          <th class="text-end">Debit</th>
                          <th class="text-end">Credit</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $total_debit = 0;
                        $total_credit = 0;
                        foreach ($trial_balance_data as $row):
                          $total_debit += $row['debit'];
                          $total_credit += $row['credit'];
                        ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['account_code']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                            <td class="text-end"><?php echo number_format($row['debit'], 2); ?></td>
                            <td class="text-end"><?php echo number_format($row['credit'], 2); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <th colspan="2" class="text-end">Total:</th>
                          <th class="text-end"><?php echo number_format($total_debit, 2); ?></th>
                          <th class="text-end"><?php echo number_format($total_credit, 2); ?></th>
                        </tr>
                        <?php if (abs($total_debit - $total_credit) > 0.01): // Allow for minor floating point inaccuracies 
                        ?>
                          <tr>
                            <th colspan="4" class="text-danger text-center">
                              Trial Balance is out of balance! Difference: <?php echo number_format($total_debit - $total_credit, 2); ?>
                            </th>
                          </tr>
                        <?php endif; ?>
                      </tfoot>
                    </table>
                  </div>
                <?php endif; ?>

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