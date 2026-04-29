<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); exit;
}
require_once '../../config/db.php';

$delivery_id = (int)$_POST['delivery_id'];
$driver_id   = (int)$_POST['driver_id'];

// Assign driver and update delivery status
$stmt = mysqli_prepare($conn,
    "UPDATE Delivery
     SET driver_id = ?, delivery_status = 'assigned', assigned_at = NOW()
     WHERE delivery_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $driver_id, $delivery_id);
mysqli_stmt_execute($stmt);

// Mark driver as unavailable
$stmt2 = mysqli_prepare($conn,
    "UPDATE Driver SET is_available = 0 WHERE driver_id = ?");
mysqli_stmt_bind_param($stmt2, 'i', $driver_id);
mysqli_stmt_execute($stmt2);

// Sync order status to 'preparing'
$stmt3 = mysqli_prepare($conn,
    "UPDATE Orders o
     JOIN Delivery d ON o.order_id = d.order_id
     SET o.status = 'preparing'
     WHERE d.delivery_id = ?");
mysqli_stmt_bind_param($stmt3, 'i', $delivery_id);
mysqli_stmt_execute($stmt3);

header('Location: ../../frontend/admin_panel.php?assigned=1');
?>