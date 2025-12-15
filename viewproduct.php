<?php
session_start();
if (!isset($_SESSION["userName"])) {
    //   header("Location: login.html");
    //   exit;
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
    $res = mysqli_stmt_get_result($stmt);

    $user = null;
    if ($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
    }
}



$productID = $_GET['id'];
// echo $productID;
$con = mysqli_connect("localhost", "root", "", "hardwaredeals");
if (!$con) {
    die("Cannot connect to DB Server");
}
// fetch product details
$sql = "SELECT * FROM products WHERE productID = '" . mysqli_real_escape_string($con, $productID) . "' LIMIT 1";
$result = mysqli_query($con, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION["userName"])) {
        header("Location: login.html");
        exit;
    }
    $productID = $_POST['productID'];
    $uEmail = $_SESSION["userName"];
    //uEmail is same as userName in session

    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) {
        $quantity = 1;
    }

    $sql = "INSERT INTO `cart` (`uEmail`, `productID`, `qty`) VALUES ('$uEmail', '$productID', $quantity) ON DUPLICATE KEY UPDATE `qty` = `qty` + $quantity";
    $result = mysqli_query($con, $sql);
    header("Location: viewproduct.php?id=" . urlencode($productID));
    exit;
}

$sql = "SELECT * FROM products WHERE productID = '" . mysqli_real_escape_string($con, $productID) . "' LIMIT 1";
$result = mysqli_query($con, $sql);
$product = null;
if ($result && mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
} else {



    header("Location: notfound.html");
    exit;
}

$imageUrls = [];
if ($product) {
    $imageUrls[] = $product['imgURL']; // main image first
    $sqlImgs = "SELECT imgURL FROM productimgs WHERE productID = '" . mysqli_real_escape_string($con, $productID) . "' ORDER BY imgID";
    $resImgs = mysqli_query($con, $sqlImgs);
    if ($resImgs && mysqli_num_rows($resImgs) > 0) {
        while ($imgRow = mysqli_fetch_assoc($resImgs)) {
            // avoid duplicates if same as main
            if ($imgRow['imgURL'] !== $product['imgURL']) {
                $imageUrls[] = $imgRow['imgURL'];
            }
        }
    }
}


?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - Hardware Deals.lk</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="viewproduct.css">

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
                <img src="login.png" alt="Login" style="width:24px; height:24px;" onclick="window.location.href='login.html'">
            </button>
            <button>
                <img src="cart-icon.webp" alt="Cart" style=" height:24px;" onclick="window.location.href='cart.php'">
            </button>

        </div>
    </header>







    <div class="vprcontainer">
        <div class="innercontainer">



            <div class="vprimg">
                <div class="slider-container">
                    <button class="slider-btn prev" onclick="changeSlide(-1)">❮</button>
                    <div class="main-image">
                        <img id="mainProductImage" src="<?php echo htmlspecialchars($product['imgURL']); ?>" alt="Product Image">
                    </div>
                    <button class="slider-btn next" onclick="changeSlide(1)">❯</button>
                </div>

                <div class="thumbnail-gallery">
                    <?php if (!empty($imageUrls)): ?>
                        <?php foreach ($imageUrls as $i => $url): ?>
                            <img src="<?php echo htmlspecialchars($url); ?>"
                                class="<?php echo ($i === 0) ? 'active-thumb' : ''; ?>"
                                onclick="setSlide(<?php echo $i; ?>)">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
            <!-- <img src="drilldemo/drill1.jpg" class="active-thumb" onclick="setSlide(0)">
    <img src="drilldemo/2.jpg" onclick="setSlide(1)">
    <img src="drilldemo/3.jpg" onclick="setSlide(2)"> -->



            <div class="vprdetails">
                <div>
                    <span class="vprbadge"><?php echo htmlspecialchars($product['offTagDescription']); ?></span>
                    <span class="vproldprice">Rs.<?php echo number_format((float)$product['oldPrice'], 2); ?></span>
                    <span class="vprprice">Rs.<?php echo number_format((float)$product['newPrice'], 2); ?></span>

                </div>
                <div class="vprtitle"><?php echo htmlspecialchars($product['title']); ?></div>
                <div class="vprdescription"><?php echo htmlspecialchars($product['description']); ?></div>
                <div class="vprseller">Sold by: <span class="sellername"><?php


                                                                            $soldbystoreID = $product['soldByStoreID'];
                                                                            $sql = "SELECT storeName FROM stores WHERE storeID = '" . mysqli_real_escape_string($con, $soldbystoreID) . "' LIMIT 1";
                                                                            $result = mysqli_query($con, $sql);
                                                                            if ($result && mysqli_num_rows($result) > 0) {
                                                                                $row = mysqli_fetch_assoc($result);
                                                                                echo htmlspecialchars($row['storeName']);
                                                                            }
                                                                            ?></span> <span class="ratings"><?php echo str_repeat('⭐', $product['rating']); ?><?php echo str_repeat('☆', 5 - $product['rating']); ?></span> <span class="buyercount">(<?php echo htmlspecialchars($product['buyerCount']); ?>)</span></div>

                <div class="vprmeta"><?php
                                        if ($product['forRent']) {
                                            echo "For Rent &bull; ";
                                        } else {
                                            if ($product['inStock']) {
                                                echo "In Stock &bull; ";
                                            }
                                            if ($product['wholesale']) {
                                                echo "Wholesale &bull; ";
                                            } else {
                                                echo "Retail &bull; ";
                                            }
                                        }
                                        if ($product['pickup']) {
                                            echo "Pickup &bull; ";
                                        }
                                        if ($product['deliveryAvailable']) {
                                            echo "Delivery available &bull; ";
                                        }


                                        ?>




                    <form class="vpractions" method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . urlencode($productID); ?>">
                        <label for="quantity">Qty:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1">
                        <input type="hidden" name="productID" value="<?php echo htmlspecialchars($productID); ?>">
                        <button type="submit" class="addtocartbutton">Add to Cart</button>
                    </form>
                </div>

            </div>

            <div>

                <section class="reviews">
                    <h2>Customer Reviews (3)</h2>

                    <div class="review-list">

                        <div class="review">
                            <strong>Nuwan Perera</strong> <span style="color:#ffcc00;">★★★★★</span>
                            <div>Meka maru</div>

                            <div class="review-images">
                                <img src="drilldemo/rev1.jpg" alt="Customer image" onclick="openReviewImage(this)">
                                <img src="drilldemo/rev2.jpg" alt="Customer image" onclick="openReviewImage(this)">
                                <img src="drilldemo/rev3.jpg" alt="Customer image" onclick="openReviewImage(this)">
                            </div>

                        </div>



                        <div class="review">
                            <strong>Sajini Fernando</strong> <span style="color:#ffcc00;">★★★★★</span>
                            <div>This is a time saver!!! totally worth it</div>

                            <div class="review-images">
                                <img src="drilldemo/rev4.jpg" alt="Customer image" onclick="openReviewImage(this)">
                                <img src="drilldemo/rev5.jpg" alt="Customer image" onclick="openReviewImage(this)">

                            </div>

                        </div>


                        <div class="review">
                            <strong>Ruwan Jayasuriya</strong> <span style="color:#ffcc00;">★★★★★</span>
                            <div>delivered in time, seller was responsive. </div>
                        </div>

                        <div class="review">
                            <strong>sameera max</strong> <span style="color:#ffcc00;">☆☆☆☆☆</span>
                            <div>Thanks </div>
                        </div>

                    </div>
                    <style>

                    </style>

                </section>


            </div>

        </div>


        <footer class="footer" style="margin-top:40px;">
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


        <script>
            function changeImage(img) {
                document.getElementById('mainProductImage').src = img.src;
            }


            const images = <?php echo json_encode($imageUrls, JSON_UNESCAPED_SLASHES); ?>;


            let currentSlide = 0;
            document.addEventListener("DOMContentLoaded", () => {
                if (images.length > 0) showSlide(0);
            });

            function showSlide(index) {
                const mainImage = document.getElementById("mainProductImage");
                const thumbs = document.querySelectorAll(".thumbnail-gallery img");

                if (index >= images.length) currentSlide = 0;
                else if (index < 0) currentSlide = images.length - 1;
                else currentSlide = index;



                mainImage.classList.add("fade-out");
                setTimeout(() => {
                    mainImage.src = images[currentSlide];
                    mainImage.classList.remove("fade-out");
                }, 200);

                thumbs.forEach((img, i) => img.classList.toggle("active-thumb", i === currentSlide));
            }

            function changeSlide(direction) {
                showSlide(currentSlide + direction);
            }

            function setSlide(index) {
                showSlide(index);
            }

            function openReviewImage(img) {
                const modal = document.getElementById("imageModal");
                const modalImg = document.getElementById("modalImg");
                modal.style.display = "flex";
                modalImg.src = img.src;
            }

            function closeReviewImage() {
                document.getElementById("imageModal").style.display = "none";
            }
        </script>


        <div id="imageModal" class="image-modal" onclick="closeReviewImage()">
            <span class="close-btn">&times;</span>
            <img class="modal-content" id="modalImg">
        </div>

</body>

</html>