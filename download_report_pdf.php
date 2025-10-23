<?php
session_start();
require_once 'database/db_connection.php';
require_once 'includes/fpdf.php'; // Adjust path if FPDF is in a different subdirectory

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Client')) {
  header("Location: login.php");
  exit();
}

$report_id = $_GET['report_id'] ?? 0;
$report_type = $_GET['report_type'] ?? ''; // 'Audit Report' or 'Management Letter'

if ($report_id > 0 && !empty($report_type)) {
  $table_name = ($report_type === 'Audit Report') ? 'audit_reports' : 'management_letters';

  $id_column = ($report_type === 'Audit Report') ? 'report_id' : 'letter_id';
  $stmt = $conn->prepare("SELECT r.title, r.content, e.engagement_year, e.period, c.client_name FROM $table_name r JOIN engagements e ON r.engagement_id = e.engagement_id JOIN clients c ON e.client_id = c.client_id WHERE r.$id_column = ?");
  $stmt->bind_param("i", $report_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $report_data = $result->fetch_assoc();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, $report_data['title'], 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Client: ' . $report_data['client_name'], 0, 1, 'L');
    $pdf->Cell(0, 10, 'Engagement Year: ' . $report_data['engagement_year'] . ' - ' . $report_data['period'], 0, 1, 'L');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 10);
    // FPDF does not directly support HTML. We need to convert content to plain text or format it manually.
    // For simplicity, we'll just output the raw content. For rich text, you'd need to parse HTML or use a library that supports it.
    $pdf->MultiCell(0, 5, $report_data['content']);

    $pdf->Output('D', str_replace(' ', '_', $report_data['title']) . '.pdf');
    exit();
  } else {
    echo "Report not found.";
  }
  $stmt->close();
} else {
  echo "Invalid report ID or type.";
}

$conn->close();
?>
