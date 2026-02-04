<?php
function rb_get_time_slots()
{
    return [
        '08:00-09:00',
        '09:00-10:00',
        '10:00-11:00',
        '11:00-12:00',
        '12:00-13:00',
        '13:00-14:00',
        '14:00-15:00',
        '15:00-16:00',
        '16:00-17:00',
        '17:00-18:00'
    ];
}

function rb_get_rooms($conn)
{
    $rooms = [];
    $sql = "SELECT id, name, location, capacity, is_active FROM rooms ORDER BY name ASC";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
        $result->free();
    }
    return $rooms;
}

function rb_create_room($conn, $name, $location, $capacity, $is_active)
{
    $stmt = $conn->prepare("INSERT INTO rooms (name, location, capacity, is_active) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $name, $location, $capacity, $is_active);
    $stmt->execute();
    $stmt->close();
}

function rb_update_room($conn, $id, $name, $location, $capacity, $is_active)
{
    $stmt = $conn->prepare("UPDATE rooms SET name = ?, location = ?, capacity = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $name, $location, $capacity, $is_active, $id);
    $stmt->execute();
    $stmt->close();
}

function rb_delete_room($conn, $id)
{
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get slot statuses for a specific room and date
 * Returns array: ['time_slot' => 'status'] where status is 'Approved', 'Pending', 'Rejected', or null
 */
function rb_get_slot_statuses($conn, $room_id, $booking_date)
{
    $slots = [];
    $stmt = $conn->prepare("SELECT time_slot, status FROM room_bookings WHERE room_id = ? AND booking_date = ?");
    $stmt->bind_param("is", $room_id, $booking_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $slots[$row['time_slot']] = $row['status'];
    }
    $stmt->close();
    return $slots;
}

/**
 * Get the status of a specific slot
 * Returns: 'Approved', 'Pending', 'Rejected', or null if slot is available
 */
function rb_get_slot_status($conn, $room_id, $booking_date, $time_slot)
{
    $stmt = $conn->prepare("SELECT status FROM room_bookings WHERE room_id = ? AND booking_date = ? AND time_slot = ? LIMIT 1");
    $stmt->bind_param("iss", $room_id, $booking_date, $time_slot);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['status'] : null;
}

/**
 * Check if a slot can be booked based on strict validation rules
 * Returns: array with 'can_book' (bool) and 'reason' (string)
 * 
 * Rules:
 * - Approved: CANNOT book (blocked)
 * - Pending: CANNOT book (blocked)
 * - Rejected: CAN book (allowed for new request)
 * - No record: CAN book (available)
 */
function rb_can_book_slot($conn, $room_id, $booking_date, $time_slot, $is_focal_person = false)
{
    $status = rb_get_slot_status($conn, $room_id, $booking_date, $time_slot);
    
    if ($status === 'Approved') {
        return [
            'can_book' => false,
            'reason' => 'Already Booked',
            'status' => 'Approved'
        ];
    }
    
    if ($status === 'Pending') {
        return [
            'can_book' => false,
            'reason' => 'Pending Approval',
            'status' => 'Pending'
        ];
    }
    
    if ($status === 'Rejected') {
        // Rejected slots can be re-booked (especially by focal person)
        return [
            'can_book' => true,
            'reason' => 'Rejected',
            'status' => 'Rejected'
        ];
    }
    
    // No record exists - slot is available
    return [
        'can_book' => true,
        'reason' => 'Available',
        'status' => null
    ];
}

/**
 * STRICT backend validation for booking requests
 * This function MUST be called before creating any booking to prevent bypassing UI
 * 
 * @param mysqli $conn Database connection
 * @param int $room_id Room ID
 * @param string $booking_date Booking date (Y-m-d format)
 * @param string $time_slot Time slot string
 * @param bool $is_focal_person Whether the requester is a focal person
 * @return array ['valid' => bool, 'error' => string|null]
 */
function rb_validate_booking_request($conn, $room_id, $booking_date, $time_slot, $is_focal_person = false)
{
    // Validate inputs
    if ($room_id <= 0) {
        return ['valid' => false, 'error' => 'Invalid room ID'];
    }
    
    if (empty($booking_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_date)) {
        return ['valid' => false, 'error' => 'Invalid booking date format'];
    }
    
    if (empty($time_slot)) {
        return ['valid' => false, 'error' => 'Time slot is required'];
    }
    
    // Check slot status
    $slot_check = rb_can_book_slot($conn, $room_id, $booking_date, $time_slot, $is_focal_person);
    
    if (!$slot_check['can_book']) {
        return [
            'valid' => false,
            'error' => 'Slot is not available: ' . $slot_check['reason']
        ];
    }
    
    return ['valid' => true, 'error' => null];
}

// Legacy function for backward compatibility
function rb_get_booked_slots($conn, $room_id, $booking_date)
{
    $statuses = rb_get_slot_statuses($conn, $room_id, $booking_date);
    $slots = [];
    foreach ($statuses as $slot => $status) {
        // Only mark as "booked" if Approved or Pending (blocked slots)
        if ($status === 'Approved' || $status === 'Pending') {
            $slots[$slot] = true;
        }
    }
    return $slots;
}

/**
 * Create a new booking with STRICT validation
 * Backend validation ensures Approved and Pending slots cannot be booked
 * 
 * @param mysqli $conn Database connection
 * @param int $faculty_id Faculty member ID
 * @param int|null $department_id Department ID
 * @param int $room_id Room ID
 * @param string $booking_date Booking date (Y-m-d format)
 * @param string $time_slot Time slot string
 * @param string $event_title Event title
 * @param int $num_persons Number of persons
 * @param bool $is_focal_person Whether the requester is a focal person
 * @return int|false Booking ID on success, false on failure
 */
function rb_create_booking($conn, $faculty_id, $department_id, $room_id, $booking_date, $time_slot, $event_title, $num_persons, $is_focal_person = false)
{
    // STRICT backend validation - blocks Approved and Pending slots
    $validation = rb_validate_booking_request($conn, $room_id, $booking_date, $time_slot, $is_focal_person);
    
    if (!$validation['valid']) {
        return false;
    }
    
    // Additional validation
    if ($faculty_id <= 0 || $room_id <= 0 || empty($event_title) || $num_persons <= 0) {
        return false;
    }
    
    // If slot was previously rejected, we can create a new booking
    // Otherwise, ensure slot is truly available (no existing record)
    $existing_status = rb_get_slot_status($conn, $room_id, $booking_date, $time_slot);
    
    // Double-check: if status exists and is not Rejected, block it
    if ($existing_status !== null && $existing_status !== 'Rejected') {
        return false;
    }
    
    // Insert new booking with Pending status
    $stmt = $conn->prepare("INSERT INTO room_bookings (faculty_id, department_id, room_id, booking_date, time_slot, event_title, num_persons, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iiisssi", $faculty_id, $department_id, $room_id, $booking_date, $time_slot, $event_title, $num_persons);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function rb_get_faculty_bookings($conn, $faculty_id)
{
    $items = [];
    $sql = "SELECT b.*, r.name AS room_name, r.location, d.name AS department_name
            FROM room_bookings b
            INNER JOIN rooms r ON b.room_id = r.id
            LEFT JOIN departments d ON b.department_id = d.depart_id
            WHERE b.faculty_id = ?
            ORDER BY b.booking_date DESC, b.time_slot ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
    return $items;
}

function rb_get_all_bookings($conn)
{
    $items = [];
    $sql = "SELECT b.*, r.name AS room_name, r.location, d.name AS department_name, f.first_name, f.last_name
            FROM room_bookings b
            INNER JOIN rooms r ON b.room_id = r.id
            LEFT JOIN departments d ON b.department_id = d.depart_id
            INNER JOIN faculty f ON b.faculty_id = f.id
            ORDER BY b.booking_date DESC, b.time_slot ASC";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $result->free();
    }
    return $items;
}

function rb_update_booking_status($conn, $booking_id, $status)
{
    $allowed = ['Pending', 'Approved', 'Rejected'];
    if (!in_array($status, $allowed, true)) {
        return;
    }
    $stmt = $conn->prepare("UPDATE room_bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $stmt->close();
}

function rb_get_booking($conn, $booking_id)
{
    $sql = "SELECT b.*, r.name AS room_name, r.location, d.name AS department_name, f.first_name, f.last_name
            FROM room_bookings b
            INNER JOIN rooms r ON b.room_id = r.id
            LEFT JOIN departments d ON b.department_id = d.depart_id
            INNER JOIN faculty f ON b.faculty_id = f.id
            WHERE b.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}

function rb_get_booking_pdf_path($conn, $booking_id)
{
    $stmt = $conn->prepare("SELECT pdf_path FROM room_booking_pdfs WHERE booking_id = ? LIMIT 1");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['pdf_path'] : null;
}

function rb_set_booking_pdf_path($conn, $booking_id, $pdf_path)
{
    $existing = rb_get_booking_pdf_path($conn, $booking_id);
    if ($existing !== null) {
        $stmt = $conn->prepare("UPDATE room_booking_pdfs SET pdf_path = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $pdf_path, $booking_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO room_booking_pdfs (booking_id, pdf_path) VALUES (?, ?)");
        $stmt->bind_param("is", $booking_id, $pdf_path);
        $stmt->execute();
        $stmt->close();
    }
}

function rb_get_calendar_data($conn, $room_id, $year, $month)
{
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = date('Y-m-t', strtotime($start));
    $data = [];
    $stmt = $conn->prepare("SELECT booking_date, status FROM room_bookings WHERE room_id = ? AND booking_date BETWEEN ? AND ?");
    $stmt->bind_param("iss", $room_id, $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $day = (int)date('j', strtotime($row['booking_date']));
        if (!isset($data[$day])) {
            $data[$day] = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
        }
        if (isset($data[$day][$row['status']])) {
            $data[$day][$row['status']]++;
        }
    }
    $stmt->close();
    return $data;
}


