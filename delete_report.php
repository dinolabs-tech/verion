<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin')) {
  header("Location: login.php");
  exit();
}

$report_id = $_GET['report_id'] ?? 0;
$report_type = $_GET['report_type'] ?? ''; // 'Audit Report' or 'Management Letter'

if ($report_id > 0 && !empty($report_type)) {
  $table_name = ($report_type === 'Audit Report') ? 'audit_reports' : 'management_letters';

  $id_column = ($report_type === 'Audit Report') ? 'report_id' : 'letter_id';
  $stmt = $conn->prepare("DELETE FROM $table_name WHERE $id_column = ?");
  $stmt->bind_param("i", $report_id);

  if ($stmt->execute()) {
    $_SESSION['success_message'] = "Report deleted successfully!";
  } else {
    $_SESSION['error_message'] = "Error deleting report: " . $conn->error;
  }
  $stmt->close();
} else {
  $_SESSION['error_message'] = "Invalid report ID or type provided.";
}

$conn->close();
header("Location: view_reports.php");
exit();
?>
