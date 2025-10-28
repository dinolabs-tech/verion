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
$balance_sheet_data = [
  'assets' => [],
  'liabilities' => [],
  'equity' => []
];
$total_assets = 0;
$total_liabilities = 0;
$total_equity = 0;
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
      $balance = $row['debit'] - $row['credit'];

      // Simple categorization based on account names/codes
      // This is a basic example and might need more sophisticated logic for real-world scenarios
      if (preg_match('/^(cash|bank|accounts receivable|inventory|prepaid|land|building|equipment|accumulated depreciation)/i', $row['account_name']) || in_array(substr($row['account_code'], 0, 1), ['1'])) {
        $balance_sheet_data['assets'][] = ['name' => $row['account_name'], 'balance' => $balance];
        $total_assets += $balance;
      } elseif (preg_match('/^(accounts payable|notes payable|salaries payable|unearned revenue|loan payable)/i', $row['account_name']) || in_array(substr($row['account_code'], 0, 1), ['2'])) {
        $balance_sheet_data['liabilities'][] = ['name' => $row['account_name'], 'balance' => -$balance]; // Liabilities typically have credit balances
        $total_liabilities += -$balance;
      } elseif (preg_match('/^(capital|owner\'s equity|retained earnings)/i', $row['account_name']) || in_array(substr($row['account_code'], 0, 1), ['3'])) {
        $balance_sheet_data['equity'][] = ['name' => $row['account_name'], 'balance' => -$balance]; // Equity typically has credit balances
        $total_equity += -$balance;
      }
      // Revenue and Expense accounts would typically go to an Income Statement,
      // but for a simple balance sheet, we might include their net effect in equity
      // For this example, we'll assume they are already closed to equity or handled elsewhere.
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
                <a href="my_engagements.php" class="btn btn-secondary btn-icon btn-round"><i class="fas fa-arrow-left"></i></a>
              <?php elseif ($engagement): ?>
                <h1 class="mb-4">Balance Sheet for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

                <?php if (empty($balance_sheet_data['assets']) && empty($balance_sheet_data['liabilities']) && empty($balance_sheet_data['equity'])): ?>
                  <div class="alert alert-info" role="alert">
                    No balance sheet data found for this engagement. Please ensure trial balance data is uploaded.
                  </div>
                <?php else: ?>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                          <h4>Assets</h4>
                        </div>
                        <ul class="list-group list-group-flush">
                          <?php foreach ($balance_sheet_data['assets'] as $asset): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              <?php echo htmlspecialchars($asset['name']); ?>
                              <span><?php echo number_format($asset['balance'], 2); ?></span>
                            </li>
                          <?php endforeach; ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center fw-bold bg-light">
                            Total Assets
                            <span><?php echo number_format($total_assets, 2); ?></span>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                          <h4>Liabilities</h4>
                        </div>
                        <ul class="list-group list-group-flush">
                          <?php foreach ($balance_sheet_data['liabilities'] as $liability): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              <?php echo htmlspecialchars($liability['name']); ?>
                              <span><?php echo number_format($liability['balance'], 2); ?></span>
                            </li>
                          <?php endforeach; ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center fw-bold bg-light">
                            Total Liabilities
                            <span><?php echo number_format($total_liabilities, 2); ?></span>
                          </li>
                        </ul>
                      </div>
                      <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                          <h4>Equity</h4>
                        </div>
                        <ul class="list-group list-group-flush">
                          <?php foreach ($balance_sheet_data['equity'] as $equity_item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              <?php echo htmlspecialchars($equity_item['name']); ?>
                              <span><?php echo number_format($equity_item['balance'], 2); ?></span>
                            </li>
                          <?php endforeach; ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center fw-bold bg-light">
                            Total Equity
                            <span><?php echo number_format($total_equity, 2); ?></span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-4">
                    <div class="card-header">
                      <h4>Balance Sheet Summary</h4>
                    </div>
                    <div class="card-body">
                      <p class="fw-bold">Total Assets: <?php echo number_format($total_assets, 2); ?></p>
                      <p class="fw-bold">Total Liabilities & Equity: <?php echo number_format($total_liabilities + $total_equity, 2); ?></p>
                      <?php if (abs($total_assets - ($total_liabilities + $total_equity)) > 0.01): ?>
                        <div class="alert alert-danger" role="alert">
                          Balance Sheet is out of balance! Difference: <?php echo number_format($total_assets - ($total_liabilities + $total_equity), 2); ?>
                        </div>
                      <?php else: ?>
                        <div class="alert alert-success" role="alert">
                          Balance Sheet is in balance.
                        </div>
                      <?php endif; ?>
                    </div>
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