<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once '../config/db.php';
$role = $_SESSION['role'];
$uid  = $_SESSION['user_id'];

if ($role === 'customer') {
    $result = mysqli_query($conn,
        "SELECT o.order_id, o.status, o.total_amount, o.created_at,
                d.discount_name, d.discount_percent
         FROM Orders o
         LEFT JOIN Discounts d ON o.discount_id = d.discount_id
         WHERE o.customer_id = $uid
         ORDER BY o.created_at DESC");
} else {
    $result = mysqli_query($conn,
        "SELECT o.order_id, o.status, o.total_amount, o.created_at,
                c.full_name, d.discount_name
         FROM Orders o
         JOIN Customer c ON o.customer_id = c.customer_id
         LEFT JOIN Discounts d ON o.discount_id = d.discount_id
         ORDER BY o.created_at DESC");
}
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PizzApp – Orders</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .status-trail { display:flex; gap:4px; flex-wrap:wrap; margin-top:6px; }
    .status-trail span {
      padding:2px 8px; border-radius:4px; font-size:0.72rem;
    }
    .status-active { background:#c0392b; color:white; }
    .status-done   { background:#e8b4b0; color:#7b1a12; }
    .status-pending{ background:#eee;    color:#999; }
    .stats-box {
      background:white; border-radius:8px; padding:20px;
      margin-bottom:20px; box-shadow:0 1px 6px rgba(0,0,0,0.08);
      display:flex; gap:40px;
    }
    .stats-box div { text-align:center; }
    .stats-box .num { font-size:2rem; font-weight:bold; color:#c0392b; }
    .stats-box .lbl { font-size:0.85rem; color:#666; margin-top:4px; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
  <h2><?= $role === 'customer' ? 'My Orders' : 'All Orders' ?></h2>

  <?php if ($role === 'customer'):
    $stats = mysqli_fetch_assoc(mysqli_query($conn,
      "SELECT total_orders, total_spending
       FROM Customer WHERE customer_id = $uid"));
  ?>
  <div class="stats-box">
    <div>
      <div class="num"><?= $stats['total_orders'] ?></div>
      <div class="lbl">Total Orders</div>
    </div>
    <div>
      <div class="num">$<?= number_format($stats['total_spending'], 2) ?></div>
      <div class="lbl">Total Spending</div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($orders)): ?>
    <p>No orders found. <?= $role === 'customer' ? '<a href="order.php">Order now!</a>' : '' ?></p>
  <?php else: ?>
  <table>
    <tr>
      <th>Order #</th>
      <?php if ($role !== 'customer'): ?><th>Customer</th><?php endif; ?>
      <th>Status</th>
      <th>Total</th>
      <th>Discount</th>
      <th>Date</th>
    </tr>
    <?php
    $statuses = ['received','preparing','baking','out_for_delivery','delivered'];
    foreach ($orders as $o):
      $current = array_search($o['status'], $statuses);
    ?>
    <tr>
      <td>#<?= $o['order_id'] ?></td>
      <?php if ($role !== 'customer'): ?>
        <td><?= htmlspecialchars($o['full_name']) ?></td>
      <?php endif; ?>
      <td>
        <div class="status-trail">
          <?php foreach ($statuses as $i => $s): ?>
            <span class="<?= $i < $current ? 'status-done' : ($i === $current ? 'status-active' : 'status-pending') ?>">
              <?= str_replace('_', ' ', $s) ?>
            </span>
          <?php endforeach; ?>
        </div>
      </td>
      <td>$<?= number_format($o['total_amount'], 2) ?></td>
      <td><?= $o['discount_name'] ?? 'None' ?></td>
      <td><?= $o['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
</body>
</html>