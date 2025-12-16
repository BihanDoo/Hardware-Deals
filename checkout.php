<?php
session_start();

// Require login for checkout
if (!isset($_SESSION["userName"])) {
  header("Location: login.html");
  exit;
}

$username = $_SESSION["userName"];

// Fetch current user for pre-filling checkout details
$con = mysqli_connect("localhost", "root", "", "hardwaredeals");
if (!$con) {
  die("Cannot connect to DB Server");
}

$stmt = mysqli_prepare(
  $con,
  "SELECT uEmail, `name`, address, contact, profilePic 
   FROM users 
   WHERE uEmail = ? OR `name` = ? 
   LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "ss", $username, $username);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$user = $res && mysqli_num_rows($res) > 0 ? mysqli_fetch_assoc($res) : null;

$profilePic = (!$user || empty($user['profilePic']))
  ? 'profile-placeholder.png'
  : $user['profilePic'];



//ordercommited table is used to store the orders that have been committed. same function as cart table but with orderID and status.
// //1	uEmail	varchar(100)		
// 	2	orderID	int			
// 	3	productID	int			
// 	4	qty	int			
// 	5	status	varchar(100)	+

if (isset($_POST['placeOrder'])) {

    $uEmail = $_SESSION["userName"];
    $status = "pending";
  
    // Start transaction so insert+delete happen together
    mysqli_begin_transaction($con);
  
    // 1) Make a new orderID (since ordercommited doesn't look auto-increment)
    $res = mysqli_query($con, "SELECT COALESCE(MAX(orderID),0)+1 AS nextID FROM ordercommited");
    if (!$res) { mysqli_rollback($con); die(mysqli_error($con)); }
    $row = mysqli_fetch_assoc($res);
    $orderID = (int)$row["nextID"];
  
    // 2) Read all items from cart table for this user
    $stmt = mysqli_prepare($con, "SELECT productID, qty FROM cart WHERE uEmail=?");
    mysqli_stmt_bind_param($stmt, "s", $uEmail);
    if (!mysqli_stmt_execute($stmt)) { mysqli_rollback($con); die(mysqli_error($con)); }
    $items = mysqli_stmt_get_result($stmt);
  
    // If no items, stop
    if (mysqli_num_rows($items) === 0) {
      mysqli_rollback($con);
      header("Location: cart.php");
      exit;
    }
  
    // 3) Insert each cart item into ordercommited
    $ins = mysqli_prepare($con,
      "INSERT INTO ordercommited (uEmail, orderID, productID, qty, status)
       VALUES (?, ?, ?, ?, ?)"
    );
  
    while ($it = mysqli_fetch_assoc($items)) {
      $pid = (int)$it["productID"];
      $qty = (int)$it["qty"];
  
      mysqli_stmt_bind_param($ins, "siiis", $uEmail, $orderID, $pid, $qty, $status);
      if (!mysqli_stmt_execute($ins)) { mysqli_rollback($con); die(mysqli_error($con)); }
    }
  
    // 4) Remove items from cart table
    $del = mysqli_prepare($con, "DELETE FROM cart WHERE uEmail=?");
    mysqli_stmt_bind_param($del, "s", $uEmail);
    if (!mysqli_stmt_execute($del)) { mysqli_rollback($con); die(mysqli_error($con)); }
  
    // Done
    mysqli_commit($con);
  
    header("Location: trackorder.php?orderID=" . $orderID);
    exit;
  }
  
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Hardware Deals.lk</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .checkout-wrapper {
      max-width: 1120px;
      margin: 24px auto;
      padding: 0 16px 32px;
    }

    .checkout-layout {
      display: grid;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1.4fr);
      gap: 20px;
      align-items: flex-start;
    }

    @media (max-width: 900px) {
      .checkout-layout {
        grid-template-columns: minmax(0, 1fr);
      }
    }

    .checkout-card {
      background: #fff;
      border-radius: 8px;
      padding: 18px 18px 20px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .checkout-section-title {
      font-size: 1.05rem;
      font-weight: 600;
      margin-bottom: 10px;
      color: #111827;
    }

    .checkout-grid-2 {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px 14px;
    }

    @media (max-width: 720px) {
      .checkout-grid-2 {
        grid-template-columns: minmax(0, 1fr);
      }
    }

    .checkout-field {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-bottom: 10px;
    }

    .checkout-field label {
      font-size: 0.86rem;
      color: #374151;
    }

    .checkout-field input,
    .checkout-field textarea,
    .checkout-field select {
      border: 1px solid #d1d5db;
      border-radius: 6px;
      padding: 8px 10px;
      font-size: 0.9rem;
      font-family: inherit;
    }

    .checkout-field textarea {
      resize: vertical;
      min-height: 70px;
    }

    .checkout-radio-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin: 6px 0 4px;
    }

    .checkout-radio-option {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.9rem;
      padding: 6px 8px;
      border-radius: 6px;
      background: #f9fafb;
    }

    .checkout-radio-option strong {
      font-weight: 600;
      color: #111827;
    }

    .order-summary-title {
      font-size: 1.05rem;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .summary-items {
      list-style: none;
      padding: 0;
      margin: 0 0 14px;
      max-height: 260px;
      overflow-y: auto;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
      padding: 6px 0;
      border-bottom: 1px solid #e5e7eb;
      font-size: 0.9rem;
    }

    .summary-item:last-child {
      border-bottom: none;
    }

    .summary-item-name {
      font-weight: 500;
      color: #111827;
    }

    .summary-item-meta {
      font-size: 0.82rem;
      color: #6b7280;
    }

    .summary-item-price {
      white-space: nowrap;
      font-weight: 500;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin: 4px 0;
      font-size: 0.9rem;
    }

    .summary-row.total {
      margin-top: 8px;
      font-weight: 600;
      font-size: 0.96rem;
      border-top: 1px solid #e5e7eb;
      padding-top: 6px;
    }

    .place-order-btn {
      width: 100%;
      border: none;
      border-radius: 6px;
      padding: 10px 14px;
      margin-top: 14px;
      font-size: 0.95rem;
      font-weight: 600;
      background: #2563eb;
      color: #fff;
      cursor: pointer;
    }

    .place-order-btn:hover {
      background: #1d4ed8;
    }

    .checkout-note {
      font-size: 0.8rem;
      color: #6b7280;
      margin-top: 4px;
    }
  </style>
</head>

<body>
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
        <?php } else { ?>
          <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" style="width:24px; height:24px;" onclick="window.location.href='viewProfile.php'">
        <?php } ?>
      </button>
      <button>
        <img src="cart-icon.webp" alt="Cart" style=" height:24px;" onclick="window.location.href='cart.php'">
      </button>
    </div>
  </header>

  <div class="checkout-wrapper">
    <h2 style="margin: 8px 0 18px; font-size:1.25rem;">Checkout</h2>

    <form class="checkout-layout" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
      <div class="checkout-card">
        <div class="checkout-section">
          <div class="checkout-section-title">Contact information</div>
          <div class="checkout-grid-2">
            <div class="checkout-field">
              <label for="fullName">Full name</label>
              <input type="text" id="fullName" name="fullName" required
                value="<?php echo $user ? htmlspecialchars($user['name']) : ''; ?>"
                placeholder="Your name">
            </div>
            <div class="checkout-field">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" required
                value="<?php echo $user ? htmlspecialchars($user['uEmail']) : ''; ?>"
                placeholder="you@example.com">
            </div>
          </div>

          <div class="checkout-grid-2">
            <div class="checkout-field">
              <label for="phone">Phone number</label>
              <input type="text" id="phone" name="phone"
                value="<?php echo $user ? htmlspecialchars($user['contact']) : ''; ?>"
                placeholder="07X XXXXXXX">
            </div>
            <div class="checkout-field">
              <label for="deliveryType">Delivery type</label>
              <select id="deliveryType" name="deliveryType">
                <option value="standard" selected>Standard delivery (2–5 days)</option>
                <option value="express">Express delivery (1–2 days)</option>
                <option value="pickup">Store pickup (where available)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="checkout-section" style="margin-top:14px;">
          <div class="checkout-section-title">Shipping address</div>
          <div class="checkout-field">
            <label for="addressLine">Address</label>
            <textarea id="addressLine" name="addressLine" required
              placeholder="Street, city, district">
<?php echo $user ? htmlspecialchars($user['address']) : ''; ?></textarea>
          </div>
          <div class="checkout-grid-2">
            <div class="checkout-field">
              <label for="city">City / Town</label>
              <input type="text" id="city" name="city" placeholder="City / Town">
            </div>
            <div class="checkout-field">
              <label for="postalCode">Postal code</label>
              <input type="text" id="postalCode" name="postalCode" placeholder="Postal code">
            </div>
          </div>
        </div>

        <div class="checkout-section" style="margin-top:14px;">
          <div class="checkout-section-title">Payment method</div>
          <div class="checkout-radio-group">
            <label class="checkout-radio-option">
              <input type="radio" name="paymentMethod" value="cod" checked>
              <div>
                <div><strong>Cash on Delivery</strong></div>
                <div class="checkout-note">Pay in cash when your order is delivered.</div>
              </div>
            </label>

            <label class="checkout-radio-option">
              <input type="radio" name="paymentMethod" value="bank">
              <div>
                <div><strong>Bank Transfer</strong></div>
                <div class="checkout-note">You will receive our bank details after placing the order.</div>
              </div>
            </label>

            <label class="checkout-radio-option">
              <input type="radio" name="paymentMethod" value="card" disabled>
              <div>
                <div><strong>Credit / Debit Card</strong> (Coming soon)</div>
                <div class="checkout-note">Secure online card payments will be available later.</div>
              </div>
            </label>
          </div>
        </div>
      </div>

      <aside class="checkout-card">
        <div class="order-summary-title">Order summary</div>
        <ul class="summary-items">
          <!-- TODO: Replace this sample item list with dynamic cart items from database -->
          
            <div>
              <div class="summary-item-name">Your cart items</div>
              <?php

            //   uEmail Primary	varchar(100)
	//	productID Primary	int			
	//	qty
    $uEmail = $_SESSION["userName"];
              $sql = "SELECT p.title, p.newPrice, c.qty FROM cart c JOIN products p ON c.productID = p.productID WHERE c.uEmail = '" . mysqli_real_escape_string($con, $uEmail) . "'";
              
              $result = mysqli_query($con, $sql);
              while($row = mysqli_fetch_assoc($result)){
                echo "<div class='summary-item'>";
                echo "<div class='summary-item-name'>" . $row['title'] . "</div>";
                echo "<div class='summary-item-price'>Rs." . $row['newPrice'] . "</div>";
                echo "<div class='summary-item-meta'>Qty: " . $row['qty'] . "</div>";
                echo "<div class='summary-item-meta'>Subtotal: Rs." . $row['newPrice'] * $row['qty'] . "</div>";
                echo "</div>";
              }
              ?>
              

            </div>
            
          
        </ul>

        <div class="summary-row">
          <span>Subtotal</span>
          <span>Rs.<?php
          $uEmail = $_SESSION["userName"];
          $sumRes = mysqli_query($con, "
            SELECT SUM(p.newPrice * c.qty) AS total
            FROM cart c
            JOIN products p ON c.productID = p.productID
            WHERE c.uEmail = '" . mysqli_real_escape_string($con, $uEmail) . "'
          ");
          $totalRow = mysqli_fetch_assoc($sumRes);
          $total = $totalRow['total'] ?? 0;
          
          
          echo $total; ?></span>
        </div>
        <div class="summary-row">
          <span>Delivery</span>
          <span>Calculated at dispatch</span>
        </div>
        <div class="summary-row total">
          <span>Total</span>
          <span>Rs.<?php echo $total; ?></span>
        </div>

        <input type="hidden" name="userEmail" value="<?php echo $user ? htmlspecialchars($user['uEmail']) : ''; ?>">
        <button type="submit" name="placeOrder" class="place-order-btn">Place order</button>

        <div class="checkout-note">By placing this order, you agree to our Terms &amp; Conditions.</div>
      </aside>
   </form>
  </div>

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


