<?php
session_start();
if (!isset($_SESSION["userName"])) {
    header("Location: login.html");
    exit;
}

$username = $_SESSION["userName"];

$con = mysqli_connect("localhost", "root", "", "hardwaredeals");
if (!$con) {
    die("Cannot connect to DB Server");
}

// fetch user by email OR name (safe-ish prepared statement)
$stmt = mysqli_prepare($con, "SELECT uEmail, `name`, address, contact, profilePic, isSeller, ordersCompleted FROM users WHERE uEmail = ? OR `name` = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ss", $username, $username);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$user = null;
if ($res && mysqli_num_rows($res) > 0) {
    $user = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profile - Hardware Deals</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* small inline adjustments to fit existing theme */
    .profile-wrapper { max-width: 1000px; margin: 24px auto; padding: 0 16px; }
    .profile-card { display:flex; gap:18px; align-items:center; background:#fff; padding:18px; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,0.08); }
    .profile-card img { width:140px; height:140px; object-fit:cover; border-radius:8px; }
    .profile-details { flex:1; }
    .profile-actions { margin-top:12px; }
    .profile-actions button { margin-right:8px; }
    table.profile-table { width:100%; border-collapse:collapse; margin-top:14px; }
    table.profile-table th, table.profile-table td { border:1px solid #e0e0e0; padding:8px; text-align:left; }
  </style>
</head>
<body>
  <!-- ...existing header (same theme as index.php) ... -->
  <header class="title-header-thing">
    <div class="logo-title">
      <img src="logo.png" alt="Logo" class="logo">
      <h1 class="title" onclick="window.location.href='index.php'">Hardware Deals.lk</h1>
    </div>

    <div class="search-box">
      <input type="text" placeholder="Search products...">
      <button type="submit">🔍</button>
    </div>

    <div class="cart">
      <button style="align-items: center; justify-content: center; width: 24px; height: 24px; padding: 0; font-size: 24px; color: black; font-weight: 300; line-height: 1; text-align: center; border: none;" onclick="window.location.href='addproduct.php'">+</button>
      <button>
        <?php if (!isset($_SESSION["userName"])) { ?>

          <img src="login.png" alt="Login" style="width:24px; height:24px;" onclick="window.location.href='login.html'">
        <?php
        } else {
        ?>
          <img src="<?php echo htmlspecialchars($user['profilePic'] ? $user['profilePic'] : 'profile-placeholder.png'); ?>" alt="Profile" style="width:24px; height:24px;" onclick="window.location.href='viewProfile.php'">
        <?php } ?>
      </button>
      <button>
        <img src="cart-icon.webp" alt="Cart" style=" height:24px;" onclick="window.location.href='cart.html'">
      </button>
    </div>
  </header>

  <div class="profile-wrapper">
<?php if (!$user): ?>
    <div class="card">
      <div class="card-content">
        <h2>User not found</h2>
        <p>Could not find your profile in the database.</p>
      </div>
    </div>
<?php else: ?>
    <div class="profile-card">
      <img src="<?php echo htmlspecialchars($user['profilePic'] ? $user['profilePic'] : 'profile-placeholder.png'); ?>" alt="Profile Picture" >
      <div class="profile-details">
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['uEmail']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($user['contact']); ?></p>
        <p><strong>Role:</strong> <?php echo ($user['isSeller'] ? 'Seller' : 'Buyer'); ?>
            <button>Switch to <?php echo ($user['isSeller'] ? 'Buyer' : 'Seller'); ?>?</button>
        </p>
        <p><strong>Orders Completed:</strong> <?php echo htmlspecialchars($user['ordersCompleted']); ?></p>

        <div class="profile-actions">
          <button onclick="window.location.href='editProfile.php'">Edit Profile</button>
          <button onclick="window.location.href='index.php'">Back to Shop</button>
          <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
      </div>
    </div>

    <?php if ($user['isSeller']): ?>
      <h3 style="margin-top:18px;">Your Products</h3>
      <!-- Seller products list placeholder -->
      <table class="profile-table">
        <thead>
          <tr>
            <th>Product ID</th>
            <th>Title</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- populateLater: fetch seller products backend here and output rows -->
          <!-- Example placeholder row; remove when backend is added -->
          <tr>
            <td colspan="5" style="text-align:center; color:#666;">No products shown here. Backend not implemented. <!-- populateLater --></td>
          </tr>
        </tbody>
      </table>
    <?php else: ?>
      <h3 style="margin-top:18px;">Completed Orders</h3>
      <!-- Buyer completed orders placeholder -->
      <table class="profile-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Items</th>
            <th>Total</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <!-- populateLater: fetch completed orders backend here and output rows -->
          <tr>
            <td colspan="5" style="text-align:center; color:#666;">No orders shown here. Backend not implemented. <!-- populateLater --></td>
          </tr>
        </tbody>
      </table>
    <?php endif; ?>
<?php endif; ?>
  </div>

  <!-- ...existing footer (same theme as index.php) ... -->
  <footer class="footer" >
    <div class="footer-inside">
      <div class="footr2" >
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
        <p><a href="trackorder.html">Track My Order</a></p>
      </div>
    </div>
  </footer>
</body>
</html>
