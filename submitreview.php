<?php
session_start();

// require login
if (!isset($_SESSION['userName'])) {
    header('Location: login.html');
    exit;
}

$uEmail = $_SESSION['userName'];

// determine productID: prefer POST, then GET, then parse HTTP_REFERER as fallback
$productID = null;
if (!empty($_POST['productID'])) {
    $productID = $_POST['productID'];
} elseif (!empty($_GET['id'])) {
    $productID = $_GET['id'];
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    // try to extract ?id= from referer
    $ref = $_SERVER['HTTP_REFERER'];
    $parts = parse_url($ref);
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $qs);
        if (!empty($qs['id'])) $productID = $qs['id'];
    }
}

if (!$productID) {
    // cannot proceed without product id
    header('Location: notfound.html');
    exit;
}

// collect and sanitize review text
$reviewText = trim((string)($_POST['review'] ?? ''));
if ($reviewText === '') {
    // nothing to save
    header('Location: viewproduct.php?id=' . urlencode($productID) . '&review=empty');
    exit;
}
if (mb_strlen($reviewText) > 500) {
    $reviewText = mb_substr($reviewText, 0, 500);
}

// rating (required by updated schema). ensure integer and clamp to 1..5 (or 0 if you accept)
$rating = (int)($_POST['rating'] ?? 0);
if ($rating < 1 || $rating > 5) {
    header('Location: viewproduct.php?id=' . urlencode($productID) . '&review=rating');
    exit;
}


// DB connection
$con = mysqli_connect("localhost", "root", "", "hardwaredeals");
if (!$con) {
    // fail quietly or show minimal message
    header('Location: viewproduct.php?id=' . urlencode($productID) . '&review=fail');
    exit;
}

// insert into productreviews (productID, uEmail, reviewText, rating)
$insertReviewSql = "INSERT INTO productreviews (productID, uEmail, reviewText, rating) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($con, $insertReviewSql);
if (!$stmt) {
    header('Location: viewproduct.php?id=' . urlencode($productID) . '&review=fail');
    exit;
}
$productID = (int)$productID;
mysqli_stmt_bind_param($stmt, "issi", $productID, $uEmail, $reviewText, $rating);

$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    header('Location: viewproduct.php?id=' . urlencode($productID) . '&review=fail');
    exit;
}
$reviewID = mysqli_insert_id($con);

// Handle uploaded images (input name expected: reviewImages[])
// Note: the form in viewproduct.php should set name="reviewImages[]" for files to arrive;
// this code handles both single and multiple file inputs if present.
if (!empty($_FILES['reviewImages']) && is_array($_FILES['reviewImages']['name'])) {
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reviews';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $fileCount = count($_FILES['reviewImages']['name']);
    $maxFiles = 5;
    for ($i = 0, $saved = 0; $i < $fileCount && $saved < $maxFiles; $i++) {
        $error = $_FILES['reviewImages']['error'][$i];
        if ($error !== UPLOAD_ERR_OK) continue;
        $tmpName = $_FILES['reviewImages']['tmp_name'][$i];
        $origName = basename($_FILES['reviewImages']['name'][$i]);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);
        if (strpos($mime, 'image/') !== 0) continue; // skip non-images

        $ext = pathinfo($origName, PATHINFO_EXTENSION);
        $safeName = 'rev_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . ($ext ?: 'jpg');
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
        if (move_uploaded_file($tmpName, $destPath)) {
            $webPath = 'uploads/reviews/' . $safeName;
            // insert into reviewimgs (reviewID, imgURL)
            $insImg = mysqli_prepare($con, "INSERT INTO reviewimgs (reviewID, imgURL) VALUES (?, ?)");
            if ($insImg) {
                mysqli_stmt_bind_param($insImg, "is", $reviewID, $webPath);
                mysqli_stmt_execute($insImg);
                mysqli_stmt_close($insImg);
            }
            $saved++;
        }
    }
}elseif (!empty($_FILES['reviewImages']) && is_string($_FILES['reviewImages']['name'])) {
    // single file input with name 'reviewImages'
    if ($_FILES['reviewImages']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reviews';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $tmpName = $_FILES['reviewImages']['tmp_name'];
        $origName = basename($_FILES['reviewImages']['name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);
        if (strpos($mime, 'image/') === 0) {
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $safeName = 'rev_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . ($ext ?: 'jpg');
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
            if (move_uploaded_file($tmpName, $destPath)) {
                $webPath = 'uploads/reviews/' . $safeName;
                $insImg = mysqli_prepare($con, "INSERT INTO reviewimgs (reviewID, imgURL) VALUES (?, ?)");

                if ($insImg) {
                    mysqli_stmt_bind_param($insImg, "is", $reviewID, $webPath);
                    mysqli_stmt_execute($insImg);
                    mysqli_stmt_close($insImg);
                }
            }
        }
    }
}


// close and redirect back with success flag
mysqli_close($con);
header('Location: viewproduct.php?id=' . urlencode($productID) . '&review=ok');
exit;
?>
