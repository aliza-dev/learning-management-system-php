<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once __DIR__ . '/room_booking_functions.php';
require_once __DIR__ . '/fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die('Missing booking id');
}

$booking_id = (int)$_GET['id'];

$booking = rb_get_booking($conn, $booking_id);

if (!$booking) {
    die('Booking not found');
}

$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : '';
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($user_type === 'faculty' && $booking['faculty_id'] !== $user_id) {
    die('Access denied');
}

class BookingPDF extends FPDF
{
}

$pdf = new BookingPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Room Booking Request', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'Faculty Name:', 0, 0);
$pdf->Cell(0, 8, $booking['first_name'] . ' ' . $booking['last_name'], 0, 1);
$pdf->Cell(50, 8, 'Department:', 0, 0);
$pdf->Cell(0, 8, $booking['department_name'] ? $booking['department_name'] : 'N/A', 0, 1);
$pdf->Cell(50, 8, 'Room:', 0, 0);
$pdf->Cell(0, 8, $booking['room_name'], 0, 1);
$pdf->Cell(50, 8, 'Location:', 0, 0);
$pdf->Cell(0, 8, $booking['location'], 0, 1);
$pdf->Cell(50, 8, 'Date:', 0, 0);
$pdf->Cell(0, 8, $booking['booking_date'], 0, 1);
$pdf->Cell(50, 8, 'Time Slot:', 0, 0);
$pdf->Cell(0, 8, $booking['time_slot'], 0, 1);
$pdf->Cell(50, 8, 'Event Title:', 0, 0);
$pdf->Cell(0, 8, $booking['event_title'], 0, 1);
$pdf->Cell(50, 8, 'Number of Persons:', 0, 0);
$pdf->Cell(0, 8, (string)$booking['num_persons'], 0, 1);
$pdf->Cell(50, 8, 'Status:', 0, 0);
$pdf->Cell(0, 8, $booking['status'], 0, 1);

$dir = __DIR__ . '/uploads/booking_pdfs';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$file_name = 'booking_' . $booking_id . '.pdf';
$file_path = $dir . '/' . $file_name;
$file_url = 'uploads/booking_pdfs/' . $file_name;

$pdf->Output('F', $file_path);

rb_set_booking_pdf_path($conn, $booking_id, $file_url);

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $file_name . '"');
readfile($file_path);
exit;


