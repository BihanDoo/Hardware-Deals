<?php session_start();

$q = trim($_GET["q"] ?? "");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!isset($_SESSION["userName"])) {
    header("Location: login.html");
    exit;
  }
  $productID = $_POST['productID'];
  $uEmail = $_SESSION["userName"];
  //uEmail is same as userName in session
  $con = mysqli_connect("localhost", "root", "", "hardwaredeals");
  if (!$con) {
    die("Cannot connect to DB Server");
  }
  $sql = "INSERT INTO `cart` (`uEmail`, `productID`, `qty`) VALUES ('$uEmail', '$productID', 1) ON DUPLICATE KEY UPDATE `qty` = `qty` + 1";
  $result = mysqli_query($con, $sql);
}





if (!isset($_SESSION["userName"])) {
  //header('Location: login.html');

} else {
  $username = $_SESSION["userName"];
  //echo $username;
  $con = mysqli_connect("localhost", "root", "", "hardwaredeals");
  if (!$con) {
    die("Cannot connect to DB Server");
  }

  // fetch user by email OR name
  $stmt = mysqli_prepare($con, "SELECT uEmail, `name`, address, contact, profilePic, isSeller, ordersCompleted FROM users WHERE uEmail = ? OR `name` = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "ss", $username, $username);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  $user = null;
  if ($res && mysqli_num_rows($res) > 0) {
    $user = mysqli_fetch_assoc($res);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hardware Deals</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <header class="title-header-thing">
    <div class="logo-title">
      <img src="logo.png" alt="Logo" class="logo">
      <h1 class="title" onclick="window.location.href='index.php'">Hardware Deals.lk</h1>

    </div>

    <form class="search-box" method="get" action="index.php">
  <input type="text" name="q" placeholder="Search products..."
         value="<?php echo htmlspecialchars($q); ?>">
  <button type="submit">🔍</button>
</form>

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



  <div class="item-catalog">

    <?php

    $con = mysqli_connect("localhost", "root", "", "hardwaredeals");

    if (!$con) {
      die("Cannot connect to DB Server");
    }

    $sql = "SELECT products.*, stores.storeName
        FROM `products`
        INNER JOIN `stores` ON products.soldByStoreID = stores.storeID";

if ($q !== "") {
  $sql .= " WHERE products.title LIKE ?";
  $stmt = mysqli_prepare($con, $sql);
  $like = "%" . $q . "%";
  mysqli_stmt_bind_param($stmt, "s", $like);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
} else {
  $result = mysqli_query($con, $sql);
}


    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
    ?>




        <div class="card" onclick="window.location.href='viewproduct.php?id=<?php echo $row["productID"]; ?>'">
          <img src="<?php echo $row["imgURL"]; ?>" alt="Card Image" class="cardimg">
          <div class="card-content">
            <span class="badge"><?php echo $row["offTagDescription"]; ?></span>
            <div class="pricetag">
              <?php
              if (isset($row["oldPrice"])) {





              ?>
                <span class="oldprice">Rs.<?php echo $row["oldPrice"]; ?></span>


              <?php
              }
              ?>
              <?php 
              if($row["newPrice"] == 0.00){
                ?>
                <span class="newprice">Call for price</span>
                <?php
              }else{
                ?>
                <span class="newprice">Rs.<?php echo $row["newPrice"]; ?></span>
                <?php
              }
              ?>
            </div>
            <h3 class="nametitle"><?php echo $row["title"]; ?></h3>
            <p class="descriptiontext"><?php echo $row["description"]; ?></p>
            <div class="sellerinfo">
              <span class="soldbytext">Sold by:</span>
              <span class="sellername"><?php echo $row["storeName"]; ?></span>
              <span class="ratings"><?php if ($row["rating"] > 0) echo str_repeat("⭐", $row["rating"]);
                                    echo "☆" . str_repeat("☆", 4 - $row["rating"]); ?>
              </span>
              <span class="buyercount">(<?php echo $row["reviewCount"]; ?>)</span>
            </div>

            <div class="innercardscontainer">


              <div class="inner1card">
                <span class="favorite"><?php if ($row["pickup"]) echo "Pickup";
                                        else echo "Delivery"; ?></span>
              </div>
              <div class="inner1card">
                <span class="favorite"><?php if ($row["inStock"]) echo "In Stock";
                                        else echo "Out of Stock"; ?></span>

              </div>

              <?php if ($row["deliveryAvailable"]) { ?> <br><span class="deliveryavailable"><?php echo "Delivery available."; ?></span>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                  <input type="hidden" name="productID" value="<?php echo $row['productID']; ?>">
                  <button class="addtocartbutton"><?php echo "Add to Cart"; ?></button>
                </form>

              <?php } else { ?>
                <br><span class="deliveryavailable"><?php echo "Call for information."; ?></span>
                <button class="addtocartbutton"><?php echo $row["callToAction"]; ?></button>
              <?php } ?>
            </div>
          </div>
        </div>

    <?php
      }
    } else {
      echo "<p>No products found</p>";
    }
    ?>




  </div>


  <!-- foooooooooooter -->



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


  <?php

  ?>





</body>

</html>