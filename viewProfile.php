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

if (isset($_POST['switchRole']) && isset($_POST['userEmail'])) {
  $newRole = $_POST['switchRole'] === 'Seller' ? 1 : 0;
  $userEmail = $_POST['userEmail'];
  if (!$user) {
    header("Location: login.html");
    exit;
  }

  if ($newRole === (int)$user['isSeller']) {

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  } else if ($newRole === 1) {
    //if switching to seller, also create store entry
    $updateStmt = mysqli_prepare($con, "UPDATE users SET isSeller = ? WHERE uEmail = ?");
    mysqli_stmt_bind_param($updateStmt, "is", $newRole, $userEmail);
    mysqli_stmt_execute($updateStmt);

    $sql = "INSERT INTO `stores` (`storeContactUEmail`, `storeName`, `storeBio`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `storeContactUEmail` = `storeContactUEmail`";
    $storeName = $user['name'];

    $storeBio = "Welcome to " . $storeName . "'s store!";
    $insertStmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($insertStmt, "sss", $userEmail, $storeName, $storeBio);
    mysqli_stmt_execute($insertStmt);
  } else {
    //if switching to buyer, no extra action needed
    $updateStmt = mysqli_prepare($con, "UPDATE users SET isSeller = ? WHERE uEmail = ?");
    mysqli_stmt_bind_param($updateStmt, "is", $newRole, $userEmail);
    mysqli_stmt_execute($updateStmt);
  }
  // Update the user's role in the database


  // Refresh the page to reflect changes
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}



if (isset($_POST['deleteProduct']) && isset($_POST['productID'])) {
  $productIDToDelete = $_POST['productID'];

  // Delete the product from products table
  $deleteSql = "DELETE FROM products WHERE productID = ?";
  $stmt = mysqli_prepare($con, $deleteSql);
  mysqli_stmt_bind_param($stmt, "i", $productIDToDelete);
  mysqli_stmt_execute($stmt);

  // Redirect to the same page to reflect changes
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
if (isset($_POST['editProduct']) && isset($_POST['productID'])) {
  $productIDToEdit = $_POST['productID'];

  // Redirect to edit product page with productID as a GET parameter
  header("Location: addproduct.php?id=" . urlencode($productIDToEdit));
  exit;
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
    .profile-wrapper {
      max-width: 1000px;
      margin: 24px auto;
      padding: 0 16px;
    }

    .profile-card {
      display: flex;
      gap: 18px;
      align-items: center;
      background: #fff;
      padding: 18px;
      border-radius: 8px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .profile-card img {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border-radius: 8px;
    }

    .profile-details {
      flex: 1;
    }

    .profile-actions {
      margin-top: 12px;
    }

    .profile-actions button {
      margin-right: 8px;
    }

    table.profile-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
    }

    table.profile-table th,
    table.profile-table td {
      border: 1px solid #e0e0e0;
      padding: 8px;
      text-align: left;
    }
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
        <img src="cart-icon.webp" alt="Cart" style=" height:24px;" onclick="window.location.href='cart.php'">
      </button>
    </div>
  </header>

  <div class="profile-wrapper">
    <?php if (!$user) { ?>
      <div class="card">
        <div class="card-content">
          <h2>User not found</h2>
          <p>Could not find your profile in the database.</p>
        </div>
      </div>
    <?php } else {
      $profilePic = (!$user || empty($user['profilePic'])) ? 'profile-placeholder.png' : $user['profilePic'];
    }
    ?>
    <div class="profile-card">
      <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
      <div class="profile-details">
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['uEmail']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($user['contact']); ?></p>
        <strong>Role:</strong> <?php echo ($user['isSeller'] ? 'Seller' : 'Buyer'); ?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" name="userEmail" value="<?php echo htmlspecialchars($user['uEmail']); ?>">
          <button type="submit" name="switchRole" value="<?php echo ($user['isSeller'] ? 'Buyer' : 'Seller'); ?>">Switch to <?php echo ($user['isSeller'] ? 'Buyer' : 'Seller'); ?>?</button>
        </form>

        <p><strong>Orders Completed:</strong> <?php echo htmlspecialchars($user['ordersCompleted']); ?></p>

        <div class="profile-actions">
          <button onclick="window.location.href='editProfile.php'">Edit Profile</button>
          <button onclick="window.location.href='index.php'">Back to Shop</button>
          <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
      </div>
    </div>

    <?php if ($user['isSeller']) { ?>
      <h3 style="margin-top:18px;">Your Products</h3>

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

          <?php
          $sql = "SELECT storeID FROM stores WHERE storeContactUEmail = ?";
          $stmt = mysqli_prepare($con, $sql);
          mysqli_stmt_bind_param($stmt, "s", $user['uEmail']);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);

          if ($result && mysqli_num_rows($result) > 0) {
            $storeRow = mysqli_fetch_assoc($result);
            $storeID = (int)$storeRow['storeID'];

            $sql = "SELECT * FROM products WHERE soldByStoreID = ?";
            $stmt2 = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt2, "i", $storeID);
            mysqli_stmt_execute($stmt2);
            $productsResult = mysqli_stmt_get_result($stmt2);

            if ($productsResult && mysqli_num_rows($productsResult) > 0) {
              while ($product = mysqli_fetch_assoc($productsResult)) { ?>
                <tr>
                  <td><?php echo htmlspecialchars($product['productID']); ?></td>
                  <td><?php echo htmlspecialchars($product['title']); ?></td>
                  <td>Rs.<?php echo number_format((float)$product['newPrice'], 2); ?></td>
                  <td>
                    <?php
                    echo ($product['inStock']
                      ? (is_null($product['stockQty']) ? "In Stock" : intval($product['stockQty']) . " units")
                      : "Out of Stock");
                    ?>
                  </td>
                  <td>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                      <input type="hidden" name="productID" value="<?php echo htmlspecialchars($product['productID']); ?>">
                      <button type="submit" name="editProduct">Edit</button>
                      <button type="submit" name="deleteProduct" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                    </form>
                  </td>
                </tr>
          <?php
              }
            } else {
              echo "<tr><td colspan='5' style='text-align:center; color:#666;'>No products yet</td></tr>";
            }
          } else {
            echo "<tr><td colspan='5' style='text-align:center; color:#666;'>No store found for this seller</td></tr>";
          }
          ?>


        </tbody>
      </table>
    <?php } else { ?>
      <h3 style="margin-top:18px;">Completed Orders</h3>

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
          <?php
          // Completed orders for THIS user (uses ordercommited + products, and uses orders table if available)
          $sqlOrders = "
    SELECT
      oc.orderID,
      COALESCE(o.orderDate, NULL) AS orderDate,
      COALESCE(o.status, oc.status) AS status,
      GROUP_CONCAT(CONCAT(p.title, ' x', oc.qty) SEPARATOR ', ') AS items,
      SUM(COALESCE(p.newPrice, 0) * oc.qty) AS total
    FROM ordercommited oc
    LEFT JOIN orders o
      ON o.orderID = oc.orderID AND o.uEmail = oc.uEmail
    JOIN products p
      ON p.productID = oc.productID
    WHERE oc.uEmail = ?
      AND LOWER(COALESCE(o.status, oc.status)) IN ('completed','delivered')
    GROUP BY oc.orderID, o.orderDate, COALESCE(o.status, oc.status)
    ORDER BY o.orderDate DESC, oc.orderID DESC
  ";

          $stmtO = mysqli_prepare($con, $sqlOrders);
          mysqli_stmt_bind_param($stmtO, "s", $user['uEmail']);
          mysqli_stmt_execute($stmtO);
          $ordersRes = mysqli_stmt_get_result($stmtO);

          if ($ordersRes && mysqli_num_rows($ordersRes) > 0) {
            while ($oRow = mysqli_fetch_assoc($ordersRes)) {
              $dateTxt = $oRow['orderDate'] ? htmlspecialchars($oRow['orderDate']) : "-";
              $statusTxt = htmlspecialchars($oRow['status']);
              $itemsTxt = htmlspecialchars($oRow['items']);

              $totalVal = (float)$oRow['total'];
              $totalTxt = ($totalVal <= 0) ? "Call for price" : "Rs." . number_format($totalVal, 2);
          ?>
              <tr>
                <td><?php echo htmlspecialchars($oRow['orderID']); ?></td>
                <td><?php echo $itemsTxt; ?></td>
                <td><?php echo $totalTxt; ?></td>
                <td><?php echo $dateTxt; ?></td>
                <td><?php echo $statusTxt; ?></td>
              </tr>
            <?php
            }
          } else {
            ?>
            <tr>
              <td colspan="5" style="text-align:center; color:#666;">No completed orders yet</td>
            </tr>
          <?php } ?>
        </tbody>

      </table>
    <?php } ?>
    <?php  ?>
  </div>

  <!-- ...existing footer (same theme as index.php) ... -->
  <footer class="footer">
    <div class="footer-inside">
      <div class="footr2">
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