<?php
ob_start();
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

// Get store info safely
$storename = "not found";
$storenamenotfound = 1;
$storeid = null;

$storeStmt = mysqli_prepare($con, "SELECT storeID, storeName FROM stores WHERE storeContactUEmail = ? LIMIT 1");
mysqli_stmt_bind_param($storeStmt, "s", $username);
mysqli_stmt_execute($storeStmt);
$storeRes = mysqli_stmt_get_result($storeStmt);
if ($storeRes && mysqli_num_rows($storeRes) > 0) {
  $storeRow = mysqli_fetch_assoc($storeRes);
  $storeid = (int)$storeRow["storeID"];
  $storename = $storeRow["storeName"];
  $storenamenotfound = 0;
}

// Load user for profilePic etc.
$user = null;
$userStmt = mysqli_prepare($con, "SELECT uEmail, `name`, address, contact, profilePic, isSeller, ordersCompleted FROM users WHERE uEmail = ? OR `name` = ? LIMIT 1");
mysqli_stmt_bind_param($userStmt, "ss", $username, $username);
mysqli_stmt_execute($userStmt);
$userRes = mysqli_stmt_get_result($userStmt);
if ($userRes && mysqli_num_rows($userRes) > 0) {
  $user = mysqli_fetch_assoc($userRes);
}

// Editing mode
$productID = null;
$editing = false;
$existingProduct = null;
$existingImages = array();




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSubmit'])) {
  
  if ($storenamenotfound == 1 || $storeid === null) {
    echo "<div class='message error'>You don't have a store yet.</div>";
    exit;
  }

  $title = trim($_POST["prodctitle"] ?? "");
  $description = trim($_POST["prodcdescription"] ?? "");
  $Price = $_POST["Price"] ?? null;
  $category = $_POST["category"] ?? null;
  $searchtags = trim($_POST["searchtags"] ?? "");
  $offTagDescription = trim($_POST["offTagDescription"] ?? "");

  $instock = isset($_POST["inStock"]) ? 1 : 0;
  $deliveryAvailable = isset($_POST["deliveryAvailable"]) ? 1 : 0;
  $pickup = isset($_POST["pickup"]) ? 1 : 0;

  $forRent = isset($_POST["forRent"]) ? 1 : 0;
  $wholesale = ($forRent === 1) ? 0 : (isset($_POST["wholesale"]) ? 1 : 0);

  // callToAction handling
  $callToAction = null;
  if (isset($_POST["callforprice"]) || !empty($_POST["callToAction"])) {
    $callToAction = trim($_POST["callToAction"] ?? "");
  }

  // Main image upload handling
  $mainimage = $existingProduct['imgURL'] ?? null;

  if (isset($_FILES["mainimg"]) && isset($_FILES["mainimg"]["error"]) && $_FILES["mainimg"]["error"] === UPLOAD_ERR_OK) {
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES["mainimg"]["name"], PATHINFO_EXTENSION));
    $allowedExt = ["jpg", "jpeg", "png", "webp"];
    if (!in_array($ext, $allowedExt)) {
      echo "<div class='message error'>Main image must be JPG/PNG/WEBP.</div>";
      exit;
    }

    $mainimage = $uploadDir . time() . "_main_" . uniqid() . "." . $ext;
    move_uploaded_file($_FILES["mainimg"]["tmp_name"], $mainimage);
  } else {
    // If adding new product, main image is required
    if (!$editing) {
      echo "<div class='message error'>Please upload a main image.</div>";
      exit;
    }
  }

  // Multiple images upload (saved to productimgs table)
  $uploadedImages = array();
  if (
    isset($_FILES["imgs"], $_FILES["imgs"]["name"]) &&
    is_array($_FILES["imgs"]["name"]) &&
    count($_FILES["imgs"]["name"]) > 0
  ) {
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $allowedExtMulti = ["jpg", "jpeg", "png", "webp"];
    $fileCount = count($_FILES["imgs"]["name"]);
    for ($i = 0; $i < $fileCount; $i++) {
      if (
        isset($_FILES["imgs"]["error"][$i]) &&
        $_FILES["imgs"]["error"][$i] === UPLOAD_ERR_OK &&
        !empty($_FILES["imgs"]["name"][$i])
      ) {
        $ext = strtolower(pathinfo($_FILES["imgs"]["name"][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtMulti)) {
          // Skip invalid file types silently
          continue;
        }

        $uniqueName = $uploadDir . time() . "_img_" . $i . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES["imgs"]["tmp_name"][$i], $uniqueName)) {
          $uploadedImages[] = $uniqueName;
        }
      }
    }
  }

  // INSERT vs UPDATE
  if ($editing && isset($_POST["productID"]) && ctype_digit((string)$_POST["productID"])) {
    $pid = (int)$_POST["productID"];

    $upStmt = mysqli_prepare($con, "
  UPDATE products
  SET imgURL = ?, offTagDescription = ?, newPrice = ?, title = ?, description = ?,
      soldByStoreID = ?, deliveryAvailable = ?, callToAction = ?, pickup = ?,
      inStock = ?, forRent = ?, wholesale = ?, searchTags = ?, categoryID = ?
  WHERE productID = ? AND soldByStoreID = ?
");
    mysqli_stmt_bind_param(
      $upStmt,
      "ssdssiisiiiisiii",
      $mainimage,
      $offTagDescription,
      $Price,
      $title,
      $description,
      $storeid,
      $deliveryAvailable,
      $callToAction,
      $pickup,
      $instock,
      $forRent,
      $wholesale,
      $searchtags,
      $category,
      $pid,
      $storeid
    );

    if (mysqli_stmt_execute($upStmt)) {
      // Save new additional images if any were uploaded
      if (!empty($uploadedImages)) {
        $imgStmt = mysqli_prepare($con, "INSERT INTO productimgs (productID, imgURL) VALUES (?, ?)");
        if ($imgStmt) {
          foreach ($uploadedImages as $imgPath) {
            mysqli_stmt_bind_param($imgStmt, "is", $pid, $imgPath);
            mysqli_stmt_execute($imgStmt);
          }
          mysqli_stmt_close($imgStmt);
        }
      }

      header("Location: viewprofile.php");
      exit;
    } else {
      echo "<div class='message error'>Update failed: " . htmlspecialchars(mysqli_error($con)) . "</div>";
    }
  } else {
    $inStmt = mysqli_prepare($con, "
  INSERT INTO products
  (imgURL, offTagDescription, oldPrice, newPrice, title, description, soldByStoreID,
   reviewCount, rating, deliveryAvailable, callToAction, pickup, inStock, forRent,
   wholesale, searchTags, categoryID)
  VALUES (?, ?, NULL, ?, ?, ?, ?, NULL, NULL, ?, ?, ?, ?, ?, ?, ?, ?)
");

    mysqli_stmt_bind_param(
      $inStmt,
      "ssdssiisiiiisi",
      $mainimage,
      $offTagDescription,
      $Price,
      $title,
      $description,
      $storeid,
      $deliveryAvailable,
      $callToAction,
      $pickup,
      $instock,
      $forRent,
      $wholesale,
      $searchtags,
      $category
    );

    if (mysqli_stmt_execute($inStmt)) {
      $newProductId = mysqli_insert_id($con);

      if ($newProductId && !empty($uploadedImages)) {
        $imgStmt = mysqli_prepare($con, "INSERT INTO productimgs (productID, imgURL) VALUES (?, ?)");
        if ($imgStmt) {
          foreach ($uploadedImages as $imgPath) {
            mysqli_stmt_bind_param($imgStmt, "is", $newProductId, $imgPath);
            mysqli_stmt_execute($imgStmt);
          }
          mysqli_stmt_close($imgStmt);
        }
      }

      header("Location: viewprofile.php");
      exit;
    } else {
      echo "<div class='message error'>Insert failed: " . htmlspecialchars(mysqli_error($con)) . "</div>";
    }
  }






  if ($ok) {
      header("Location: viewprofile.php");
      exit;
  }

  $error = "Insert failed"; // store error instead of echoing immediately
}





if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
  $productID = (int)$_GET['id'];
  $editing = true;

  // IMPORTANT: also make sure this product belongs to this store
  $prodStmt = mysqli_prepare($con, "SELECT * FROM products WHERE productID = ? AND soldByStoreID = ? LIMIT 1");
  mysqli_stmt_bind_param($prodStmt, "ii", $productID, $storeid);
  mysqli_stmt_execute($prodStmt);
  $prodRes = mysqli_stmt_get_result($prodStmt);

  if ($prodRes && mysqli_num_rows($prodRes) > 0) {
    $existingProduct = mysqli_fetch_assoc($prodRes);

    // Load existing images (main + gallery) using same approach as viewproduct.php
    if (!empty($existingProduct['imgURL'])) {
      $existingImages[] = $existingProduct['imgURL']; // main first
    }
    $imgStmt = mysqli_prepare($con, "SELECT imgURL FROM productimgs WHERE productID = ? ORDER BY imgID");
    if ($imgStmt) {
      mysqli_stmt_bind_param($imgStmt, "i", $productID);
      mysqli_stmt_execute($imgStmt);
      $imgRes = mysqli_stmt_get_result($imgStmt);
      if ($imgRes && mysqli_num_rows($imgRes) > 0) {
        while ($imgRow = mysqli_fetch_assoc($imgRes)) {
          if ($imgRow['imgURL'] !== ($existingProduct['imgURL'] ?? '')) {
            $existingImages[] = $imgRow['imgURL'];
          }
        }
      }
      mysqli_stmt_close($imgStmt);
    }
  } else {
    $editing = false;
    $productID = null;
  }
}
?>
<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product - Hardware Deals.lk</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="loginstyle.css">
  <style>
    .addproduct-container {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
      padding: 36px 32px;
    }

    .addproduct-title {
      font-size: 1.5em;
      font-weight: bold;
      color: #ed6510;
      margin-bottom: 24px;
      text-align: center;
    }

    .addproduct-form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .addproduct-form label {
      font-size: 0.95em;
      color: #333;
      font-weight: 500;
      margin-bottom: 4px;
    }

    .addproduct-form input[type="text"],
    .addproduct-form input[type="number"],
    .addproduct-form input[type="url"],
    .addproduct-form textarea,
    .addproduct-form select {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1em;
      background: #f7f7f7;
      transition: border 0.2s;
      font-family: Arial, sans-serif;
    }

    .addproduct-form textarea {
      resize: vertical;
      min-height: 80px;
    }

    .addproduct-form input:focus,
    .addproduct-form textarea:focus,
    .addproduct-form select:focus {
      border: 1.5px solid #ed6510;
      outline: none;
      background: #fffbe9;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    @media (max-width: 600px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .message {
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 500;
    }

    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .addproduct-btn {
      background: #ed6510;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 12px 0;
      font-size: 1.1em;
      font-weight: bold;
      cursor: pointer;
      margin-top: 8px;
      transition: background 0.2s;
    }

    .addproduct-btn:hover {
      background: #c94e00;
    }

    .back-link {
      text-align: center;
      margin-top: 16px;
    }

    .back-link a {
      color: #ed6510;
      text-decoration: none;
      font-size: 0.95em;
    }

    .back-link a:hover {
      color: #c94e00;
      text-decoration: underline;
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
      <button style="align-items: center; justify-content: center; width: 24px; height: 24px; padding: 0; margin: 0; font-size: 24px; color: black; font-weight: 300; line-height: 1; text-align: center; border: none;" onclick="window.location.href='addproduct.php'">+</button>
      <button>
        <?php if (!isset($_SESSION["userName"])) { ?>

          <!-- <img src="login.png" alt="Login" style="width:24px; height:24px;" onclick="window.location.href='login.html'"> -->
          
        <?php
        } else {
        ?>
          <img src="<?php echo htmlspecialchars($user['profilePic'] ? $user['profilePic'] : 'profile-placeholder.png'); ?>" alt="Profile" style="width:24px; height:24px;" onclick="window.location.href='viewProfile.php'">
        <?php } ?>
      </button>
      <button>
        <img src="cart-icon.webp" alt="Cart" style="height:24px;" onclick="window.location.href='cart.php'">
      </button>
    </div>
  </header>

  <div class="addproduct-container">
    <div class="addproduct-title">
      <?php echo $editing ? 'Edit Product' : 'Add New Product'; ?>
    </div>

    <form class="addproduct-form" method="POST" action="addproduct.php" enctype="multipart/form-data" autocomplete="off">
      <?php if ($editing && $productID !== null) { ?>
        <input type="hidden" name="productID" value="<?php echo htmlspecialchars($productID); ?>">
      <?php } ?>
      <div>
        <label for="prodctitle">Product Title</label>
        <input type="text" id="prodctitle" name="prodctitle" placeholder="Title" required value="<?php echo $existingProduct ? htmlspecialchars($existingProduct['title']) : ''; ?>">
      </div>

      <div>
        <label for="description">Description</label>
        <textarea style=" display: flex; align-items: center; justify-content: center;" id="description" name="prodcdescription" placeholder="enter product description" required><?php echo $existingProduct ? htmlspecialchars($existingProduct['description']) : ''; ?></textarea>
      </div>

      <div>
        <label for="mainimg">Upload main image(preview):</label>
        <input type="file" id="mainimg" name="mainimg" accept="image/*" <?php echo $editing ? '' : 'required'; ?>>
        <?php if ($editing && $existingProduct && !empty($existingProduct['imgURL'])) { ?>
          <div style="margin-top:8px;">
            <span>Current image:</span><br>
            <img src="<?php echo htmlspecialchars($existingProduct['imgURL']); ?>" alt="Current image" style="max-width:120px; max-height:120px; object-fit:cover; border-radius:6px; border:1px solid #ccc;">
          </div>
        <?php } ?>
      </div>

      <div>
        <label for="imgs">Images (Select multiple)</label>
        <input type="file" id="imgs" name="imgs[]" accept="image/*" multiple onchange="previewImages(this)">
        <div id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
        <?php if ($editing && !empty($existingImages)) { ?>
          <div style="margin-top:10px;">
            <strong>Current images:</strong>
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:6px;">
              <?php foreach ($existingImages as $img): ?>
                <img src="<?php echo htmlspecialchars($img); ?>" alt="Existing image" style="width:80px; height:80px; object-fit:cover; border:1px solid #ccc; border-radius:6px;">
              <?php endforeach; ?>
            </div>
          </div>
        <?php } ?>
      </div>

      <div class="form-row">

        <div>
          <label for="Price">Price (Rs.)</label>
          <input type="number" id="Price" name="Price" step="0.01" min="0" value="<?php echo $existingProduct ? htmlspecialchars($existingProduct['newPrice']) : ''; ?>">

        </div>
        <div style="display: flex; align-items: center; justify-content: center;">
          <div class="checkbox-group">
            <input type="checkbox" name="callforprice" id="callforprice" onchange="disablethis('Price', this.checked); document.getElementById('callToAction').required = this.checked; document.getElementById('Price').value='0.00';">
            <label for="callforprice">Call for price</label>
          </div>

        </div>
      </div>

      <div>
        <label for="offTagDescription">Offer Tag (if any)</label>
        <input type="text" id="offTagDescription" name="offTagDescription" placeholder="20% OFF" value="<?php echo $existingProduct ? htmlspecialchars($existingProduct['offTagDescription']) : ''; ?>">
      </div>

      <?php

$categories = [];
$categoryQuery = "SELECT categoryID, categoryName FROM categories";
$categoryResult = mysqli_query($con, $categoryQuery);
if ($categoryResult) {
  while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row;
  }
}
?>



      <div>
        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="">-- Select Category --</option>
          <!-- select all categories, snippet from AI -->
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat['categoryID']); ?>" <?php
                                                                                if ($existingProduct && isset($existingProduct['categoryID']) && (string)$existingProduct['categoryID'] === (string)$cat['categoryID']) {
                                                                                  echo 'selected';
                                                                                }
                                                                                ?>><?php echo htmlspecialchars($cat['categoryName']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Store</label>
        <label id="storename"><?php echo $storename ?></label>

      </div>



      <div>
        <label for="callToAction">Contact number</label>
        <input type="text" id="callToAction" name="callToAction" value="<?php

                                                                        if ($existingProduct == null) {
                                                                          echo '';
                                                                        } else {
                                                                          if ($existingProduct['callToAction'] == null) {
                                                                            echo '';
                                                                          } else {
                                                                            echo htmlspecialchars($existingProduct['callToAction'] ?? '');
                                                                          }
                                                                        }
                                                                        ?>">
      </div>

      <div>
        <div class="checkbox-group">
          <input type="checkbox" id="pickup" name="pickup" <?php
                                                            if ($existingProduct) {
                                                              echo ((int)$existingProduct['pickup'] === 1) ? 'checked' : '';
                                                            } else {
                                                              echo 'checked';
                                                            }
                                                            ?>>
          <label for="pickup">Pickup Available</label>
          <input type="checkbox" id="deliveryAvailable" name="deliveryAvailable" <?php
                                                                                  if ($existingProduct) {
                                                                                    echo ((int)$existingProduct['deliveryAvailable'] === 1) ? 'checked' : '';
                                                                                  } else {
                                                                                    echo 'checked';
                                                                                  }
                                                                                  ?>>
          <label for="deliveryAvailable">Delivery Available</label>
        </div>

      </div>

      <div>
        <div class="checkbox-group">
          <input type="checkbox" id="inStock" name="inStock" <?php
                                                              if ($existingProduct) {
                                                                echo ((int)$existingProduct['inStock'] === 1) ? 'checked' : '';
                                                              } else {
                                                                echo 'checked';
                                                              }
                                                              ?>>
          <label for="inStock">In Stock</label>

          <input type="checkbox" id="forRent" name="forRent" onchange="disablethis('wholesale', this.checked)" <?php
                                                                                                                if ($existingProduct) {
                                                                                                                  echo ((int)$existingProduct['forRent'] === 1) ? 'checked' : '';
                                                                                                                }
                                                                                                                ?>>
          <label for="forRent">For Rent?</label>

          <input type="checkbox" id="wholesale" name="wholesale" <?php
                                                                  if ($existingProduct) {
                                                                    echo ((int)$existingProduct['wholesale'] === 1) ? 'checked' : '';
                                                                  } else {
                                                                    echo 'checked';
                                                                  }
                                                                  ?>>
          <label for="wholesale">Wholesale? (uncheck for retail)</label>
        </div>
      </div>

      <div>
        <label for="searchtags">Search Tags</label>
        <input type="text" id="searchtags" name="searchtags" placeholder="separate by a comma" value="<?php echo $existingProduct ? htmlspecialchars($existingProduct['searchTags']) : ''; ?>">
      </div>

      <button type="submit" id="btnSubmit" name="btnSubmit" class="addproduct-btn">
        <?php echo $editing ? 'Update Product' : 'Add Product'; ?>
      </button>




      <div class="back-link">
        <a href="index.php">← Back to Home</a>
      </div>
    </form>
    <?php
    // if (isset($_POST["btnSubmit"])) {

      
    // }
    ?>


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

  <script>
    <?php if ($storenamenotfound == 1) { ?>
      document.addEventListener('DOMContentLoaded', function() {
        disablethis("btnSubmit", true);
        document.getElementById('btnSubmit').style.background = "#808080";
      });
    <?php } ?>
  </script>

  <script>
    function disablethis(view, boool) {
      var elem = document.getElementById(view);
      if (elem) {
        elem.disabled = !!boool; // set based on boolean value passed
      }
    }









    function previewImages(input) {
      const preview = document.getElementById('imagePreview');
      //preview.innerHTML = '';


      if (input.files && input.files.length > 0) {

        for (let i = 0; i < input.files.length; i++) {
          const file = input.files[i];
          const reader = new FileReader();

          reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            //styleeeee
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            img.style.border = '1px solid #ccc';
            img.style.cursor = 'pointer';
            img.title = file.name;

            //onlickj
            img.onclick = function() {
              const modal = document.createElement('div');
              modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 1000; cursor: pointer;';
              modal.onclick = function() {
                document.body.removeChild(modal);
              };

              const modalImg = document.createElement('img');
              modalImg.src = e.target.result;
              modalImg.style.maxWidth = '90%';
              modalImg.style.maxHeight = '90%';
              modalImg.style.borderRadius = '8px';

              modal.appendChild(modalImg);
              document.body.appendChild(modal);
            };

            preview.appendChild(img);
          };

          reader.readAsDataURL(file);
        }
      }
    }
  </script>

</body>

</html>