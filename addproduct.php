<!DOCTYPE html>
<?php session_start();
if (!isset($_SESSION["userName"]))
{
	header('Location:login.html');

}
?>
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
      box-shadow: 0 4px 16px rgba(0,0,0,0.10);
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
        <img src="login.png" alt="Login" style="width:24px; height:24px;" onclick="window.location.href='login.html'">
      </button>
      <button>
        <img src="cart-icon.webp" alt="Cart" style="height:24px;" onclick="window.location.href='cart.html'">
      </button>
    </div>
  </header>

  <div class="addproduct-container">
    <div class="addproduct-title">Add New Product</div>

    <form class="addproduct-form" method="POST" action="addproduct.php" enctype="multipart/form-data" autocomplete="off">
      <div>
        <label for="title">Product Title</label>
        <input type="text" id="prodctitle" name="title" placeholder="Title" required>
      </div>
      
      <div>
        <label for="description">Description</label>
        <textarea style=" display: flex; align-items: center; justify-content: center;" id="description" name="prodcdescription" placeholder="enter product description" required></textarea>
      </div>

      <div>
      <label for="mainimg">Upload main image(preview):</label>
      <input type="file" id="mainimg" name="mainimg" accept="image/*" required>
      </div>

      <div>
        <label for="imgs">Images (Select multiple)</label>
        <input type="file" id="imgs" name="imgs[]" accept="image/*" multiple onchange="previewImages(this)">
        <div id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
      </div>

      <div class="form-row">
        
        <div>
          <label for="Price">Price (Rs.)</label>
          <input type="number" id="Price" name="Price" step="0.01" min="0">
          
        </div>
        <div style="display: flex; align-items: center; justify-content: center;">
          <div class="checkbox-group">
          <input type="checkbox" name="callforprice" id="callforprice">
          <label for="callforprice">Call for price</label>
          </div>
        
        </div>
      </div>

      <div>
        <label for="offTagDescription">Offer Tag (if any)</label>
        <input type="text" id="offTagDescription" name="offTagDescription" placeholder="20% OFF">
      </div>

      <div>
        <label>Store</label>
        <label id="storename">name</label>
        
      </div>

      

      <div>
        <label for="callToAction">Contact number</label>
        <input type="text" id="callToAction" name="callToAction" value="">
      </div>

      <div>
        <div class="checkbox-group">
          <input type="checkbox" id="pickup" name="pickup">
          <label for="pickup">Pickup Available</label>
        </div>
      </div>

      <div>
        <div class="checkbox-group">
          <input type="checkbox" id="inStock" name="inStock">
          <label for="inStock">In Stock</label>
        </div>
      </div>

      <div>
        <div class="checkbox-group">
          <input type="checkbox" id="deliveryAvailable" name="deliveryAvailable">
          <label for="deliveryAvailable">Delivery Available</label>
        </div>
      </div>

      <button type="submit" name="btnSubmit" class="addproduct-btn">Add Product</button>
      
      <div class="back-link">
        <a href="index.php">← Back to Home</a>
      </div>
    </form>
<?php
if(isset($_POST["btnSubmit"])){
  $title = $_POST["prodctitle"];
  $description = $_POST["prodcdescription"];
  $Price = $_POST["Price"];




  if(isset($_POST["inStock"])){
    $instock = 1;
  }else{
    $instock = 0;
  }

  if(isset($_POST["deliveryAvailable"])){
    $deliveryAvailable = 1;
  }else{
    $deliveryAvailable = 0;
  }

  if(isset($_POST["callforprice"])){
    $callToAction = $_POST["callToAction"];
  }else{
    $callToAction = NULL;
  }


  
  if(isset($_POST["offTagDescription"])){
    $offTagDescription = $_POST["offTagDescription"];
  }else{
    $offTagDescription = NULL;
  }





  if(isset($_POST["inStock"])){
      $instock = 1;
  }else{
    $instock = 0;
  }

  $mainimage = "uploads/".basename($_FILES["mainimg"]["name"]);
				move_uploaded_file($_FILES["mainimg"]["tmp_name"],$mainimage);

  
  // -----------------------img upoads section(multiple image handling code from AI)------------------------
  //
  $uploadDir = "uploads/";
  if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }
  
  $uploadedImages = array(); // image paths into an arraqy
  
  // Check if files were uploaded (using array notation imgs[])
  //if(isset($_FILES["imgs"]) && is_array($_FILES["imgs"]["name"]) && !empty($_FILES["imgs"]["name"][0])) {
    // Handle multiple files
    $fileCount = count($_FILES["imgs"]["name"]);
    
    // Loop through all uploaded files
    for($i = 0; $i < $fileCount; $i++) {
      // Check if file was actually uploaded (not empty) and no errors
      if(isset($_FILES["imgs"]["error"][$i]) && 
         $_FILES["imgs"]["error"][$i] === UPLOAD_ERR_OK && 
         !empty($_FILES["imgs"]["name"][$i])) {
        
        $fileName = $_FILES["imgs"]["name"][$i];
        $fileTmpName = $_FILES["imgs"]["tmp_name"][$i];
        $fileSize = $_FILES["imgs"]["size"][$i];
        $fileType = $_FILES["imgs"]["type"][$i];
        
        // Validate file type (images only)
        $allowedTypes = array('image/jpeg', 'image/jpg', 'image/png');
        if(in_array($fileType, $allowedTypes)) {
          // Generate unique filename to avoid conflicts
          $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
          $uniqueFileName = time() . '_' . $i . '_' . uniqid() . '.' . $fileExtension;
          $destination = $uploadDir . $uniqueFileName;
          
          // Move uploaded file to destination
          if(move_uploaded_file($fileTmpName, $destination)) {
            $uploadedImages[] = $destination; // Store the path
          }
        }

      }

    }
  //}
  

  // $uploadedImages now contains array of all uploaded image paths
  // You can use this array to save to database or JSON file
  // Example: $imagesString = implode(',', $uploadedImages); // For comma-separated string

  
  $con = mysqli_connect("localhost","root","","hardwaredeals");
			if(!$con)
			{	
				die("Cannot upload the file, Please choose another file");		
			}
    $sql = "SELECT storeID FROM `stores` WHERE storeContactUEmail = '".$_SESSION["userName"]."'";
    $storeid = mysqli_query($con,$sql);

      $sql = "INSERT INTO `products` (`productID`, `imgURL`, `offTagDescription`, `oldPrice`, `newPrice`, `title`, `description`, `soldByStoreID`, `reviewCount`, `rating`, `deliveryAvailable`, `callToAction`, `pickup`, `inStock`, `forRent`, `wholesale`, `searchTags`) 
                VALUES (NULL, '".$mainimage."', '".$offTagDescription."', NULL, '".$Price."', '".$title."', '".$description."', '".$storeid."', NULL, NULL, '".$deliveryAvailable."', '".$callToAction."', '1', '".$instock."', '0', '1', NULL)";

if(  mysqli_query($con,$sql))
{
  echo "Post uploaded Successfully";
}else{
  echo "error";
}

}
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
              modal.onclick = function() { document.body.removeChild(modal); };
              
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
