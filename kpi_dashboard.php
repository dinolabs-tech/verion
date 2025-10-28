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
$kpi_data = [];
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

  // Fetch trial balance data if engagement found to calculate KPIs
  if ($engagement) {
    $trial_balance_accounts = [];
    $stmt = $conn->prepare("SELECT account_code, account_name, debit, credit FROM trial_balance WHERE engagement_id = ?");
    $stmt->bind_param("i", $engagement_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $trial_balance_accounts[$row['account_name']] = ['debit' => $row['debit'], 'credit' => $row['credit']];
    }
    $stmt->close();

    // --- KPI Calculations (Basic Examples) ---
    $current_assets = 0;
    $current_liabilities = 0;
    $total_revenue = 0;
    $cost_of_goods_sold = 0;
    $operating_expenses = 0;
    $net_income = 0; // Simplified for this example

    foreach ($trial_balance_accounts as $name => $balances) {
      $balance = $balances['debit'] - $balances['credit'];

      // Current Assets (example accounts)
      if (preg_match('/^(cash|bank|accounts receivable|inventory|prepaid)/i', $name)) {
        $current_assets += $balance;
      }
      // Current Liabilities (example accounts)
      if (preg_match('/^(accounts payable|notes payable|salaries payable|unearned revenue)/i', $name)) {
        $current_liabilities += -$balance; // Liabilities have credit balances
      }
      // Revenue (example accounts)
      if (preg_match('/^(sales revenue|service revenue)/i', $name)) {
        $total_revenue += -$balance; // Revenue has credit balances
      }
      // Cost of Goods Sold (example accounts)
      if (preg_match('/^(cost of goods sold)/i', $name)) {
        $cost_of_goods_sold += $balance;
      }
      // Operating Expenses (example accounts)
      if (preg_match('/^(rent expense|salary expense|utilities expense|depreciation expense)/i', $name)) {
        $operating_expenses += $balance;
      }
    }

    // Gross Profit (simplified)
    $gross_profit = $total_revenue - $cost_of_goods_sold;
    // Net Income (very simplified, assuming no other income/expenses)
    $net_income = $gross_profit - $operating_expenses;

    // Current Ratio
    $current_ratio = ($current_liabilities > 0) ? ($current_assets / $current_liabilities) : 0;
    // Gross Profit Margin
    $gross_profit_margin = ($total_revenue > 0) ? (($gross_profit / $total_revenue) * 100) : 0;
    // Net Profit Margin
    $net_profit_margin = ($total_revenue > 0) ? (($net_income / $total_revenue) * 100) : 0;


    $kpi_data = [
      'Current Ratio' => ['value' => number_format($current_ratio, 2), 'description' => 'Measures a company\'s ability to pay short-term obligations.'],
      'Gross Profit Margin' => ['value' => number_format($gross_profit_margin, 2) . '%', 'description' => 'Indicates the percentage of revenue left after deducting the cost of goods sold.'],
      'Net Profit Margin' => ['value' => number_format($net_profit_margin, 2) . '%', 'description' => 'Indicates how much net profit a company makes for every dollar of revenue.'],
      // Add more KPIs as needed
    ];
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
                <h1 class="mb-4">KPI Dashboard for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

                <?php if (empty($kpi_data)): ?>
                  <div class="alert alert-info" role="alert">
                    No sufficient data found to calculate KPIs for this engagement. Please ensure trial balance data is uploaded.
                  </div>
                <?php else: ?>
                  <div class="row">
                    <?php foreach ($kpi_data as $kpi_name => $data): ?>
                      <div class="col-md-4 mb-4">
                        <div class="card h-100">
                          <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($kpi_name); ?></h5>
                            <p class="card-text fs-3 fw-bold"><?php echo htmlspecialchars($data['value']); ?></p>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($data['description']); ?></p>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
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