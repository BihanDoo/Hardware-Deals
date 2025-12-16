<?php
session_start();
if (!isset($_SESSION["userName"])) {
    header("Location: login.html");
    exit;
} else {
    $username = $_SESSION["userName"];
    //profile pic in header
    $con = mysqli_connect("localhost", "root", "", "hardwaredeals");
    if (!$con) {
        die("Cannot connect to DB Server");
    }
    // fetch current user
    $stmt = mysqli_prepare($con, "SELECT uEmail, `name`, address, contact, profilePic FROM users WHERE uEmail = ? OR `name` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }
}
$profilePic = (!empty($user) && !empty($user['profilePic'])) ? $user['profilePic'] : 'profile-placeholder.png';
$uEmail = $_SESSION["userName"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'removeItem' && isset($_POST['productID'])) {
        $productIDToRemove = $_POST['productID'];
        // Delete the item from cart
        $deleteSql = "DELETE FROM cart WHERE uEmail = '" . mysqli_real_escape_string($con, $uEmail) . "' AND productID = '" . mysqli_real_escape_string($con, $productIDToRemove) . "'";
        mysqli_query($con, $deleteSql);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'updateQuantity' && isset($_POST['productID']) && isset($_POST['qty-input'])) {
        $productIDToUpdate = $_POST['productID'];
        $newQty = max(1, intval($_POST['qty-input'])); // ensure at least 1
        // Update the quantity in cart
        $updateSql = "UPDATE cart SET qty = " . intval($newQty) . " WHERE uEmail = '" . mysqli_real_escape_string($con, $uEmail) . "' AND productID = '" . mysqli_real_escape_string($con, $productIDToUpdate) . "'";
        mysqli_query($con, $updateSql);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}











// Load cart rows joined with products for this user
$sql = "SELECT c.qty, p.productID, p.imgURL, p.title, p.newPrice
        FROM cart c
        JOIN products p ON c.productID = p.productID
        WHERE c.uEmail = '" . mysqli_real_escape_string($con, $uEmail) . "'";

$result = mysqli_query($con, $sql);

// compute initial total server-side
$total = 0;
$cartRows = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['subtotal'] = floatval($row['newPrice']) * intval($row['qty']);
        $total += $row['subtotal'];
        $cartRows[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        /* .cart-summary { text-align:right; margin:20px; } */
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
            <button>
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" style="width:24px; height:24px;" onclick="window.location.href='viewProfile.php'">
            </button>
        </div>
    </header>

    <h2 style="text-align:center; margin-top:30px;">Your Shopping Cart</h2>

    <div style="overflow-x:auto;">
        <table class="cart-table" style="white-space: nowrap; width:100%;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cartRows)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#666; padding:20px;">Your cart is empty.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cartRows as $r): ?>
                        <tr data-productid="<?php echo htmlspecialchars($r['productID']); ?>">
                            <td><img src="<?php echo htmlspecialchars($r['imgURL']); ?>" alt="<?php echo htmlspecialchars($r['title']); ?>"></td>
                            <td><?php echo htmlspecialchars($r['title']); ?></td>
                            <td class="unit-price">Rs.<?php echo number_format((float)$r['newPrice'], 2); ?></td>
                            <td>
                                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="qty-form">
                                    <input type="hidden" name="action" value="updateQuantity">
                                    <input type="hidden" name="productID" class="product-id" value="<?php echo htmlspecialchars($r['productID']); ?>">
                                    <input type="number" name="qty-input" class="qty-input" value="<?php echo intval($r['qty']); ?>" min="1" onchange="this.form.submit()" style="width:60px;">
                                </form>
                            </td>
                            <td class="row-subtotal">Rs.<?php echo number_format($r['subtotal'], 2); ?></td>
                            <td>
                                <!-- remove action -->
                                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                                    <input type="hidden" name="action" value="removeItem">
                                    <input type="hidden" name="productID" value="<?php echo htmlspecialchars($r['productID']); ?>">
                                    <button type="submit" class="remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="cart-summary">
        <span class="total-label" style="font-weight:600; margin-right:8px;">Total:</span>
        <span class="total-value" style="font-weight:600;">Rs.<?php echo number_format($total, 2); ?></span>
        <button class="checkout-btn" style="margin-left:16px;" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
    </div>



    <script>
        // Update client-side subtotal and total when qty changes
        function updateTotalsClient() {
            let total = 0;
            const rows = document.querySelectorAll('.cart-table tbody tr[data-productid]');
            rows.forEach(row => {
                const priceText = row.querySelector('.unit-price').innerText.replace('Rs.', '').replace(/,/g, '');
                const price = parseFloat(priceText) || 0;
                const qty = parseInt(row.querySelector('.qty-input').value) || 1;
                const subtotal = price * qty;
                row.querySelector('.row-subtotal').innerText = 'Rs.' + subtotal.toFixed(2);
                total += subtotal;
            });
            document.querySelector('.total-value').innerText = 'Rs.' + total.toFixed(2);
        }

        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('qty-input')) {
                updateTotalsClient();
                // Optionally, send an AJAX request to update server-side qty here (not implemented)
                // populateLater: add backend qty update
            }
        });
    </script>

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