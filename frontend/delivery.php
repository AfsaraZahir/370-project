<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['driver','admin'])) {
    header('Location: login.php'); exit;
}
require_once '../config/db.php';
$role = $_SESSION['role'];
$uid  = $_SESSION['user_id'];

if ($role === 'driver') {
    $result = mysqli_query($conn,
        "SELECT d.delivery_id, d.order_id, d.delivery_status, d.current_location,
                o.total_amount, c.full_name, c.address
         FROM Delivery d
         JOIN Orders o ON d.order_id = o.order_id
         JOIN Customer c ON o.customer_id = c.customer_id
         WHERE d.driver_id = $uid OR d.delivery_status = 'pending'
         ORDER BY d.delivery_id DESC");
} else {
    $result = mysqli_query($conn,
        "SELECT d.delivery_id, d.order_id, d.delivery_status, d.current_location,
                o.total_amount, c.full_name, c.address, dr.full_name AS driver_name
         FROM Delivery d
         JOIN Orders o ON d.order_id = o.order_id
         JOIN Customer c ON o.customer_id = c.customer_id
         LEFT JOIN Driver dr ON d.driver_id = dr.driver_id
         ORDER BY d.delivery_id DESC");
}
$deliveries = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PizzApp – Delivery</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
  <h2><?= $role === 'driver' ? 'My Deliveries' : 'All Deliveries' ?></h2>
  <?php foreach ($deliveries as $d): ?>
  <div class="card">
    <p><strong>Order #<?= $d['order_id'] ?></strong> — <?= htmlspecialchars($d['full_name']) ?></p>
    <p>Address: <?= htmlspecialchars($d['address']) ?></p>
    <p>Status: <?= $d['delivery_status'] ?> | Total: $<?= $d['total_amount'] ?></p>
    <?php if (isset($d['driver_name'])): ?>
      <p>Driver: <?= htmlspecialchars($d['driver_name'] ?? 'Unassigned') ?></p>
    <?php endif; ?>
    <?php if ($role === 'driver'): ?>
    <form action="../backend/delivery/update_delivery.php" method="POST">
      <input type="hidden" name="order_id" value="<?= $d['order_id'] ?>">
      <input type="text" name="current_location" placeholder="Current location"
             value="<?= htmlspecialchars($d['current_location'] ?? '') ?>">
      <select name="delivery_status">
        <option value="assigned">Assigned</option>
        <option value="out_for_delivery">Out for Delivery</option>
        <option value="delivered">Delivered</option>
      </select>
      <button type="submit">Update</button>
    </form>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
</body>
</html>