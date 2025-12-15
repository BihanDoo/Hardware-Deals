<!DOCTYPE html>
<html lang="en">


<?php
session_start();
if (!isset($_SESSION["userName"])) {
    header("Location: login.html");
    exit;
}

$uEmail = $_SESSION["userName"];

$con = mysqli_connect("localhost", "root", "", "hardwaredeals");
if (!$con) {
    die("Cannot connect to DB Server");
}


?>




<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <header class="title-header-thing">
        <div class="logo-title">
            <img src="logo.png" alt="Logo" class="logo">
            <h1 class="title" onclick="window.location.href='index.html'">Hardware Deals.lk</h1>
        </div>

        <div class="search-box">
            <input type="text" placeholder="Search products...">
            <button type="submit">🔍</button>
        </div>

        <div class="cart">
            <button>
                <img src="login.png" alt="Login" style="width:24px; height:24px;" onclick="window.location.href='login.html'">
            </button>


        </div>
    </header>


    <h2 style="text-align:center; margin-top:30px;">Your Shopping Cart</h2>


    <!-- enable horizontal scrolling -->
    <table class="cart-table" style="overflow-x: auto; overflow-x: auto; white-space: nowrap; ">
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
        <tbody style="box-sizing: border-box; ">



            <?php

            $sql = "SELECT c.qty, p.productID, p.imgURL, p.title, p.newPrice FROM cart c
        JOIN products p ON c.productID = p.productID
        WHERE c.uEmail = '" . mysqli_real_escape_string($con, $uEmail) . "'";

            $result = mysqli_query($con, $sql);

            // compute initial total server-side
            $total = 0;
            $cartRows = [];
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { ?>



                    <tr>
                        <td><img src="product-imgs/cordlessdrill.webp" alt="Cordless Drill"></td>
                        <td>Cordless Drill</td>
                        <td>Rs.7200.00</td>
                        <td><input type="number" value="1" min="1" style="width:50px;" onchange="updateTotal()"></td>
                        <td>Rs.7200.00</td>
                        <td><button class="remove-btn" onclick="removeitem()">Remove</button></td>
                    </tr>
            <?php
                }
            }
            ?>



        </tbody>
    </table>

    <script>
        function removeitem() {
            const button = event.target;
            const row = button.closest('tr');
            row.remove();
            updateTotal();
        }



        function updateTotal() {
            let total = 0;
            const rows = document.querySelectorAll('.cart-table tbody tr');
            rows.forEach(row => {
                const price = parseFloat(row.cells[2].innerText.replace('Rs.', ''));
                const quantity = parseInt(row.cells[3].querySelector('input').value);
                const subtotal = price * quantity;
                row.cells[4].innerText = 'Rs.' + subtotal.toFixed(2);
                total += subtotal;
            });
            document.querySelector('.total-value').innerText = 'Rs.' + total.toFixed(2);
        }
    </script>

    <div class="cart-summary">
        <span class="total-label">Total:</span>
        <span class="total-value">Rs.9850.00</span>
        <button class="checkout-btn">Proceed to Checkout</button>
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