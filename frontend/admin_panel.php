<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}
require_once '../config/db.php';

$customers = mysqli_fetch_all(mysqli_query($conn,
    "SELECT c.full_name, c.phone, c.total_orders, c.total_spending, u.email
     FROM Customer c JOIN Users u ON c.customer_id = u.user_id"), MYSQLI_ASSOC);

$drivers = mysqli_fetch_all(mysqli_query($conn,
    "SELECT d.driver_id, d.full_name, d.phone, d.license_number, d.is_available
     FROM Driver d"), MYSQLI_ASSOC);

$discounts = mysqli_fetch_all(mysqli_query($conn,
    "SELECT * FROM Discounts"), MYSQLI_ASSOC);

$pending = mysqli_fetch_all(mysqli_query($conn,
    "SELECT d.delivery_id, d.order_id, c.full_name, c.address
     FROM Delivery d
     JOIN Orders o ON d.order_id = o.order_id
     JOIN Customer c ON o.customer_id = c.customer_id
     WHERE d.driver_id IS NULL"), MYSQLI_ASSOC);

$available_drivers = mysqli_fetch_all(mysqli_query($conn,
    "SELECT driver_id, full_name FROM Driver WHERE is_available = 1"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PizzApp – Admin Panel</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    h3 { margin: 28px 0 12px; color: #c0392b; border-bottom: 2px solid #c0392b; padding-bottom:6px; }
    .badge {
      padding:3px 10px; border-radius:12px; font-size:0.8rem;
      background:#eee; color:#555;
    }
    .badge.available { background:#d5f5e3; color:#1e8449; }
    .badge.unavailable { background:#fadbd8; color:#922b21; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
  <h2>Admin Panel</h2>

  <!-- CUSTOMERS -->
  <h3>👥 Customers</h3>
  <?php if (empty($customers)): ?>
    <p>No customers yet.</p>
  <?php else: ?>
  <table>
    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th></tr>
    <?php foreach ($customers as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['full_name']) ?></td>
      <td><?= htmlspecialchars($c['email']) ?></td>
      <td><?= $c['phone'] ?></td>
      <td><?= $c['total_orders'] ?></td>
      <td>$<?= number_format($c['total_spending'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <!-- DRIVERS -->
  <h3>🚗 Drivers</h3>
  <?php if (empty($drivers)): ?>
    <p>No drivers yet.</p>
  <?php else: ?>
  <table>
    <tr><th>Name</th><th>Phone</th><th>License</th><th>Status</th></tr>
    <?php foreach ($drivers as $d): ?>
    <tr>
      <td><?= htmlspecialchars($d['full_name']) ?></td>
      <td><?= $d['phone'] ?></td>
      <td><?= $d['license_number'] ?></td>
      <td>
        <span class="badge <?= $d['is_available'] ? 'available' : 'unavailable' ?>">
          <?= $d['is_available'] ? 'Available' : 'On Delivery' ?>
        </span>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <!-- DISCOUNTS -->
  <h3>🎯 Discounts</h3>
  <?php if (empty($discounts)): ?>
    <p>No discounts configured.</p>
  <?php else: ?>
  <table>
    <tr><th>Name</th><th>Min Orders</th><th>Min Spending</th><th>Discount %</th></tr>
    <?php foreach ($discounts as $d): ?>
    <tr>
      <td><?= htmlspecialchars($d['discount_name']) ?></td>
      <td><?= $d['min_orders'] ?? '—' ?></td>
      <td><?= $d['min_spending'] ? '$'.$d['min_spending'] : '—' ?></td>
      <td><?= $d['discount_percent'] ?>%</td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <!-- ASSIGN DRIVER -->
  <h3>🚚 Assign Drivers to Pending Orders</h3>
  <?php if (empty($pending)): ?>
    <p>No unassigned orders right now.</p>
  <?php elseif (empty($available_drivers)): ?>
    <p>No drivers available right now.</p>
  <?php else: ?>
    <?php foreach ($pending as $p): ?>
    <div class="card">
      <p><strong>Order #<?= $p['order_id'] ?></strong> — <?= htmlspecialchars($p['full_name']) ?></p>
      <p>Address: <?= htmlspecialchars($p['address']) ?></p>
      <form action="../backend/admin/assign_driver.php" method="POST">
        <input type="hidden" name="delivery_id" value="<?= $p['delivery_id'] ?>">
        <select name="driver_id">
          <?php foreach ($available_drivers as $dr): ?>
            <option value="<?= $dr['driver_id'] ?>"><?= htmlspecialchars($dr['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Assign Driver</button>
      </form>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
</body>
</html>