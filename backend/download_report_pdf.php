<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if report exists in session
if (!isset($_SESSION['latest_report'])) {
    header('Location: ../frontend/report.php');
    exit();
}

$report = $_SESSION['latest_report'];


require_once('../tools/library/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('InnerPeace Mental Wellness');
$pdf->SetAuthor('InnerPeace System');
$pdf->SetTitle('Mental Wellness Report - ' . ucfirst($report['report_type']));
$pdf->SetSubject('Mental Health Assessment Report');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(TRUE, 20);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 11);

// Title
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(64, 53, 10);
$pdf->Cell(0, 15, 'Mental Wellness Report', 0, 1, 'C');

// Report Type and Date Range
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(112, 111, 78);
$pdf->Cell(0, 8, ucfirst($report['report_type']) . ' Report', 0, 1, 'C');
$pdf->Cell(0, 8, $report['date_range']['label'], 0, 1, 'C');
$pdf->Cell(0, 8, 'Generated on ' . $report['generated_at'], 0, 1, 'C');

$pdf->Ln(5);

// Horizontal line
$pdf->SetDrawColor(185, 197, 180);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());

$pdf->Ln(10);

// Data Summary Section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(64, 53, 10);
$pdf->Cell(0, 10, 'Report Overview', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);

// Create a table for data summary
$html = '<table border="0" cellpadding="8" style="width: 100%;">
    <tr style="background-color: #B9C5B4;">
        <td style="width: 25%; text-align: center;"><strong>PHQ-9 Assessments</strong><br/><span style="font-size: 18px; color: #3B82F6;">' . $report['data_summary']['phq_count'] . '</span></td>
        <td style="width: 25%; text-align: center;"><strong>GAD-7 Assessments</strong><br/><span style="font-size: 18px; color: #A855F7;">' . $report['data_summary']['gad_count'] . '</span></td>
        <td style="width: 25%; text-align: center;"><strong>Journal Entries</strong><br/><span style="font-size: 18px; color: #10B981;">' . $report['data_summary']['journal_count'] . '</span></td>
        <td style="width: 25%; text-align: center;"><strong>Chat Messages</strong><br/><span style="font-size: 18px; color: #6366F1;">' . $report['data_summary']['chat_count'] . '</span></td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Ln(10);

// Report Content
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(64, 53, 10);
$pdf->Cell(0, 10, 'Detailed Analysis', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);

// Function to convert markdown to HTML for PDF
function formatMarkdownForPDF($text) {
    // Convert markdown headers to HTML with proper styling for PDF
    // ### Header (h3)
    $text = preg_replace('/^### (.+)$/m', '<h3 style="font-size: 14pt; font-weight: bold; margin-top: 10px; margin-bottom: 5px; color: #40350A;">$1</h3>', $text);
    
    // ## Header (h2)
    $text = preg_replace('/^## (.+)$/m', '<h2 style="font-size: 16pt; font-weight: bold; margin-top: 12px; margin-bottom: 6px; color: #40350A;">$1</h2>', $text);
    
    // # Header (h1)
    $text = preg_replace('/^# (.+)$/m', '<h1 style="font-size: 18pt; font-weight: bold; margin-top: 15px; margin-bottom: 8px; color: #40350A;">$1</h1>', $text);
    
    // Convert **bold** to <strong>
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
    
    // Convert *italic* to <em>
    $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
    
    return $text;
}

// Convert line breaks to HTML and format markdown
$content = formatMarkdownForPDF($report['content']);
$content = nl2br(htmlspecialchars($content));
$content = '<div style="text-align: justify; line-height: 1.6;">' . htmlspecialchars_decode($content) . '</div>';

$pdf->writeHTML($content, true, false, true, false, '');

$pdf->Ln(10);

// Footer disclaimer
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(128, 128, 128);
$disclaimer = "This report is for informational purposes only and is not a substitute for professional medical advice, diagnosis, or treatment. If you're experiencing severe symptoms, please consult with a qualified mental health professional.";
$pdf->MultiCell(0, 5, $disclaimer, 0, 'C');

// Close and output PDF document
$filename = 'Mental_Wellness_Report_' . ucfirst($report['report_type']) . '_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D'); // 'D' means download
exit();
?>