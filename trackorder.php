<?php
session_start();

// DB connection (same style as checkout.php)
$con = mysqli_connect("localhost", "root", "", "hardwaredeals");
if (!$con) {
  die("Cannot connect to DB Server");
}

// Read orderID from URL (checkout redirects here with ?orderID=...)
$orderID = null;
$errorMsg = "";
$orderRows = [];
$overallStatus = null;

if (isset($_GET["orderID"]) && $_GET["orderID"] !== "") {
  if (ctype_digit($_GET["orderID"])) {
    $orderID = (int)$_GET["orderID"];

    // If user is logged in, only show their own orders
    $hasLogin = isset($_SESSION["userName"]) && $_SESSION["userName"] !== "";
    $uEmail = $hasLogin ? $_SESSION["userName"] : null;

    if ($hasLogin) {
      $stmt = mysqli_prepare(
        $con,
        "SELECT oc.orderID, oc.productID, oc.qty, oc.status, p.title
         FROM ordercommited oc
         JOIN products p ON p.productID = oc.productID
         WHERE oc.orderID = ? AND oc.uEmail = ?
         ORDER BY oc.productID"
      );
      mysqli_stmt_bind_param($stmt, "is", $orderID, $uEmail);
    } else {
      $stmt = mysqli_prepare(
        $con,
        "SELECT oc.orderID, oc.productID, oc.qty, oc.status, p.title
         FROM ordercommited oc
         JOIN products p ON p.productID = oc.productID
         WHERE oc.orderID = ?
         ORDER BY oc.productID"
      );
      mysqli_stmt_bind_param($stmt, "i", $orderID);
    }

    if (!mysqli_stmt_execute($stmt)) {
      $errorMsg = "Something went wrong while loading your order.";
    } else {
      $res = mysqli_stmt_get_result($stmt);
      while ($r = mysqli_fetch_assoc($res)) {
        $orderRows[] = $r;
      }
    }

    if (!$errorMsg && count($orderRows) === 0) {
      $errorMsg = "No order found for that Order ID" . ($hasLogin ? " under your account." : ".");
    } else {
      // Pick an overall status (best progress among items)
      $priority = ["pending" => 1, "processing" => 2, "shipped" => 3, "delivered" => 4, "cancelled" => 0];
      $best = -999;
      foreach ($orderRows as $r) {
        $s = strtolower(trim($r["status"] ?? ""));
        $p = $priority[$s] ?? 2; // default processing
        if ($p > $best) {
          $best = $p;
          $overallStatus = $s ?: "processing";
        }
      }
      if (!$overallStatus) $overallStatus = "processing";
    }
  } else {
    $errorMsg = "Please enter a valid numeric Order ID.";
  }
}

function status_to_step_index(string $status): int {
  $s = strtolower(trim($status));
  // 0: Order Placed, 1: Processing, 2: Shipped, 3: Delivered
  if ($s === "delivered") return 3;
  if ($s === "shipped") return 2;
  if ($s === "processing") return 1;
  if ($s === "pending") return 1; // pending = processing stage
  return 1;
}

function step_class(int $current, int $step): string {
  if ($step < $current) return "done";
  if ($step === $current) return "active";
  return "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Track Your Order | Hardware Deals</title>
  <link rel="stylesheet" href="track.css" />
  <link rel="stylesheet" href="style.css">
  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #0f1724; color: #fff; }
    .title-header-thing { text-align: center; padding: 20px 0; font-size: 24px; font-weight: 700; background: #121c2a; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .track-section { padding: 40px 16px; display: flex; justify-content: center; }
    .track-card { width: min(720px, 100%); background: #121c2a; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 22px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
    #trackForm { display: flex; gap: 10px; margin: 16px 0 8px; }
    #trackForm input { flex: 1; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: #0f1724; color: #fff; outline: none; }
    #trackForm button { padding: 12px 16px; border-radius: 10px; border: none; cursor: pointer; background: #38bdf8; color: #0b1220; font-weight: 700; }
    .result { margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.08); }
    .progress { list-style: none; padding: 0; margin: 10px 0 16px; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .step { text-align: center; padding: 10px 8px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); opacity: 0.65; font-size: 14px; }
    .step.done { opacity: 1; background: rgba(34,197,94,0.18); border-color: rgba(34,197,94,0.35); }
    .step.active { opacity: 1; background: rgba(56,189,248,0.18); border-color: rgba(56,189,248,0.35); }
    .status-card { margin-top: 12px; padding: 14px; border-radius: 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); }
    .error-box { margin-top: 12px; padding: 12px 14px; border-radius: 12px; background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); }
    .muted { opacity: 0.85; }
  </style>
</head>
<body>
  <header>
    <div class="title-header-thing" onclick="location.href='index.php'">Hardware Deals.lk</div>
  </header>

  <section class="track-section">
    <div class="track-card">
      <h2>Track Your Order</h2>
      <p class="muted">Enter your Order ID below to check the latest status.</p>

      <form id="trackForm" method="GET" action="trackorder.php">
        <input
          type="text"
          name="orderID"
          id="orderId"
          placeholder="Enter your Order ID"
          value="<?php echo isset($_GET["orderID"]) ? htmlspecialchars($_GET["orderID"]) : ""; ?>"
          required
        />
        <button type="submit">Track</button>
      </form>

      <?php if ($errorMsg): ?>
        <div class="error-box"><?php echo htmlspecialchars($errorMsg); ?></div>
      <?php endif; ?>

      <?php if ($orderID !== null && !$errorMsg && count($orderRows) > 0): ?>
        <?php $currentStep = status_to_step_index($overallStatus); ?>
        <div id="result" class="result">
          <h3>Order Status</h3>

          <ul class="progress">
            <li class="step <?php echo step_class($currentStep, 0); ?>">Order Placed</li>
            <li class="step <?php echo step_class($currentStep, 1); ?>">Processing</li>
            <li class="step <?php echo step_class($currentStep, 2); ?>">Shipped</li>
            <li class="step <?php echo step_class($currentStep, 3); ?>">Delivered</li>
          </ul>

          <p><strong>Order ID:</strong> <?php echo htmlspecialchars((string)$orderID); ?></p>
          <p><strong>Overall Status:</strong> <?php echo htmlspecialchars(ucfirst($overallStatus)); ?></p>

          <?php foreach ($orderRows as $row): ?>
            <div class="status-card">
              <p><strong>Product name:</strong> <?php echo htmlspecialchars($row["title"]); ?></p>
              <p><strong>Product ID:</strong> <?php echo htmlspecialchars((string)$row["productID"]); ?></p>
              <p><strong>Quantity:</strong> <?php echo htmlspecialchars((string)$row["qty"]); ?></p>
              <p><strong>Current Status:</strong> <?php echo htmlspecialchars(ucfirst($row["status"])); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <footer>
    <div class="footer">
      <div class="footr1">
        <h3>Hardware Deals.lk</h3>
        <p>Your trusted source for hardware tools and rentals in Sri Lanka.</p>
        <p>&copy; 2024 HardwareDeals.lk</p>
      </div>
      <div class="footr2">
        <h3>Contact Us</h3>
        <p>Email: <a href="mailto:info@hardwaredeals.lk">info@hardwaredeals.lk</a></p>
        <p>Phone: <a href="tel:+94111234567">011 1 1234567</a></p>
        <p><a href="about.html">About Us</a></p>
        <p><a href="terms.html">Terms & Conditions</a></p>
      </div>
    </div>
  </footer>
</body>
</html>
