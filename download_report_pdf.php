<?php
session_start();
require_once 'database/db_connection.php';
require_once 'includes/fpdf.php'; // Adjust path if FPDF is in a different subdirectory

// Only Auditor or Admin can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Auditor' && $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Client' && $_SESSION['role'] !== 'Reviewer')) {
  header("Location: login.php");
  exit();
}

$report_id = $_GET['report_id'] ?? 0;
$report_type = $_GET['report_type'] ?? ''; // 'Audit Report' or 'Management Letter'

if ($report_id > 0 && !empty($report_type)) {
  $table_name = ($report_type === 'Audit Report') ? 'audit_reports' : 'management_letters';

  $id_column = ($report_type === 'Audit Report') ? 'report_id' : 'letter_id';
  $stmt = $conn->prepare("SELECT r.title, r.content, e.engagement_year, e.period, c.client_name,
                                 CONCAT(au.first_name, ' ', au.last_name) AS auditor_name,
                                 CONCAT(rev.first_name, ' ', rev.last_name) AS reviewer_name,
                                 r.reviewer_approved
                          FROM $table_name r
                          JOIN engagements e ON r.engagement_id = e.engagement_id
                          JOIN clients c ON e.client_id = c.client_id
                          LEFT JOIN users au ON e.assigned_auditor_id = au.user_id
                          LEFT JOIN users rev ON e.assigned_reviewer_id = rev.user_id
                          WHERE r.$id_column = ?");
  $stmt->bind_param("i", $report_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $report_data = $result->fetch_assoc();

    // Check for reviewer approval if the current user is a client
    if ($_SESSION['role'] === 'Client' && isset($report_data['reviewer_name']) && $report_data['reviewer_name'] !== null && $report_data['reviewer_approved'] == 0) {
        echo "This report is awaiting reviewer approval and cannot be downloaded by the client yet.";
        exit();
    }

    $pdf = new FPDF();
    $pdf->AddPage();

    // Get current Y position for consistent alignment
    $y_start = $pdf->GetY();

    // Branding Logo on the far left
    // Assuming logoproduct.svg is the logo. Adjust path and dimensions as needed.
    $logo_path = 'assets/img/logo.jpg';
    $logo_width = 30; // Adjust as needed
    $logo_height = 30; // Adjust as needed
    // FPDF does not natively support SVG. If this fails, the SVG image needs to be converted to PNG/JPG or an FPDF extension for SVG needs to be used.
    $pdf->Image($logo_path, 10, $y_start, $logo_width, $logo_height); // x=10, y=current Y

    // Verion Details on the right, on the same row
    // Move cursor to the right of the logo for the text
    $pdf->SetXY(10 + $logo_width + 5, $y_start); // 5 units padding after logo
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'VERION', 0, 1, 'R'); // 0 width means extend to right margin, 1 means new line

    // Subsequent lines for Verion details
    $pdf->SetX(10 + $logo_width + 5); // Reset X for next line
    $pdf->Cell(0, 5, '5th floor, Wing-B, TISCO building', 0, 1, 'R');
    $pdf->SetX(10 + $logo_width + 5);
    $pdf->Cell(0, 5, 'Alagbaka, Akure, Ondo state, Nigeria.', 0, 1, 'R');
    $pdf->SetX(10 + $logo_width + 5);
    $pdf->Cell(0, 5, 'enquiries@dinolabstech.com', 0, 1, 'R');
    $pdf->SetX(10 + $logo_width + 5);
    $pdf->Cell(0, 5, '+234 704 324 7461', 0, 1, 'R');

    // Advance Y position after the logo and text block
    $pdf->SetY(max($y_start + $logo_height, $pdf->GetY())); // Ensure Y is below both logo and text
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, $report_data['title'], 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 5, 'Client: ' . $report_data['client_name'], 0, 1, 'L');
    $pdf->Cell(0, 5, 'Engagement Year: ' . $report_data['engagement_year'] . ' - ' . $report_data['period'], 0, 1, 'L');
    $pdf->Cell(0, 5, 'Auditor: ' . $report_data['auditor_name'], 0, 1, 'L');
    if (!empty($report_data['reviewer_name'])) {
      $pdf->Cell(0, 5, 'Reviewer: ' . $report_data['reviewer_name'], 0, 1, 'L');
    }
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
