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

// fetch current user
$stmt = mysqli_prepare($con, "SELECT uEmail, `name`, address, contact, profilePic FROM users WHERE uEmail = ? OR `name` = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ss", $username, $username);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : null;

if (!$user) {
    echo "User not found.";
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $profilePicURL = trim($_POST['profilePicURL'] ?? '');

    if ($name === '') {
        $errors[] = "Name is required.";
    }

    // Handle upload if provided
    $newProfilePic = $user['profilePic']; // default keep existing
    if (!empty($_FILES['profilePicFile']['name'])) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $file = $_FILES['profilePicFile'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeName = 'profile_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // store web-accessible path relative to project root
                $newProfilePic = 'uploads/' . $safeName;
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        } else {
            $errors[] = "File upload error (code {$file['error']}).";
        }
    } elseif ($profilePicURL !== '') {
        // If no upload but a URL provided, use it
        $newProfilePic = $profilePicURL;
    }

    if (empty($errors)) {
        // Update the user (prepared statement)
        $updateStmt = mysqli_prepare($con, "UPDATE users SET `name` = ?, address = ?, contact = ?, profilePic = ? WHERE uEmail = ? OR `name` = ? LIMIT 1");
        mysqli_stmt_bind_param($updateStmt, "ssssss", $name, $address, $contact, $newProfilePic, $username, $username);
        if (mysqli_stmt_execute($updateStmt)) {
            $success = true;
            // refresh session username if user changed their display name and session stores name
            // (only update session if it matched previous name)
            if ($username === $user['name']) {
                $_SESSION['userName'] = $name;
            }
            header("Location: viewProfile.php");
            exit;
        } else {
            $errors[] = "Database update failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Edit Profile - Hardware Deals</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .form-wrapper { max-width:700px; margin:28px auto; padding:16px; background:#fff; border-radius:8px; box-shadow:0 1px 6px rgba(0,0,0,.06); }
    .form-row { margin-bottom:12px; }
    label { display:block; margin-bottom:6px; font-weight:600; }
    input[type="text"], input[type="email"], textarea { width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; }
    .profile-thumb { display:inline-block; margin-bottom:8px; }
    .actions { margin-top:14px; }
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
      <button><img src="login.png" alt="Login" style="width:24px; height:24px;" onclick="window.location.href='login.html'"></button>
      <button><img src="cart-icon.webp" alt="Cart" style=" height:24px;" onclick="window.location.href='cart.html'"></button>
    </div>
  </header>

  <div class="form-wrapper">
    <h2>Edit Profile</h2>

    <?php if (!empty($errors)): ?>
      <div style="color:#b00020; margin-bottom:12px;">
        <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <div class="form-row">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($_POST['name'] ?? $user['name']); ?>" required>
      </div>

      <div class="form-row">
        <label for="address">Address</label>
        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? $user['address']); ?></textarea>
      </div>

      <div class="form-row">
        <label for="contact">Contact</label>
        <input id="contact" name="contact" type="text" value="<?php echo htmlspecialchars($_POST['contact'] ?? $user['contact']); ?>">
      </div>

      <div class="form-row">
        <label>Current Profile Picture</label>
        <div class="profile-thumb">
          <img src="<?php echo htmlspecialchars($user['profilePic'] ?: 'profile-placeholder.png'); ?>" alt="Profile" style="width:120px;height:120px;object-fit:cover;border-radius:6px;">
        </div>
      </div>

      <div class="form-row">
        <label for="profilePicFile">Upload New Profile Picture (optional)</label>
        <input id="profilePicFile" name="profilePicFile" type="file" accept="image/*">
      </div>

      <div class="form-row">
        <label for="profilePicURL">Or Profile Picture URL (optional)</label>
        <input id="profilePicURL" name="profilePicURL" type="text" value="<?php echo htmlspecialchars($_POST['profilePicURL'] ?? ''); ?>">
      </div>

      <div class="actions">
        <button type="submit">Save Changes</button>
        <button type="button" onclick="window.location.href='viewProfile.php'">Cancel</button>
      </div>
    </form>
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
