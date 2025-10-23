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
$trial_balance_data = [];
$adjusted_trial_balance = [];

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

if ($engagement) {
  // Fetch trial balance data
  $tb_result = $conn->query("SELECT * FROM trial_balance WHERE engagement_id = $engagement_id ORDER BY account_code");
  if ($tb_result) {
    while ($row = $tb_result->fetch_assoc()) {
      $trial_balance_data[$row['account_code']] = $row;
    }
  }

  // Apply audit adjustments to create an adjusted trial balance
  $adjusted_trial_balance = $trial_balance_data; // Start with original TB
  $adj_result = $conn->query("SELECT * FROM audit_adjustments WHERE engagement_id = $engagement_id ORDER BY posted_at");
  if ($adj_result) {
    while ($adj = $adj_result->fetch_assoc()) {
      $account_code = $adj['account_code'];
      if (!isset($adjusted_trial_balance[$account_code])) {
        // If account not in original TB, add it
        $adjusted_trial_balance[$account_code] = [
          'engagement_id' => $engagement_id,
          'account_code' => $account_code,
          'account_name' => $adj['description'], // Use adjustment description as account name if new
          'debit' => 0,
          'credit' => 0,
          'adjusted_debit' => 0,
          'adjusted_credit' => 0,
        ];
      }
      $adjusted_trial_balance[$account_code]['adjusted_debit'] += $adj['debit'];
      $adjusted_trial_balance[$account_code]['adjusted_credit'] += $adj['credit'];
    }
  }

  // Finalize adjusted balances
  foreach ($adjusted_trial_balance as $code => &$account) {
    $account['final_debit'] = $account['debit'] + $account['adjusted_debit'] - $account['adjusted_credit'];
    $account['final_credit'] = $account['credit'] + $account['adjusted_credit'] - $account['adjusted_debit'];

    // Ensure no negative balances for debit/credit, adjust to zero if negative
    if ($account['final_debit'] < 0) {
      $account['final_credit'] += abs($account['final_debit']);
      $account['final_debit'] = 0;
    }
    if ($account['final_credit'] < 0) {
      $account['final_debit'] += abs($account['final_credit']);
      $account['final_credit'] = 0;
    }
  }
  unset($account); // Break the reference
}

// Function to categorize accounts (simplified example, real systems use chart of accounts)
function getAccountCategory($account_code, $account_name)
{
  $account_name_lower = strtolower($account_name);
  if (preg_match('/^1/', $account_code) || strpos($account_name_lower, 'cash') !== false || strpos($account_name_lower, 'bank') !== false || strpos($account_name_lower, 'receivable') !== false || strpos($account_name_lower, 'inventory') !== false || strpos($account_name_lower, 'asset') !== false) {
    return 'Asset';
  } elseif (preg_match('/^2/', $account_code) || strpos($account_name_lower, 'payable') !== false || strpos($account_name_lower, 'loan') !== false || strpos($account_name_lower, 'liability') !== false) {
    return 'Liability';
  } elseif (preg_match('/^3/', $account_code) || strpos($account_name_lower, 'equity') !== false || strpos($account_name_lower, 'capital') !== false || strpos($account_name_lower, 'retained earnings') !== false) {
    return 'Equity';
  } elseif (preg_match('/^4/', $account_code) || strpos($account_name_lower, 'revenue') !== false || strpos($account_name_lower, 'sales') !== false || strpos($account_name_lower, 'income') !== false) {
    return 'Revenue';
  } elseif (preg_match('/^5/', $account_code) || strpos($account_name_lower, 'expense') !== false || strpos($account_name_lower, 'cost of goods sold') !== false) {
    return 'Expense';
  }
  return 'Other';
}

// Calculate Financial Statement figures
$total_assets = 0;
$total_liabilities = 0;
$total_equity = 0;
$total_revenue = 0;
$total_expenses = 0;

foreach ($adjusted_trial_balance as $account) {
  $category = getAccountCategory($account['account_code'], $account['account_name']);
  $balance = $account['final_debit'] - $account['final_credit']; // Net balance

  switch ($category) {
    case 'Asset':
      $total_assets += $balance;
      break;
    case 'Liability':
      $total_liabilities += $balance;
      break;
    case 'Equity':
      $total_equity += $balance;
      break;
    case 'Revenue':
      $total_revenue += $balance; // Revenue typically has a credit balance, so this will be negative
      break;
    case 'Expense':
      $total_expenses += $balance; // Expenses typically have a debit balance
      break;
  }
}

$net_profit = abs($total_revenue) - $total_expenses; // Assuming revenue is negative from above calculation

// Handle Report Generation (Audit Report / Management Letter)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $engagement && isset($_POST['report_action'])) {
  $report_action = $_POST['report_action'];
  $report_type = $_POST['report_type'] ?? ''; // 'Audit Report' or 'Management Letter'
  $report_version_type = $_POST['report_version_type'] ?? ''; // 'Draft' or 'Final'
  $report_title = $_POST['report_title'] ?? '';
  $report_content = $_POST['report_content'] ?? '';
  $generated_by_user_id = $_SESSION['user_id'];

  if (empty($report_type) || empty($report_version_type) || empty($report_title) || empty($report_content)) {
    $error_message = "All report fields are required.";
  } else {
    // Save report to database
    $table_name = ($report_type === 'Audit Report') ? 'audit_reports' : 'management_letters';
    $stmt = $conn->prepare("INSERT INTO $table_name (engagement_id, report_type, title, content, generated_by_user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $engagement_id, $report_version_type, $report_title, $report_content, $generated_by_user_id);
    if ($stmt->execute()) {
      $new_report_id = $conn->insert_id; // Get the ID of the newly inserted report
      $success_message = $report_type . " (" . $report_version_type . ") generated and saved successfully! <a href=\"download_report_pdf.php?report_id=" . $new_report_id . "&report_type=" . urlencode($report_type) . "\" class=\"btn btn-success btn-sm ms-2\" target=\"_blank\">Download PDF</a>";
    } else {
      $error_message = "Error generating " . $report_type . ": " . $conn->error;
    }
    $stmt->close();
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
                <h1 class="mb-4">Generate Reports for <?php echo htmlspecialchars($engagement['client_name']); ?> (<?php echo htmlspecialchars($engagement['engagement_year']); ?> - <?php echo htmlspecialchars($engagement['period']); ?>)</h1>

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

                <!-- Financial Statements Section -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Financial Statements (Based on Adjusted Trial Balance)</h4>
                  </div>
                  <div class="card-body">
                    <h5>Balance Sheet</h5>
                    <p><strong>Total Assets:</strong> <?php echo htmlspecialchars(number_format($total_assets, 2)); ?></p>
                    <p><strong>Total Liabilities:</strong> <?php echo htmlspecialchars(number_format(abs($total_liabilities), 2)); ?></p>
                    <p><strong>Total Equity:</strong> <?php echo htmlspecialchars(number_format(abs($total_equity), 2)); ?></p>
                    <p class="fw-bold">Assets = Liabilities + Equity: <?php echo (number_format($total_assets, 2) == number_format(abs($total_liabilities) + abs($total_equity), 2)) ? 'Balanced' : 'Unbalanced'; ?></p>
                    <hr>
                    <h5>Income Statement</h5>
                    <p><strong>Total Revenue:</strong> <?php echo htmlspecialchars(number_format(abs($total_revenue), 2)); ?></p>
                    <p><strong>Total Expenses:</strong> <?php echo htmlspecialchars(number_format($total_expenses, 2)); ?></p>
                    <p><strong>Net Profit:</strong> <?php echo htmlspecialchars(number_format($net_profit, 2)); ?></p>
                    <hr>
                    <h5>Cash Flow Statement (Simplified - Placeholder)</h5>
                    <p>Cash Flow Statement generation is complex and typically requires detailed ledger entries categorized into operating, investing, and financing activities. This is a placeholder for future development.</p>
                  </div>
                </div>

                <!-- Generate Audit Report / Management Letter Section -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h4>Generate Audit Report / Management Letter</h4>
                  </div>

                  <div class="card-body">
                    <form action="generate_reports.php?engagement_id=<?php echo $engagement_id; ?>" method="POST">
                      <input type="hidden" name="report_action" value="generate">
                      <div class="row">
                        <div class="mb-3  col-md-4">
                          <select class="form-select" id="report_type" name="report_type" required>
                            <option value="" selected disabled>Select Report Type</option>
                            <option value="Audit Report">Audit Report</option>
                            <option value="Management Letter">Management Letter</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-4">
                          <select class="form-select" id="report_version_type" name="report_version_type" required>
                            <option value="" selected disabled>select Version Type</option>
                            <option value="Draft">Draft</option>
                            <option value="Final">Final</option>
                          </select>
                        </div>
                        <div class="mb-3 col-md-4">
                          <input type="text" class="form-control" id="report_title" placeholder="Report Title" name="report_title" required>
                        </div>
                        <div class="mb-3 col-md-12">
                          <textarea class="form-control" id="report_content" placeholder="Report Content" name="report_content" rows="5" required></textarea>
                        </div>

                        <div class="col-md-12 text-center">
                          <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <div class="mt-4 col-md-12 text-center">
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
