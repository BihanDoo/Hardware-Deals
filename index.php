<!DOCTYPE html>
<?php session_start();
if(isset($_SESSION["userName"]))
{
    // header('Location: login.html');

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
      <h1 class="title" onclick="window.location.href='index.html'">Hardware Deals.lk</h1>
    </div>

    <div class="search-box">
      <input type="text" placeholder="Search products...">
      <button type="submit">🔍</button>
    </div>

    <div class="cart">
      <button style=" align-items: center; justify-content: center; width: 24px; height: 24px; padding: 0; margin: font-size: 24px; color: black; font-weight: 300; line-height: 1; text-align: center; border: none;" onclick="window.location.href='addproduct.php'">+</button>
      <button>
        <img src="login.png" alt="Login" style="width:24px; height:24px;" onclick="window.location.href='login.html'">
      </button>
      <button>
        <img src="cart-icon.webp" alt="Cart" style=" height:24px;" onclick="window.location.href='cart.html'">
      </button>

    </div>
  </header>



<div class="item-catalog">

<?php

        $con = mysqli_connect("localhost" , "root" , "" , "hardwaredeals");

        if(!$con)
        {
            die("Cannot connect to DB Server");
        }

        $sql = "SELECT products.*, stores.storeName FROM `products` INNER JOIN `stores` ON products.soldByStoreID = stores.storeID";

        $result = mysqli_query($con,$sql);

        if(mysqli_num_rows($result) > 0)
        {
            while($row = mysqli_fetch_assoc($result))
            {
        ?>




<div class="card" onclick="window.location.href='viewproduct.html'">
      <img src="<?php echo $row["imgURL"] ; ?>" alt="Card Image" class="cardimg" >
      <div class="card-content">
        <span class="badge"><?php echo $row["offTagDescription"] ; ?></span>
        <div class="pricetag">
          <span class="oldprice">Rs.<?php echo $row["oldPrice"] ; ?></span>
          <span class="newprice">Rs.<?php echo $row["newPrice"] ; ?></span>
        </div>
        <h3 class="nametitle"><?php echo $row["title"] ; ?></h3>
        <p class="descriptiontext"><?php echo $row["description"] ; ?></p>
        <div class="sellerinfo">
          <span class="soldbytext">Sold by:</span>
          <span class="sellername"><?php echo $row["storeName"] ; ?></span>
          <span class="ratings"><?php if($row["rating"]>0) echo str_repeat("⭐", $row["rating"]) ;echo "☆".str_repeat("☆", 4-$row["rating"]) ; ?>
        </span>
          <span class="buyercount">(<?php echo $row["reviewCount"] ; ?>)</span>
        </div>
        
        <div class="innercardscontainer">

        
          <div class="inner1card">
          <span class="favorite"><?php if($row["pickup"]) echo "Pickup" ; else echo "Delivery" ; ?></span>
            </div>
        <div class="inner1card">
          <span class="favorite"><?php if($row["inStock"]) echo "In Stock" ; else echo "Out of Stock" ; ?></span>

        </div>
        
        <br><span class="deliveryavailable"><?php if($row["deliveryAvailable"]) echo "Delivery available." ; else echo "Call for information." ; ?></span>
        <button class="addtocartbutton"><?php if($row["callToAction"]=="number") echo $row["callToAction"] ; else echo "Add to Cart" ; ?></button>
        
      </div>
      </div>
   </div>

<?php
    }
}else{
  echo "<p>No products found</p>";
}
?>




</div> 


<!-- foooooooooooter -->


     
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




